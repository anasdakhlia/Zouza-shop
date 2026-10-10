<?php
/**
 * ZOUZA SHOP - Contrôleur du Panier d'Achat
 *
 * Gère les actions sur le panier : consultation, ajout, modification de quantité,
 * suppression d'un article et vidage complet du panier.
 * Fonctionne en mode invité (session) sans imposer de connexion préalable.
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Product.php';

class CartController extends Controller
{
    private Cart $cartModel;
    private Product $productModel;

    public function __construct()
    {
        $this->cartModel    = new Cart();
        $this->productModel = new Product();
    }

    /**
     * Affiche le contenu actuel du panier
     */
    public function index(): void
    {
        $items = $this->cartModel->getItems();
        $total = $this->cartModel->getTotal();

        $this->render('frontoffice/cart/index', [
            'pageTitle' => 'Mon Panier Gourmand | ' . APP_NAME,
            'items'     => $items,
            'total'     => $total,
            'itemCount' => $this->cartModel->count(),
        ], 'front');
    }

    /**
     * Ajoute un produit au panier
     */
    public function add(): void
    {
        $this->validateCsrf();

        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity  = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT) ?? 1;

        if (!$productId || $productId <= 0) {
            Session::setFlash('error', 'Produit invalide ou inexistant.');
            $this->redirect('/products');
        }

        if (!$quantity || $quantity <= 0) {
            Session::setFlash('error', 'Veuillez indiquer une quantité valide (au moins 1).');
            $this->redirect('/products/detail?id=' . $productId);
        }

        if ($quantity > Cart::MAX_QUANTITY_PER_ITEM) {
            Session::setFlash('error', 'La quantité maximale par article est de ' . Cart::MAX_QUANTITY_PER_ITEM . '.');
            $this->redirect('/products/detail?id=' . $productId);
        }

        // Vérification de la disponibilité du produit en base
        $product = $this->productModel->getById($productId);
        if (!$product || (int)$product['disponible'] !== 1) {
            Session::setFlash('error', 'Ce produit n\'est actuellement plus disponible.');
            $this->redirect('/products');
        }

        $success = $this->cartModel->add($productId, $quantity);

        if ($success) {
            Session::setFlash('success', "Le délice '{$product['nom']}' a été ajouté à votre panier !");
        } else {
            Session::setFlash('error', 'Impossible d\'ajouter ce produit au panier.');
        }

        $this->redirect('/cart');
    }

    /**
     * Met à jour la quantité d'un article dans le panier
     */
    public function update(): void
    {
        $this->validateCsrf();

        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity  = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

        if (!$productId || $productId <= 0) {
            Session::setFlash('error', 'Identifiant de produit invalide.');
            $this->redirect('/cart');
        }

        if ($quantity === false || $quantity < 0) {
            Session::setFlash('error', 'La quantité doit être un nombre positif ou nul.');
            $this->redirect('/cart');
        }

        if ($quantity === 0) {
            $this->cartModel->remove($productId);
            Session::setFlash('info', 'L\'article a été retiré de votre panier.');
            $this->redirect('/cart');
        }

        if ($quantity > Cart::MAX_QUANTITY_PER_ITEM) {
            Session::setFlash('error', 'La quantité ne peut pas dépasser ' . Cart::MAX_QUANTITY_PER_ITEM . ' unités.');
            $this->redirect('/cart');
        }

        $this->cartModel->update($productId, $quantity);
        Session::setFlash('success', 'La quantité a été mise à jour.');
        $this->redirect('/cart');
    }

    /**
     * Supprime un article du panier
     */
    public function remove(): void
    {
        $this->validateCsrf();

        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

        if (!$productId || $productId <= 0) {
            Session::setFlash('error', 'Article introuvable.');
            $this->redirect('/cart');
        }

        $this->cartModel->remove($productId);
        Session::setFlash('info', 'L\'article a été retiré de votre panier.');
        $this->redirect('/cart');
    }

    /**
     * Vide intégralement le panier
     */
    public function clear(): void
    {
        $this->validateCsrf();

        $this->cartModel->clear();
        Session::setFlash('info', 'Votre panier a été entièrement vidé.');
        $this->redirect('/cart');
    }
}
