<?php
/**
 * ZOUZA SHOP - Contrôleur du Tableau de Bord d'Administration
 *
 * Consolide les statistiques clés de la boutique :
 * - Chiffre d'affaires total et compteurs de commandes
 * - Répartition des commandes par statut
 * - Volumes de clients enregistrés, produits et catégories
 * - Récapitulatif des dernières commandes reçues
 * - Palmarès des meilleures ventes de Zouza
 *
 * Accès strictement réservé aux utilisateurs ayant le rôle 'admin'.
 */

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../models/Category.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Order.php';

class AdminDashboardController extends Controller
{
    private Product $productModel;
    private Category $categoryModel;
    private User $userModel;
    private Order $orderModel;

    public function __construct()
    {
        // 1. Protection stricte : rôle administrateur obligatoire
        $this->requireAdmin();

        $this->productModel  = new Product();
        $this->categoryModel = new Category();
        $this->userModel     = new User();
        $this->orderModel    = new Order();
    }

    /**
     * Affiche la vue principale du tableau de bord avec l'ensemble des indicateurs réels
     */
    public function index(): void
    {
        // Indicateurs globaux du catalogue et de la clientèle
        $totalProducts          = $this->productModel->countAll();
        $totalAvailableProducts = $this->productModel->countAvailable();
        $totalCategories        = $this->categoryModel->countAll();
        $totalCustomers         = $this->userModel->countCustomers();

        // Indicateurs financiers et de commandes
        $totalOrders  = $this->orderModel->countAll();
        $totalRevenue = $this->orderModel->getTotalRevenue(true); // Exclut les commandes annulées
        $statusCounts = $this->orderModel->getStatusCounts();

        // Récapitulatifs opérationnels récents
        $recentOrders = $this->orderModel->getRecentOrders(5);
        $bestSellers  = $this->orderModel->getBestSellingProducts(5);

        $this->render('backoffice/dashboard', [
            'pageTitle'              => 'Tableau de Bord Administration | ' . APP_NAME,
            'totalProducts'          => $totalProducts,
            'totalAvailableProducts' => $totalAvailableProducts,
            'totalCategories'        => $totalCategories,
            'totalCustomers'         => $totalCustomers,
            'totalOrders'            => $totalOrders,
            'totalRevenue'           => $totalRevenue,
            'statusCounts'           => $statusCounts,
            'recentOrders'           => $recentOrders,
            'bestSellers'            => $bestSellers,
            'allowedStatuses'        => Order::ALLOWED_STATUSES,
            'statusLabels'           => Order::STATUS_LABELS,
        ], 'admin');
    }
}
