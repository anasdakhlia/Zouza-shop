<?php
/**
 * ZOUZA SHOP - Modèle Panier d'Achat (Shopping Cart)
 *
 * Gère le panier d'achat stocké en session PHP (`$_SESSION['cart']`).
 * Ne stocke en session que les identifiants et les quantités :
 * les prix, libellés, photos et disponibilités sont TOUJOURS extraits
 * directement de la base de données via le modèle Product pour garantir
 * qu'aucun prix client falsifié ne puisse être injecté.
 */

require_once __DIR__ . '/Product.php';
require_once __DIR__ . '/../core/Session.php';

class Cart
{
    private Product $productModel;
    public const MAX_QUANTITY_PER_ITEM = 99;

    public function __construct()
    {
        Session::start();
        $this->productModel = new Product();

        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    /**
     * Retourne tous les articles du panier enrichis des données réelles de la base
     *
     * @return array Tableau d'articles: [['product' => [...], 'quantity' => int, 'subtotal' => float], ...]
     */
    public function getItems(): array
    {
        $items = [];
        $cart = $_SESSION['cart'] ?? [];

        foreach ($cart as $productId => $quantity) {
            $productId = (int)$productId;
            $quantity  = (int)$quantity;

            if ($productId <= 0 || $quantity <= 0) {
                unset($_SESSION['cart'][$productId]);
                continue;
            }

            // Récupération en temps réel des données du produit depuis la base
            $product = $this->productModel->getById($productId);

            // Si le produit n'existe plus ou n'est plus disponible, on le retire du panier
            if (!$product || (int)$product['disponible'] !== 1) {
                unset($_SESSION['cart'][$productId]);
                continue;
            }

            $unitPrice = (float)$product['prix'];
            $subtotal  = $unitPrice * $quantity;

            $items[] = [
                'product'   => $product,
                'quantity'  => $quantity,
                'subtotal'  => $subtotal,
            ];
        }

        return $items;
    }

    /**
     * Calcule le montant total du panier côté serveur
     *
     * @return float
     */
    public function getTotal(): float
    {
        $items = $this->getItems();
        $total = 0.0;

        foreach ($items as $item) {
            $total += (float)$item['subtotal'];
        }

        return $total;
    }

    /**
     * Retourne le nombre total d'unités d'articles dans le panier
     *
     * @return int
     */
    public function count(): int
    {
        Session::start();
        $cart = $_SESSION['cart'] ?? [];
        $totalQuantity = 0;

        foreach ($cart as $qty) {
            $totalQuantity += (int)$qty;
        }

        return $totalQuantity;
    }

    /**
     * Méthode statique pour obtenir facilement le nombre d'articles (ex: dans les vues de navigation)
     *
     * @return int
     */
    public static function countItems(): int
    {
        Session::start();
        $cart = $_SESSION['cart'] ?? [];
        $total = 0;

        foreach ($cart as $qty) {
            $total += (int)$qty;
        }

        return $total;
    }

    /**
     * Ajoute un produit au panier ou incrémente sa quantité
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function add(int $productId, int $quantity = 1): bool
    {
        if ($productId <= 0 || $quantity <= 0) {
            return false;
        }

        // Vérification de l'existence et de la disponibilité du produit en base
        $product = $this->productModel->getById($productId);
        if (!$product || (int)$product['disponible'] !== 1) {
            return false;
        }

        $currentQty = $_SESSION['cart'][$productId] ?? 0;
        $newQty = min(self::MAX_QUANTITY_PER_ITEM, $currentQty + $quantity);

        $_SESSION['cart'][$productId] = $newQty;
        return true;
    }

    /**
     * Met à jour la quantité d'un produit dans le panier
     *
     * @param int $productId
     * @param int $quantity Si <= 0, le produit est supprimé du panier
     * @return bool
     */
    public function update(int $productId, int $quantity): bool
    {
        if ($productId <= 0) {
            return false;
        }

        if ($quantity <= 0) {
            return $this->remove($productId);
        }

        // Vérification que le produit existe et est disponible
        $product = $this->productModel->getById($productId);
        if (!$product || (int)$product['disponible'] !== 1) {
            $this->remove($productId);
            return false;
        }

        $cleanQuantity = min(self::MAX_QUANTITY_PER_ITEM, $quantity);
        $_SESSION['cart'][$productId] = $cleanQuantity;
        return true;
    }

    /**
     * Retire un produit du panier
     *
     * @param int $productId
     * @return bool
     */
    public function remove(int $productId): bool
    {
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
            return true;
        }

        return false;
    }

    /**
     * Vide intégralement le panier
     */
    public function clear(): void
    {
        $_SESSION['cart'] = [];
    }

    /**
     * Vérifie si le panier est vide
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($_SESSION['cart']);
    }
}
