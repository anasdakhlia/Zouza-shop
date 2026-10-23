<?php
/**
 * ZOUZA SHOP - Contrôleur d'Administration des Catégories
 *
 * Gère le CRUD des catégories de produits pour les administrateurs.
 * Vérifie systématiquement les autorisations, la validité des données et les jetons CSRF.
 */

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/Category.php';

class AdminCategoryController extends Controller
{
    private Category $categoryModel;

    public function __construct()
    {
        $this->requireAdmin();
        $this->categoryModel = new Category();
    }

    /**
     * Affiche la liste des catégories
     */
    public function index(): void
    {
        $this->requireAdmin();

        $categories = $this->categoryModel->getAll();

        $this->render('backoffice/categories/index', [
            'pageTitle'  => 'Gestion des Catégories',
            'categories' => $categories,
        ], 'admin');
    }

    /**
     * Affiche le formulaire d'ajout d'une catégorie
     */
    public function create(): void
    {
        $this->requireAdmin();

        $this->render('backoffice/categories/create', [
            'pageTitle' => 'Ajouter une Catégorie',
        ], 'admin');
    }

    /**
     * Traite l'enregistrement d'une nouvelle catégorie
     */
    public function store(): void
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $nom = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');

        // Validation côté serveur
        if ($nom === '') {
            Session::setFlash('error', 'Le nom de la catégorie est obligatoire.');
            $this->redirect('/admin/categories/create');
        }

        if (mb_strlen($nom) > 100) {
            Session::setFlash('error', 'Le nom de la catégorie ne peut pas dépasser 100 caractères.');
            $this->redirect('/admin/categories/create');
        }

        if (mb_strlen($description) > 1000) {
            Session::setFlash('error', 'La description ne peut pas dépasser 1000 caractères.');
            $this->redirect('/admin/categories/create');
        }

        try {
            $this->categoryModel->create([
                'nom'         => $nom,
                'description' => $description,
            ]);

            Session::setFlash('success', "La catégorie '{$nom}' a été créée avec succès.");
            $this->redirect('/admin/categories');
        } catch (Throwable $e) {
            error_log('Erreur création catégorie : ' . $e->getMessage());
            Session::setFlash('error', 'Une erreur est survenue lors de la création de la catégorie.');
            $this->redirect('/admin/categories/create');
        }
    }

    /**
     * Affiche le formulaire de modification d'une catégorie
     */
    public function edit(): void
    {
        $this->requireAdmin();

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            Session::setFlash('error', 'Identifiant de catégorie invalide.');
            $this->redirect('/admin/categories');
        }

        $category = $this->categoryModel->getById($id);
        if (!$category) {
            Session::setFlash('error', 'Catégorie introuvable.');
            $this->redirect('/admin/categories');
        }

        $this->render('backoffice/categories/edit', [
            'pageTitle' => 'Modifier la Catégorie : ' . $category['nom'],
            'category'  => $category,
        ], 'admin');
    }

    /**
     * Traite la mise à jour d'une catégorie existante
     */
    public function update(): void
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            Session::setFlash('error', 'Identifiant de catégorie manquant ou invalide.');
            $this->redirect('/admin/categories');
        }

        $category = $this->categoryModel->getById($id);
        if (!$category) {
            Session::setFlash('error', 'Catégorie introuvable.');
            $this->redirect('/admin/categories');
        }

        $nom = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');

        // Validation côté serveur
        if ($nom === '') {
            Session::setFlash('error', 'Le nom de la catégorie est obligatoire.');
            $this->redirect('/admin/categories/edit?id=' . $id);
        }

        if (mb_strlen($nom) > 100) {
            Session::setFlash('error', 'Le nom de la catégorie ne peut pas dépasser 100 caractères.');
            $this->redirect('/admin/categories/edit?id=' . $id);
        }

        if (mb_strlen($description) > 1000) {
            Session::setFlash('error', 'La description ne peut pas dépasser 1000 caractères.');
            $this->redirect('/admin/categories/edit?id=' . $id);
        }

        try {
            $this->categoryModel->update($id, [
                'nom'         => $nom,
                'description' => $description,
            ]);

            Session::setFlash('success', "La catégorie a été mise à jour avec succès.");
            $this->redirect('/admin/categories');
        } catch (Throwable $e) {
            error_log('Erreur modification catégorie : ' . $e->getMessage());
            Session::setFlash('error', 'Une erreur est survenue lors de la mise à jour.');
            $this->redirect('/admin/categories/edit?id=' . $id);
        }
    }

    /**
     * Supprime une catégorie si aucun produit n'en dépend
     */
    public function delete(): void
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            Session::setFlash('error', 'Identifiant de catégorie invalide.');
            $this->redirect('/admin/categories');
        }

        $category = $this->categoryModel->getById($id);
        if (!$category) {
            Session::setFlash('error', 'Catégorie introuvable.');
            $this->redirect('/admin/categories');
        }

        // Vérification sécurisée des produits rattachés avant suppression
        if ($this->categoryModel->hasProducts($id)) {
            $productCount = $this->categoryModel->countProducts($id);
            Session::setFlash('error', "Suppression refusée : {$productCount} produit(s) sont encore associés à cette catégorie. Veuillez d'abord réassigner ou supprimer ces produits.");
            $this->redirect('/admin/categories');
        }

        try {
            $this->categoryModel->delete($id);
            Session::setFlash('success', "La catégorie '{$category['nom']}' a été supprimée avec succès.");
        } catch (Throwable $e) {
            error_log('Erreur suppression catégorie : ' . $e->getMessage());
            Session::setFlash('error', 'Impossible de supprimer cette catégorie car des enregistrements en dépendent.');
        }

        $this->redirect('/admin/categories');
    }
}
