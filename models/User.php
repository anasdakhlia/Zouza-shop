<?php
/**
 * ZOUZA SHOP - Modèle Utilisateur
 *
 * Gère les opérations de base de données relatives aux utilisateurs et administrateurs.
 * Utilise strictement des requêtes préparées via PDO.
 */

require_once __DIR__ . '/../core/Model.php';

class User extends Model
{
    /**
     * Recherche un utilisateur par son adresse email
     *
     * @param string $email
     * @return array|null Données de l'utilisateur ou null si introuvable
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT user_id, nom, prenom, email, telephone, password, role, created_at
                FROM users
                WHERE email = :email
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => mb_strtolower(trim($email))]);

        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Recherche un utilisateur par son identifiant unique
     *
     * @param int $userId
     * @return array|null
     */
    public function findById(int $userId): ?array
    {
        $sql = "SELECT user_id, nom, prenom, email, telephone, password, role, created_at
                FROM users
                WHERE user_id = :user_id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);

        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Crée un nouvel utilisateur avec mot de passe haché
     *
     * @param array $data ['nom', 'prenom', 'email', 'telephone', 'password', 'role']
     * @return int Identifiant de l'utilisateur créé
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO users (nom, prenom, email, telephone, password, role, created_at)
                VALUES (:nom, :prenom, :email, :telephone, :password, :role, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nom'       => trim($data['nom']),
            ':prenom'    => trim($data['prenom']),
            ':email'     => mb_strtolower(trim($data['email'])),
            ':telephone' => !empty($data['telephone']) ? trim($data['telephone']) : null,
            ':password'  => password_hash($data['password'], PASSWORD_DEFAULT),
            ':role'      => in_array($data['role'] ?? 'customer', ['admin', 'customer'], true) ? $data['role'] : 'customer',
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Met à jour les informations du profil utilisateur
     *
     * @param int $userId
     * @param array $data ['nom', 'prenom', 'email', 'telephone']
     * @return bool
     */
    public function updateProfile(int $userId, array $data): bool
    {
        $sql = "UPDATE users
                SET nom = :nom,
                    prenom = :prenom,
                    email = :email,
                    telephone = :telephone
                WHERE user_id = :user_id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nom'       => trim($data['nom']),
            ':prenom'    => trim($data['prenom']),
            ':email'     => mb_strtolower(trim($data['email'])),
            ':telephone' => !empty($data['telephone']) ? trim($data['telephone']) : null,
            ':user_id'   => $userId,
        ]);
    }

    /**
     * Met à jour le mot de passe d'un utilisateur
     *
     * @param int $userId
     * @param string $passwordHash
     * @return bool
     */
    public function updatePassword(int $userId, string $passwordHash): bool
    {
        $sql = "UPDATE users SET password = :password WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':password' => $passwordHash,
            ':user_id'  => $userId,
        ]);
    }

    /**
     * Vérifie si une adresse email est déjà utilisée par un autre compte
     *
     * @param string $email
     * @param int $excludeUserId ID de l'utilisateur actuel à exclure
     * @return bool
     */
    public function isEmailTaken(string $email, int $excludeUserId): bool
    {
        $sql = "SELECT COUNT(*) FROM users WHERE email = :email AND user_id != :exclude_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':email'      => mb_strtolower(trim($email)),
            ':exclude_id' => $excludeUserId,
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Récupère la dernière adresse de livraison connue de l'utilisateur à partir de ses commandes
     *
     * @param int $userId
     * @return array|null ['adresse' => ..., 'ville' => ...] ou null
     */
    public function getLatestAddress(int $userId): ?array
    {
        $sql = "SELECT adresse, ville
                FROM orders
                WHERE user_id = :user_id
                  AND adresse IS NOT NULL
                  AND adresse != ''
                ORDER BY date_commande DESC, order_id DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);

        $res = $stmt->fetch();
        return $res ?: null;
    }
}
