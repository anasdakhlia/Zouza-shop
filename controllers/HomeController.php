<?php
/**
 * ZOUZA SHOP - Contrôleur d'Accueil Minimal
 *
 * Sert de point de test et de validation pour le pipeline MVC.
 */

require_once __DIR__ . '/../core/Controller.php';

class HomeController extends Controller
{
    /**
     * Affiche la page d'accueil minimale
     */
    public function index(): void
    {
        $this->render('frontoffice/home', [
            'pageTitle' => 'Bienvenue chez ' . APP_NAME,
            'tagline'   => APP_TAGLINE,
        ]);
    }
}
