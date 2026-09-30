<?php
/**
 * ZOUZA SHOP - Contrôleur d'Authentification
 *
 * Gère l'affichage du formulaire de connexion, la validation des identifiants,
 * la vérification sécurisée du mot de passe et la déconnexion.
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Affiche le formulaire de connexion
     */
    public function showLogin(): void
    {
        // Si déjà connecté, rediriger selon le rôle
        if (Session::isLoggedIn()) {
            if (Session::isAdmin()) {
                $this->redirect('/admin');
            }
            $this->redirect('/');
        }

        $this->render('auth/login', [
            'pageTitle' => 'Connexion | ' . APP_NAME,
        ], null); // Rendu autonome sans layout englobant
    }

    /**
     * Traite la soumission du formulaire de connexion
     */
    public function login(): void
    {
        // Si déjà connecté, rediriger selon le rôle
        if (Session::isLoggedIn()) {
            if (Session::isAdmin()) {
                $this->redirect('/admin');
            }
            $this->redirect('/');
        }

        // 1. Validation de la protection CSRF
        $this->validateCsrf();

        // 2. Récupération et nettoyage des données d'entrée
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // 3. Validation des champs requis
        if ($email === '' || $password === '') {
            Session::setFlash('error', 'Veuillez saisir votre adresse email et votre mot de passe.');
            $this->redirect('/login');
        }

        // 4. Validation du format d'email (message générique pour éviter l'énumération)
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('error', 'Email ou mot de passe incorrect.');
            $this->redirect('/login');
        }

        try {
            // 5. Recherche de l'utilisateur par email
            $user = $this->userModel->findByEmail($email);

            // 6. Vérification sécurisée du mot de passe avec password_verify()
            if (!$user || !password_verify($password, $user['password'])) {
                // Message d'erreur strictement générique (anti-énumération)
                Session::setFlash('error', 'Email ou mot de passe incorrect.');
                $this->redirect('/login');
            }

            // 7. Connexion réussie : régénération de session
            Session::login($user);

            // 8. Redirection selon le rôle
            if ($user['role'] === 'admin') {
                $this->redirect('/admin');
            }

            Session::setFlash('success', 'Bienvenue, ' . htmlspecialchars($user['prenom'], ENT_QUOTES, 'UTF-8') . ' !');
            $this->redirect('/');

        } catch (Throwable $e) {
            // Journalisation de l'erreur technique sans fuite d'informations
            error_log('Erreur technique lors de l\'authentification : ' . $e->getMessage());
            Session::setFlash('error', 'Un incident technique empêche la connexion. Veuillez réessayer.');
            $this->redirect('/login');
        }
    }

    /**
     * Déconnecte l'utilisateur et détruit la session
     */
    public function logout(): void
    {
        Session::logout();
        $this->redirect('/login');
    }
}
