<?php
/**
 * ZOUZA SHOP - Contrôleur des Commandes (Checkout & Orders)
 *
 * Gère le processus de commande client (checkout), la validation des données,
 * la création transactionnelle des commandes, la page de confirmation,
 * ainsi que l'historique "Mes Commandes" pour les clients authentifiés.
 * Intègre une protection rigoureuse contre les failles CSRF et IDOR.
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/User.php';

class OrderController extends Controller
{
    private Cart $cartModel;
    private Product $productModel;
    private Order $orderModel;
    private User $userModel;

    public function __construct()
    {
        $this->cartModel    = new Cart();
        $this->productModel = new Product();
        $this->orderModel   = new Order();
        $this->userModel    = new User();
    }

    /**
     * Affiche la page de validation de commande (formulaire + récapitulatif panier)
     */
    public function checkout(): void
    {
        // 1. Vérification que le panier n'est pas vide
        if ($this->cartModel->isEmpty()) {
            Session::setFlash('error', 'Votre panier est vide. Veuillez ajouter des délices avant de commander.');
            $this->redirect('/cart');
        }

        $items = $this->cartModel->getItems();
        if (empty($items)) {
            Session::setFlash('error', 'Aucun produit disponible dans votre panier.');
            $this->redirect('/cart');
        }

        $total = $this->cartModel->getTotal();

        // 2. Pré-remplissage des coordonnées si l'utilisateur est connecté
        $userData = null;
        if (Session::isLoggedIn()) {
            $currentUser = Session::user();
            $userData = $this->userModel->findById((int)$currentUser['user_id']);
        }

        // 3. Récupération des données précédentes en cas d'erreur de validation
        $oldInputs = $_SESSION['_checkout_old'] ?? [];
        $errors    = $_SESSION['_checkout_errors'] ?? [];
        unset($_SESSION['_checkout_old'], $_SESSION['_checkout_errors']);

        $this->render('frontoffice/orders/checkout', [
            'pageTitle' => 'Valider ma commande | ' . APP_NAME,
            'items'     => $items,
            'total'     => $total,
            'userData'  => $userData,
            'old'       => $oldInputs,
            'errors'    => $errors,
        ], 'front');
    }

    /**
     * Traite la soumission du formulaire de commande
     */
    public function processCheckout(): void
    {
        // 1. Protection CSRF stricte
        $this->validateCsrf();

        // 2. Vérification que le panier n'est pas vide
        if ($this->cartModel->isEmpty()) {
            Session::setFlash('error', 'Votre panier est vide.');
            $this->redirect('/cart');
        }

        $items = $this->cartModel->getItems();
        if (empty($items)) {
            Session::setFlash('error', 'Aucun article disponible dans votre panier.');
            $this->redirect('/cart');
        }

        // 3. Récupération et nettoyage des données soumises
        $prenom        = trim($_POST['prenom'] ?? '');
        $nom           = trim($_POST['nom'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $telephone     = trim($_POST['telephone'] ?? '');
        $typeLivraison = trim($_POST['type_livraison'] ?? 'livraison');
        $adresse       = trim($_POST['adresse'] ?? '');
        $ville         = trim($_POST['ville'] ?? '');
        $notes         = trim($_POST['notes'] ?? '');

        // 4. Validation côté serveur
        $errors = [];

        if ($prenom === '') {
            $errors['prenom'] = 'Le prénom est obligatoire.';
        } elseif (mb_strlen($prenom) > 100) {
            $errors['prenom'] = 'Le prénom ne doit pas dépasser 100 caractères.';
        }

        if ($nom === '') {
            $errors['nom'] = 'Le nom est obligatoire.';
        } elseif (mb_strlen($nom) > 100) {
            $errors['nom'] = 'Le nom ne doit pas dépasser 100 caractères.';
        }

        if ($email === '') {
            $errors['email'] = 'L\'adresse email est obligatoire.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 191) {
            $errors['email'] = 'Veuillez saisir une adresse email valide.';
        }

        if ($telephone === '') {
            $errors['telephone'] = 'Le numéro de téléphone est obligatoire.';
        } elseif (mb_strlen($telephone) > 30 || !preg_match('/^[+0-9\s\-()]{6,30}$/', $telephone)) {
            $errors['telephone'] = 'Veuillez saisir un numéro de téléphone valide (ex: +216 20 000 000).';
        }

        if (!in_array($typeLivraison, ['livraison', 'a_emporter'], true)) {
            $errors['type_livraison'] = 'Veuillez sélectionner un mode de livraison valide.';
        }

        if ($typeLivraison === 'livraison') {
            if ($adresse === '') {
                $errors['adresse'] = 'L\'adresse de livraison est obligatoire pour une livraison à domicile.';
            } elseif (mb_strlen($adresse) > 500) {
                $errors['adresse'] = 'L\'adresse est trop longue (500 caractères max).';
            }

            if ($ville === '') {
                $errors['ville'] = 'La ville de livraison est obligatoire.';
            } elseif (mb_strlen($ville) > 100) {
                $errors['ville'] = 'La ville ne doit pas dépasser 100 caractères.';
            }
        }

        if (mb_strlen($notes) > 1000) {
            $errors['notes'] = 'Les instructions de livraison ne doivent pas dépasser 1000 caractères.';
        }

        // Si erreurs de validation, recharger le formulaire avec messages
        if (!empty($errors)) {
            $_SESSION['_checkout_old']    = $_POST;
            $_SESSION['_checkout_errors'] = $errors;
            Session::setFlash('error', 'Veuillez corriger les erreurs signalées dans le formulaire.');
            $this->redirect('/checkout');
        }

        // 5. Création de la commande dans une transaction PDO
        try {
            $userId = Session::isLoggedIn() ? (int)Session::user()['user_id'] : null;

            $customerData = [
                'prenom_client'  => $prenom,
                'nom_client'     => $nom,
                'email'          => $email,
                'telephone'      => $telephone,
                'type_livraison' => $typeLivraison,
                'adresse'        => ($typeLivraison === 'livraison') ? $adresse : null,
                'ville'          => ($typeLivraison === 'livraison') ? $ville : null,
                'notes'          => $notes !== '' ? $notes : null,
            ];

            $orderResult = $this->orderModel->createOrder($customerData, $items, $userId);

            // 6. Succès de la transaction : vidage du panier et mémorisation du jeton invité
            $this->cartModel->clear();
            $_SESSION['last_order_reference'] = $orderResult['reference'];

            Session::setFlash('success', "Votre commande #{$orderResult['reference']} a été enregistrée avec succès !");
            $this->redirect('/order/confirmation?ref=' . urlencode($orderResult['reference']));

        } catch (Throwable $e) {
            // Le panier est préservé grâce au rollback dans createOrder()
            error_log('Erreur lors du traitement de la commande : ' . $e->getMessage());

            // Masquer les détails SQL/techniques internes en cas d'exception inattendue (anti-fuite d'informations)
            $userMessage = ($e instanceof RuntimeException)
                ? $e->getMessage()
                : 'Une erreur technique est survenue lors de l\'enregistrement de votre commande. Veuillez réessayer ultérieurement.';

            Session::setFlash('error', 'Impossible de valider la commande : ' . $userMessage);
            $_SESSION['_checkout_old'] = $_POST;
            $this->redirect('/checkout');
        }
    }

    /**
     * Affiche la page de confirmation de commande
     * Protégée contre les attaques IDOR.
     */
    public function confirmation(): void
    {
        $reference = trim($_GET['ref'] ?? '');

        if ($reference === '') {
            ErrorHandler::renderNotFound('Référence de commande manquante.');
            return;
        }

        $order = $this->orderModel->findWithItemsByReference($reference);
        if (!$order) {
            ErrorHandler::renderNotFound("La commande référence '{$reference}' est introuvable.");
            return;
        }

        // Contrôle d'autorisation anti-IDOR :
        // 1. Soit c'est l'invité qui vient tout juste de passer cette commande dans la session en cours
        $isSessionOwner = !empty($_SESSION['last_order_reference'])
                          && hash_equals($_SESSION['last_order_reference'], $reference);

        // 2. Soit c'est le client authentifié propriétaire de la commande
        $isAccountOwner = Session::isLoggedIn()
                          && !empty($order['user_id'])
                          && (int)Session::user()['user_id'] === (int)$order['user_id'];

        // 3. Soit c'est un administrateur
        $isAdmin = Session::isAdmin();

        if (!$isSessionOwner && !$isAccountOwner && !$isAdmin) {
            ErrorHandler::renderForbidden("Vous n'êtes pas autorisé à consulter cette commande.");
            return;
        }

        $this->render('frontoffice/orders/confirmation', [
            'pageTitle' => 'Confirmation de commande | ' . APP_NAME,
            'order'     => $order,
        ], 'front');
    }

    /**
     * Affiche l'historique des commandes d'un client connecté ("Mes Commandes")
     */
    public function myOrders(): void
    {
        $this->requireAuth();

        $userId = (int)Session::user()['user_id'];
        $orders = $this->orderModel->getByUserId($userId);

        $this->render('frontoffice/orders/my_orders', [
            'pageTitle' => 'Mes Commandes | ' . APP_NAME,
            'orders'    => $orders,
        ], 'front');
    }

    /**
     * Affiche le détail complet d'une commande
     * Protégé contre les attaques IDOR.
     */
    public function detail(): void
    {
        $orderId   = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $reference = trim($_GET['ref'] ?? '');

        $order = null;
        if ($orderId) {
            $order = $this->orderModel->findWithItemsById($orderId);
        } elseif ($reference !== '') {
            $order = $this->orderModel->findWithItemsByReference($reference);
        }

        if (!$order) {
            ErrorHandler::renderNotFound('Commande introuvable.');
            return;
        }

        // Contrôle d'autorisation anti-IDOR
        $isSessionOwner = !empty($_SESSION['last_order_reference'])
                          && hash_equals($_SESSION['last_order_reference'], $order['reference']);

        $isAccountOwner = Session::isLoggedIn()
                          && !empty($order['user_id'])
                          && (int)Session::user()['user_id'] === (int)$order['user_id'];

        $isAdmin = Session::isAdmin();

        if (!$isSessionOwner && !$isAccountOwner && !$isAdmin) {
            ErrorHandler::renderForbidden("Vous n'êtes pas autorisé à consulter cette commande.");
            return;
        }

        $this->render('frontoffice/orders/detail', [
            'pageTitle' => 'Détail de la commande #' . $order['reference'] . ' | ' . APP_NAME,
            'order'     => $order,
        ], 'front');
    }
}
