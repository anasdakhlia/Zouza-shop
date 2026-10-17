<?php
/**
 * ZOUZA SHOP - Modèle Catégorie
 *
 * Gère l'accès aux données des catégories de pâtisseries.
 * Utilise strictement des requêtes préparées via PDO.
 */

require_once __DIR__ . '/../core/Model.php';

class Category extends Model
{
    /**
     * Récupère toutes les catégories triées par nom
     *
     * @return array
     */
    public function getAll(): array
    {
        $sql = "SELECT c.category_id, c.nom, c.slug, c.description, c.created_at,
                       COUNT(p.product_id) AS total_products
                FROM categories c
                LEFT JOIN products p ON c.category_id = p.category_id
                GROUP BY c.category_id
                ORDER BY c.nom ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Recherche une catégorie par son identifiant numérique
     *
     * @param int $categoryId
     * @return array|null
     */
    public function getById(int $categoryId): ?array
    {
        $sql = "SELECT category_id, nom, slug, description, created_at
                FROM categories
                WHERE category_id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $categoryId]);

        $category = $stmt->fetch();
        return $category ?: null;
    }

    /**
     * Recherche une catégorie par son slug
     *
     * @param string $slug
     * @return array|null
     */
    public function getBySlug(string $slug): ?array
    {
        $sql = "SELECT category_id, nom, slug, description, created_at
                FROM categories
                WHERE slug = :slug
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':slug' => trim($slug)]);

        $category = $stmt->fetch();
        return $category ?: null;
    }

    /**
     * Compte le nombre de produits rattachés à une catégorie
     *
     * @param int $categoryId
     * @return int
     */
    public function countProducts(int $categoryId): int
    {
        $sql = "SELECT COUNT(*) FROM products WHERE category_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $categoryId]);

        return (int)$stmt->fetchColumn();
    }

    /**
     * Vérifie si des produits dépendent de cette catégorie
     *
     * @param int $categoryId
     * @return bool
     */
    public function hasProducts(int $categoryId): bool
    {
        return $this->countProducts($categoryId) > 0;
    }

    /**
     * Crée une nouvelle catégorie
     *
     * @param array $data ['nom', 'description']
     * @return int Identifiant de la catégorie créée
     */
    public function create(array $data): int
    {
        $nom = trim($data['nom'] ?? '');
        $description = isset($data['description']) && trim($data['description']) !== ''
            ? trim($data['description'])
            : null;

        $slug = $this->generateSlug($nom);

        $sql = "INSERT INTO categories (nom, slug, description, created_at)
                VALUES (:nom, :slug, :description, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nom'         => $nom,
            ':slug'        => $slug,
            ':description' => $description,
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Met à jour une catégorie existante
     *
     * @param int $categoryId
     * @param array $data ['nom', 'description']
     * @return bool
     */
    public function update(int $categoryId, array $data): bool
    {
        $nom = trim($data['nom'] ?? '');
        $description = isset($data['description']) && trim($data['description']) !== ''
            ? trim($data['description'])
            : null;

        $slug = $this->generateSlug($nom, $categoryId);

        $sql = "UPDATE categories
                SET nom = :nom, slug = :slug, description = :description
                WHERE category_id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nom'         => $nom,
            ':slug'        => $slug,
            ':description' => $description,
            ':id'          => $categoryId,
        ]);
    }

    /**
     * Supprime une catégorie (échoue sécuritairement si des produits sont rattachés)
     *
     * @param int $categoryId
     * @return bool
     * @throws RuntimeException Si des produits sont associés
     */
    public function delete(int $categoryId): bool
    {
        if ($this->hasProducts($categoryId)) {
            throw new RuntimeException("Impossible de supprimer cette catégorie car des produits y sont associés.");
        }

        $sql = "DELETE FROM categories WHERE category_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $categoryId]);
    }

    /**
     * Génère un slug URL-friendly unique pour une catégorie
     *
     * @param string $name
     * @param int|null $excludeId Identifiant à exclure de la vérification d'unicité
     * @return string
     */
    public function generateSlug(string $name, ?int $excludeId = null): string
    {
        // 1. Translittération et nettoyage
        $slug = mb_strtolower(trim($name), 'UTF-8');
        // Remplacement des caractères accentués
        $translits = [
            'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'ç'=>'c',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i',
            'î'=>'i', 'ï'=>'i', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o',
            'ö'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y',
            ' '=>'-', '\''=>'-', '_'=>'-',
        ];
        $slug = strtr($slug, $translits);
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        if ($slug === '') {
            $slug = 'categorie';
        }

        // 2. Vérification d'unicité en base
        $baseSlug = $slug;
        $counter = 1;

        while (true) {
            $sql = "SELECT category_id FROM categories WHERE slug = :slug";
            $params = [':slug' => $slug];

            if ($excludeId !== null) {
                $sql .= " AND category_id != :exclude_id";
                $params[':exclude_id'] = $excludeId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            if (!$stmt->fetch()) {
                break;
            }

            $counter++;
            $slug = $baseSlug . '-' . $counter;
        }

        return $slug;
    }

    /**
     * Compte le nombre total de catégories enregistrées
     *
     * @return int
     */
    public function countAll(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM categories");
        return (int)$stmt->fetchColumn();
    }
}
