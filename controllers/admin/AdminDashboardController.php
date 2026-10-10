<?php
/**
 * ZOUZA SHOP - Contrôleur de Test Espace Administration
 *
 * Sert uniquement à valider l'autorisation par rôle (rôle admin requis).
 * La construction du véritable tableau de bord fera l'objet d'une phase ultérieure.
 */

require_once __DIR__ . '/../../core/Controller.php';

class AdminDashboardController extends Controller
{
    /**
     * Point d'accès protégé vérifiant le rôle administrateur
     */
    public function index(): void
    {
        // 1. Exige le rôle administrateur (redirige vers /login si non connecté, ou 403 si rôle insuffisant)
        $this->requireAdmin();

        // 2. Affiche la vue de confirmation d'autorisation avec le layout d'administration
        $this->render('backoffice/dashboard', [
            'pageTitle' => 'Tableau de Bord Administration | ' . APP_NAME,
        ], 'admin');
    }
}
