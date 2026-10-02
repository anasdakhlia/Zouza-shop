<?php
/**
 * ZOUZA SHOP - Contrôleur Public du Catalogue Produits
 *
 * Permet aux visiteurs et clients de parcourir les pâtisseries Zouza,
 * de filtrer par catégorie et de consulter la fiche détaillée de chaque produit.
 * Accessible publiquement sans authentification.
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

class ProductController extends Controller
{
    private Product $productModel;
    private Category $categoryModel;

    public function __construct()
    {
        $this->productModel  = new Product();
        $this->categoryModel = new Category();
    }

    /**
     * Affiche le catalogue public avec filtrage optionnel par catégorie
     */
    public function index(): void
    {
        $categories = $this->categoryModel->getAll();

        // Récupération et assainissement du filtre de catégorie
        $selectedCategory = null;
        $categoryParam = $_GET['category'] ?? ($_GET['category_id'] ?? null);

        if (!empty($categoryParam)) {
            if (is_numeric($categoryParam)) {
                $selectedCategory = $this->categoryModel->getById((int)$categoryParam);
            } else {
                $selectedCategory = $this->categoryModel->getBySlug((string)$categoryParam);
            }
        }

        // Si une catégorie valide est sélectionnée, on filtre, sinon on liste tous les produits disponibles
        if ($selectedCategory) {
            $products = $this->productModel->getByCategory((int)$selectedCategory['category_id'], true);
            $pageTitle = 'Nos ' . $selectedCategory['nom'] . ' | ' . APP_NAME;
        } else {
            $products = $this->productModel->getAvailable();
            $pageTitle = 'Catalogue des Délices Zouza | ' . APP_NAME;
        }

        $this->render('frontoffice/products/index', [
            'pageTitle'        => $pageTitle,
            'categories'       => $categories,
            'products'         => $products,
            'selectedCategory' => $selectedCategory,
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

        $this->render('frontoffice/products/detail', [
            'pageTitle' => $product['nom'] . ' | ' . APP_NAME,
            'product'   => $product,
        ], 'front');
    }
}
