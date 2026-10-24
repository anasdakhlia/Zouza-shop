<?php
/**
 * ZOUZA SHOP - Contrôleur Public du Catalogue Produits
 *
 * Permet aux visiteurs et clients de parcourir les pâtisseries Zouza,
 * de rechercher par mot-clé, de filtrer par catégorie et disponibilité,
 * de trier par prix et de naviguer via pagination serveur.
 * Accessible publiquement sans authentification.
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

class ProductController extends Controller
{
    private Product $productModel;
    private Category $categoryModel;

    // Nombre de produits par page dans la grille du catalogue
    private const PER_PAGE = 6;

    public function __construct()
    {
        $this->productModel  = new Product();
        $this->categoryModel = new Category();
    }

    /**
     * Affiche le catalogue public avec recherche, filtres combinés, tri et pagination
     */
    public function index(): void
    {
        $categories = $this->categoryModel->getAll();

        // 1. Récupération et assainissement de la recherche textuelle
        $searchParam = trim($_GET['q'] ?? ($_GET['search'] ?? ''));

        // 2. Récupération et résolution du filtre de catégorie (par id ou par slug)
        $categoryParam    = trim($_GET['category'] ?? ($_GET['category_id'] ?? ''));
        $selectedCategory = null;

        if ($categoryParam !== '') {
            if (is_numeric($categoryParam)) {
                $selectedCategory = $this->categoryModel->getById((int)$categoryParam);
            } else {
                $selectedCategory = $this->categoryModel->getBySlug($categoryParam);
            }
        }

        // 3. Récupération du filtre de disponibilité ('available' / '1', '0' / 'out_of_stock', 'all')
        $availabilityParam = trim($_GET['availability'] ?? 'available');
        $validAvailabilities = ['available', '1', '0', 'out_of_stock', 'all'];
        if (!in_array($availabilityParam, $validAvailabilities, true)) {
            $availabilityParam = 'available';
        }

        // 4. Récupération et validation du tri
        $sortParam = trim($_GET['sort'] ?? 'newest');
        $validSorts = ['newest', 'price_asc', 'price_desc', 'name_asc', 'name_desc'];
        if (!in_array($sortParam, $validSorts, true)) {
            $sortParam = 'newest';
        }

        // 5. Récupération et validation du numéro de page
        $pageParam = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
        $currentPage = ($pageParam && $pageParam > 0) ? (int)$pageParam : 1;

        // 6. Construction du tableau de filtres pour le modèle
        $filters = [
            'search'        => $searchParam,
            'category_id'   => $selectedCategory ? (int)$selectedCategory['category_id'] : null,
            'category_slug' => (!$selectedCategory && $categoryParam !== '') ? $categoryParam : null,
            'availability'  => $availabilityParam,
            'sort'          => $sortParam,
        ];

        // 7. Exécution de la requête de filtrage et pagination
        $result = $this->productModel->searchAndFilter($filters, $currentPage, self::PER_PAGE);

        // 8. Préparation des paramètres de requête pour la pagination (sans 'page')
        $queryParams = [];
        if ($searchParam !== '') {
            $queryParams['q'] = $searchParam;
        }
        if ($selectedCategory) {
            $queryParams['category'] = $selectedCategory['slug'];
        } elseif ($categoryParam !== '') {
            $queryParams['category'] = $categoryParam;
        }
        if ($availabilityParam !== 'available' && $availabilityParam !== '1') {
            $queryParams['availability'] = $availabilityParam;
        }
        if ($sortParam !== 'newest') {
            $queryParams['sort'] = $sortParam;
        }

        // 9. Titre dynamique et métadonnées SEO descriptives de la page
        if ($searchParam !== '') {
            $pageTitle       = 'Recherche : « ' . $searchParam . ' » | ' . APP_NAME;
            $metaDescription = 'Résultats de recherche pour « ' . $searchParam . ' » parmi nos Zouzas artisanales chez ' . APP_NAME . '.';
            $canonicalUrl    = BASE_URL . '/products';
        } elseif ($selectedCategory) {
            $pageTitle       = 'Nos ' . $selectedCategory['nom'] . ' | ' . APP_NAME;
            $metaDescription = 'Découvrez notre sélection de ' . $selectedCategory['nom'] . ' artisanales confectionnées avec soin chez ' . APP_NAME . '.';
            $canonicalUrl    = BASE_URL . '/products?category=' . urlencode($selectedCategory['slug']);
        } else {
            $pageTitle       = 'Catalogue des Délices Zouza | ' . APP_NAME;
            $metaDescription = 'Consultez notre catalogue complet de Zouzas artisanales tunisiennes : amande, noisette, pistache, chocolat et assortiments gourmands.';
            $canonicalUrl    = BASE_URL . '/products';
        }

        $this->render('frontoffice/products/index', [
            'pageTitle'        => $pageTitle,
            'metaDescription'  => $metaDescription,
            'canonicalUrl'     => $canonicalUrl,
            'categories'       => $categories,
            'selectedCategory' => $selectedCategory,
            'products'         => $result['items'],
            'totalItems'       => $result['total'],
            'currentPage'      => $result['page'],
            'totalPages'       => $result['total_pages'],
            'perPage'          => $result['per_page'],
            'search'           => $searchParam,
            'availability'     => $availabilityParam,
            'sort'             => $sortParam,
            'queryParams'      => $queryParams,
        ], 'front');
    }

    /**
     * Affiche la fiche détaillée d'un produit
     */
    public function detail(): void
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $slug = trim($_GET['slug'] ?? '');

        $product = null;
        if ($id && $id > 0) {
            $product = $this->productModel->getById($id);
        } elseif ($slug !== '') {
            $product = $this->productModel->getBySlug($slug);
        }

        // Vérification d'existence du produit
        if (!$product) {
            ErrorHandler::renderNotFound("Le délice demandé n'existe pas ou a été retiré du catalogue.");
            return;
        }

        $metaDescription = !empty($product['description'])
            ? mb_strimwidth(strip_tags($product['description']), 0, 155, '...')
            : ($product['nom'] . ' — Pâtisserie artisanale faite maison chez ' . APP_NAME . '. Ingrédients nobles et tradition.');

        $canonicalUrl = !empty($product['slug'])
            ? BASE_URL . '/products/detail?slug=' . urlencode($product['slug'])
            : BASE_URL . '/products/detail?id=' . (int)$product['product_id'];

        $ogImage = !empty($product['image'])
            ? BASE_URL . '/uploads/products/' . rawurlencode($product['image'])
            : '';

        $this->render('frontoffice/products/detail', [
            'pageTitle'       => $product['nom'] . ' | ' . APP_NAME,
            'metaDescription' => $metaDescription,
            'canonicalUrl'    => $canonicalUrl,
            'ogType'          => 'product',
            'ogImage'         => $ogImage,
            'product'         => $product,
        ], 'front');
    }
}
