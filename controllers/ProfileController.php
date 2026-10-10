<?php
/**
 * ZOUZA SHOP - Contrôleur du Profil Client
 *
 * Gère l'espace personnel du client authentifié :
 * consultation des informations, mise à jour des coordonnées personnelles,
 * changement de mot de passe sécurisé et consultation de l'historique des commandes.
 * Toutes les actions exigent une authentification stricte et une protection CSRF.
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Order.php';

class ProfileController extends Controller
{
    private User $userModel;
    private Order $orderModel;

    public function __construct()
    {
        // 1. Exige impérativement une authentification active
        $this->requireAuth();

        $this->userModel  = new User();
        $this->orderModel = new Order();
    }

    /**
     * Affiche la page du profil client
     */
    public function show(): void
    {
        $userId = (int)Session::user()['user_id'];
        $user = $this->userModel->findById($userId);

        if (!$user) {
            Session::logout();
            Session::setFlash('error', 'Votre compte est introuvable.');
            $this->redirect('/login');
        }

        // Récupère la dernière adresse de livraison connue à partir des commandes passées
        $latestAddress = $this->userModel->getLatestAddress($userId);

        // Récupère les commandes du client
        $orders = $this->orderModel->getByUserId($userId);

        $this->render('frontoffice/profile/index', [
            'pageTitle'     => 'Mon Profil | ' . APP_NAME,
            'user'          => $user,
            'latestAddress' => $latestAddress,
            'orders'        => $orders,
        ], 'front');
    }

    /**
     * Traite la mise à jour des coordonnées personnelles (nom, prénom, email, téléphone)
     */
    public function update(): void
    {
        // 1. Protection CSRF obligatoire
        $this->validateCsrf();

        $userId = (int)Session::user()['user_id'];

        $prenom    = trim($_POST['prenom'] ?? '');
        $nom       = trim($_POST['nom'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');

        // 2. Validation côté serveur
        if ($prenom === '' || mb_strlen($prenom) > 100) {
            Session::setFlash('error', 'Veuillez renseigner un prénom valide (100 caractères max).');
            $this->redirect('/profile');
        }

        if ($nom === '' || mb_strlen($nom) > 100) {
            Session::setFlash('error', 'Veuillez renseigner un nom valide (100 caractères max).');
            $this->redirect('/profile');
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 191) {
            Session::setFlash('error', 'Veuillez fournir une adresse email valide.');
            $this->redirect('/profile');
        }

        if ($telephone !== '' && (mb_strlen($telephone) > 30 || !preg_match('/^[+0-9\s\-()]{6,30}$/', $telephone))) {
            Session::setFlash('error', 'Veuillez fournir un numéro de téléphone valide.');
            $this->redirect('/profile');
        }

        // 3. Vérification d'unicité de l'adresse email
        if ($this->userModel->isEmailTaken($email, $userId)) {
            Session::setFlash('error', 'Cette adresse email est déjà associée à un autre compte.');
            $this->redirect('/profile');
        }

        try {
            // 4. Mise à jour en base de données
            $this->userModel->updateProfile($userId, [
                'nom'       => $nom,
                'prenom'    => $prenom,
                'email'     => $email,
                'telephone' => $telephone !== '' ? $telephone : null,
            ]);

            // 5. Synchronisation de la session utilisateur
            $_SESSION['user']['nom']    = $nom;
            $_SESSION['user']['prenom'] = $prenom;
            $_SESSION['user']['email']  = mb_strtolower($email);

            Session::setFlash('success', 'Vos informations personnelles ont été mises à jour avec succès.');
        } catch (Throwable $e) {
            error_log('Erreur mise à jour profil : ' . $e->getMessage());
            Session::setFlash('error', 'Une erreur est survenue lors de la mise à jour de votre profil.');
        }

        $this->redirect('/profile');
    }

    /**
     * Traite le changement de mot de passe
     */
    public function updatePassword(): void
    {
        // 1. Protection CSRF obligatoire
        $this->validateCsrf();

        $userId = (int)Session::user()['user_id'];
        $user = $this->userModel->findById($userId);

        if (!$user) {
            $this->redirect('/login');
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // 2. Validation des champs obligatoires
        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            Session::setFlash('error', 'Veuillez remplir tous les champs relatifs au mot de passe.');
            $this->redirect('/profile');
        }

        // 3. Vérification du mot de passe actuel avec password_verify()
        if (!password_verify($currentPassword, $user['password'])) {
            Session::setFlash('error', 'Le mot de passe actuel est incorrect.');
            $this->redirect('/profile');
        }

        // 4. Vérification de la correspondance du nouveau mot de passe
        if ($newPassword !== $confirmPassword) {
            Session::setFlash('error', 'Le nouveau mot de passe et sa confirmation ne correspondent pas.');
            $this->redirect('/profile');
        }

        // 5. Validation de la longueur minimale
        if (mb_strlen($newPassword) < 6) {
            Session::setFlash('error', 'Le nouveau mot de passe doit comporter au moins 6 caractères.');
            $this->redirect('/profile');
        }

        try {
            // 6. Hachage sécurisé et mise à jour
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $this->userModel->updatePassword($userId, $newHash);

            Session::setFlash('success', 'Votre mot de passe a été modifié avec succès.');
        } catch (Throwable $e) {
            error_log('Erreur changement mot de passe : ' . $e->getMessage());
            Session::setFlash('error', 'Une erreur technique empêche la modification du mot de passe.');
        }

        $this->redirect('/profile');
    }
}
