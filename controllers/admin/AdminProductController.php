<?php
/**
 * ZOUZA SHOP - Contrôleur d'Administration des Produits
 *
 * Gère le catalogue produits côté administrateur :
 * listing, ajout, modification, suppression et téléversement sécurisé d'images.
 */

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../models/Category.php';

class AdminProductController extends Controller
{
    private Product $productModel;
    private Category $categoryModel;

    // Configuration des téléversements d'images
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];
    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    private const MAX_FILE_SIZE      = 2 * 1024 * 1024; // 2 Mo

    public function __construct()
    {
        $this->productModel  = new Product();
        $this->categoryModel = new Category();
    }

    /**
     * Affiche la liste complète des produits
     */
    public function index(): void
    {
        $this->requireAdmin();

        $products = $this->productModel->getAll();

        $this->render('backoffice/products/index', [
            'pageTitle' => 'Gestion des Produits',
            'products'  => $products,
        ], 'admin');
    }

    /**
     * Affiche le formulaire de création d'un produit
     */
    public function create(): void
    {
        $this->requireAdmin();

        $categories = $this->categoryModel->getAll();

        $this->render('backoffice/products/create', [
            'pageTitle'  => 'Ajouter un Produit',
            'categories' => $categories,
        ], 'admin');
    }

    /**
     * Traite l'ajout d'un nouveau produit
     */
    public function store(): void
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $nom         = trim($_POST['nom'] ?? '');
        $categoryId  = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $prixRaw     = trim($_POST['prix'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $disponible  = isset($_POST['disponible']) ? 1 : 0;

        // 1. Validations métier
        if ($nom === '') {
            Session::setFlash('error', 'Le nom du produit est obligatoire.');
            $this->redirect('/admin/products/create');
        }

        if (mb_strlen($nom) > 150) {
            Session::setFlash('error', 'Le nom du produit ne peut pas dépasser 150 caractères.');
            $this->redirect('/admin/products/create');
        }

        if (!$categoryId || !$this->categoryModel->getById($categoryId)) {
            Session::setFlash('error', 'Veuillez sélectionner une catégorie valide.');
            $this->redirect('/admin/products/create');
        }

        if ($prixRaw === '' || !is_numeric($prixRaw) || (float)$prixRaw < 0) {
            Session::setFlash('error', 'Le prix doit être un nombre positif ou nul valide.');
            $this->redirect('/admin/products/create');
        }
        $prix = (float)$prixRaw;

        // 2. Traitement sécurisé de l'image (si fournie)
        $imageFilename = null;
        if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = $this->handleImageUpload($_FILES['image']);
            if (!$uploadResult['success']) {
                Session::setFlash('error', $uploadResult['error']);
                $this->redirect('/admin/products/create');
            }
            $imageFilename = $uploadResult['filename'];
        }

        try {
            $this->productModel->create([
                'category_id' => $categoryId,
                'nom'         => $nom,
                'description' => $description,
                'prix'        => $prix,
                'image'       => $imageFilename,
                'disponible'  => $disponible,
            ]);

            Session::setFlash('success', "Le produit '{$nom}' a été ajouté avec succès.");
            $this->redirect('/admin/products');
        } catch (Throwable $e) {
            error_log('Erreur création produit : ' . $e->getMessage());
            Session::setFlash('error', 'Une erreur est survenue lors de l\'enregistrement du produit.');
            $this->redirect('/admin/products/create');
        }
    }

    /**
     * Affiche le formulaire de modification d'un produit
     */
    public function edit(): void
    {
        $this->requireAdmin();

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            Session::setFlash('error', 'Identifiant de produit invalide.');
            $this->redirect('/admin/products');
        }

        $product = $this->productModel->getById($id);
        if (!$product) {
            Session::setFlash('error', 'Produit introuvable.');
            $this->redirect('/admin/products');
        }

        $categories = $this->categoryModel->getAll();

        $this->render('backoffice/products/edit', [
            'pageTitle'  => 'Modifier le Produit : ' . $product['nom'],
            'product'    => $product,
            'categories' => $categories,
        ], 'admin');
    }

    /**
     * Traite la mise à jour d'un produit existant
     */
    public function update(): void
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            Session::setFlash('error', 'Identifiant de produit invalide.');
            $this->redirect('/admin/products');
        }

        $product = $this->productModel->getById($id);
        if (!$product) {
            Session::setFlash('error', 'Produit introuvable.');
            $this->redirect('/admin/products');
        }

        $nom         = trim($_POST['nom'] ?? '');
        $categoryId  = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $prixRaw     = trim($_POST['prix'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $disponible  = isset($_POST['disponible']) ? 1 : 0;

        if ($nom === '') {
            Session::setFlash('error', 'Le nom du produit est obligatoire.');
            $this->redirect('/admin/products/edit?id=' . $id);
        }

        if (mb_strlen($nom) > 150) {
            Session::setFlash('error', 'Le nom du produit ne peut pas dépasser 150 caractères.');
            $this->redirect('/admin/products/edit?id=' . $id);
        }

        if (!$categoryId || !$this->categoryModel->getById($categoryId)) {
            Session::setFlash('error', 'Veuillez sélectionner une catégorie valide.');
            $this->redirect('/admin/products/edit?id=' . $id);
        }

        if ($prixRaw === '' || !is_numeric($prixRaw) || (float)$prixRaw < 0) {
            Session::setFlash('error', 'Le prix doit être un nombre positif ou nul valide.');
            $this->redirect('/admin/products/edit?id=' . $id);
        }
        $prix = (float)$prixRaw;

        $updateData = [
            'category_id' => $categoryId,
            'nom'         => $nom,
            'description' => $description,
            'prix'        => $prix,
            'disponible'  => $disponible,
        ];

        // Téléversement d'une nouvelle image si fournie
        if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = $this->handleImageUpload($_FILES['image']);
            if (!$uploadResult['success']) {
                Session::setFlash('error', $uploadResult['error']);
                $this->redirect('/admin/products/edit?id=' . $id);
            }

            // Supprimer l'ancienne image si elle existe
            $this->deletePhysicalImage($product['image']);
            $updateData['image'] = $uploadResult['filename'];
        }

        try {
            $this->productModel->update($id, $updateData);
            Session::setFlash('success', "Le produit a été mis à jour avec succès.");
            $this->redirect('/admin/products');
        } catch (Throwable $e) {
            error_log('Erreur modification produit : ' . $e->getMessage());
            Session::setFlash('error', 'Une erreur est survenue lors de la mise à jour.');
            $this->redirect('/admin/products/edit?id=' . $id);
        }
    }

    /**
     * Supprime un produit
     */
    public function delete(): void
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            Session::setFlash('error', 'Identifiant de produit invalide.');
            $this->redirect('/admin/products');
        }

        $product = $this->productModel->getById($id);
        if (!$product) {
            Session::setFlash('error', 'Produit introuvable.');
            $this->redirect('/admin/products');
        }

        try {
            // Suppression de l'image associée si présente
            $this->deletePhysicalImage($product['image']);
            $this->productModel->delete($id);

            Session::setFlash('success', "Le produit '{$product['nom']}' a été supprimé.");
        } catch (Throwable $e) {
            error_log('Erreur suppression produit : ' . $e->getMessage());
            // Si le produit est référencé dans des commandes, on propose la désactivation
            Session::setFlash('error', 'Impossible de supprimer ce produit car des commandes y font référence. Vous pouvez désactiver sa disponibilité à la place.');
        }

        $this->redirect('/admin/products');
    }

    /**
     * Traite un fichier téléversé de façon sécurisée
     *
     * @param array $file Fichier $_FILES['...']
     * @return array ['success' => bool, 'filename' => string|null, 'error' => string|null]
     */
    private function handleImageUpload(array $file): array
    {
        // 1. Vérification des erreurs de téléversement natives PHP
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE   => 'Le fichier dépasse la taille maximale autorisée par le serveur.',
                UPLOAD_ERR_FORM_SIZE  => 'Le fichier dépasse la limite définie dans le formulaire.',
                UPLOAD_ERR_PARTIAL    => 'Le fichier n\'a été que partiellement téléchargé.',
                UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant sur le serveur.',
                UPLOAD_ERR_CANT_WRITE => 'Échec de l\'écriture du fichier sur le disque.',
                UPLOAD_ERR_EXTENSION  => 'Une extension PHP a bloqué le téléversement.',
            ];
            return [
                'success' => false,
                'error'   => $errors[$file['error']] ?? 'Erreur lors du téléversement de l\'image.',
            ];
        }

        // 2. Vérification de la taille maximale (2 Mo)
        if ($file['size'] > self::MAX_FILE_SIZE) {
            return [
                'success' => false,
                'error'   => 'L\'image ne doit pas dépasser 2 Mo.',
            ];
        }

        // 3. Validation de l'extension de fichier
        $originalExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($originalExt, self::ALLOWED_EXTENSIONS, true)) {
            return [
                'success' => false,
                'error'   => 'Format d\'image non autorisé. Extensions acceptées : ' . implode(', ', self::ALLOWED_EXTENSIONS),
            ];
        }

        // 4. Validation du type MIME réel via Fileinfo (indépendant de l'extension et de $_FILES['type'])
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($file['tmp_name']);
        if (!in_array($realMime, self::ALLOWED_MIME_TYPES, true)) {
            return [
                'success' => false,
                'error'   => 'Le contenu du fichier n\'est pas une image valide.',
            ];
        }

        // 5. Validation de la signature d'image (getimagesize)
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return [
                'success' => false,
                'error'   => 'Le fichier n\'est pas une image authentique.',
            ];
        }

        // 6. Génération d'un nom de fichier serveur aléatoire et sécurisé (anti-traversée)
        $safeExtension = ($originalExt === 'jpeg') ? 'jpg' : $originalExt;
        $uniqueFilename = bin2hex(random_bytes(16)) . '.' . $safeExtension;

        // 7. Déplacement vers le dossier de destination
        $uploadDir = __DIR__ . '/../../uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destination = $uploadDir . $uniqueFilename;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return [
                'success' => false,
                'error'   => 'Impossible de sauvegarder l\'image sur le serveur.',
            ];
        }

        return [
            'success'  => true,
            'filename' => $uniqueFilename,
        ];
    }

    /**
     * Supprime physiquement une image du dossier d'uploads si elle existe
     *
     * @param string|null $filename
     */
    private function deletePhysicalImage(?string $filename): void
    {
        if (empty($filename)) {
            return;
        }

        // Nettoyage strict anti-traversée de chemin
        $cleanFilename = basename($filename);
        $filePath = __DIR__ . '/../../uploads/products/' . $cleanFilename;

        if (file_exists($filePath) && is_file($filePath)) {
            @unlink($filePath);
        }
    }
}
