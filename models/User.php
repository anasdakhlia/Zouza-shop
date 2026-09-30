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
        $sql = "SELECT user_id, nom, prenom, email, telephone, role, created_at
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
}
