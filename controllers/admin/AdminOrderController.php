<?php
/**
 * ZOUZA SHOP - Contrôleur d'Administration des Commandes
 *
 * Gère l'espace de gestion des commandes réservé aux administrateurs :
 * listing avec filtres par statut et recherche, consultation détaillée des lignes,
 * et mise à jour du statut avec validation stricte par jeton CSRF et liste blanche.
 */

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/Order.php';

class AdminOrderController extends Controller
{
    private Order $orderModel;

    public function __construct()
    {
        // 1. Exige impérativement le rôle administrateur pour l'ensemble des actions
        $this->requireAdmin();

        $this->orderModel = new Order();
    }

    /**
     * Affiche la liste des commandes avec filtres par statut et recherche textuelle
     */
    public function index(): void
    {
        $statusParam = trim($_GET['status'] ?? '');
        $searchParam = trim($_GET['search'] ?? '');

        // Validation du filtre de statut
        $selectedStatus = null;
        if ($statusParam !== '' && in_array($statusParam, Order::ALLOWED_STATUSES, true)) {
            $selectedStatus = $statusParam;
        }

        $search = $searchParam !== '' ? $searchParam : null;

        // Récupération des commandes filtrées et des compteurs globaux
        $orders       = $this->orderModel->getAllOrders($selectedStatus, $search);
        $statusCounts = $this->orderModel->getStatusCounts();

        $this->render('backoffice/orders/index', [
            'pageTitle'      => 'Gestion des Commandes | ' . APP_NAME,
            'orders'         => $orders,
            'currentStatus'  => $selectedStatus,
            'search'         => $searchParam,
            'statusCounts'   => $statusCounts,
            'allowedStatuses'=> Order::ALLOWED_STATUSES,
            'statusLabels'   => Order::STATUS_LABELS,
        ], 'admin');
    }

    /**
     * Affiche la vue détaillée d'une commande
     */
    public function detail(): void
    {
        $orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$orderId || $orderId <= 0) {
            ErrorHandler::renderNotFound('Identifiant de commande manquant ou invalide.');
            return;
        }

        $order = $this->orderModel->findWithItemsById($orderId);

        if (!$order) {
            ErrorHandler::renderNotFound("La commande #{$orderId} est introuvable.");
            return;
        }

        $this->render('backoffice/orders/show', [
            'pageTitle'       => 'Détail Commande #' . $order['reference'] . ' | ' . APP_NAME,
            'order'           => $order,
            'allowedStatuses' => Order::ALLOWED_STATUSES,
            'statusLabels'    => Order::STATUS_LABELS,
        ], 'admin');
    }

    /**
     * Traite la mise à jour du statut d'une commande
     */
    public function updateStatus(): void
    {
        // 1. Validation CSRF obligatoire
        $this->validateCsrf();

        $orderId   = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
        $newStatus = trim($_POST['statut'] ?? '');

        if (!$orderId || $orderId <= 0) {
            Session::setFlash('error', 'Identifiant de commande invalide.');
            $this->redirect('/admin/orders');
        }

        // Vérification de l'existence de la commande
        $order = $this->orderModel->findById($orderId);
        if (!$order) {
            Session::setFlash('error', 'Commande introuvable.');
            $this->redirect('/admin/orders');
        }

        // Validation stricte du statut soumis contre l'ENUM MySQL
        if (!in_array($newStatus, Order::ALLOWED_STATUSES, true)) {
            Session::setFlash('error', "Le statut sélectionné '{$newStatus}' n'est pas autorisé.");
            $this->redirect('/admin/orders/detail?id=' . $orderId);
        }

        try {
            $this->orderModel->updateStatus($orderId, $newStatus);
            $label = Order::STATUS_LABELS[$newStatus] ?? $newStatus;
            Session::setFlash('success', "Le statut de la commande #{$order['reference']} a été mis à jour avec succès : {$label}.");
        } catch (Throwable $e) {
            error_log('Erreur lors du changement de statut de commande : ' . $e->getMessage());
            Session::setFlash('error', 'Une erreur est survenue lors de la mise à jour du statut.');
        }

        $this->redirect('/admin/orders/detail?id=' . $orderId);
    }
}
