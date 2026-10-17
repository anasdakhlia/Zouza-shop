<?php
/**
 * ZOUZA SHOP - Modèle Produit
 *
 * Gère l'accès aux données des produits Zouza en base.
 * Utilise strictement des requêtes préparées via PDO.
 */

require_once __DIR__ . '/../core/Model.php';

class Product extends Model
{
    /**
     * Récupère tous les produits avec le nom de leur catégorie
     *
     * @return array
     */
    public function getAll(): array
    {
        $sql = "SELECT p.product_id, p.category_id, p.nom, p.slug, p.description,
                       p.prix, p.image, p.disponible, p.created_at, p.updated_at,
                       c.nom AS category_name, c.slug AS category_slug
                FROM products p
                INNER JOIN categories c ON p.category_id = c.category_id
                ORDER BY p.product_id DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Récupère tous les produits disponibles (pour le catalogue public)
     *
     * @return array
     */
    public function getAvailable(): array
    {
        $sql = "SELECT p.product_id, p.category_id, p.nom, p.slug, p.description,
                       p.prix, p.image, p.disponible, p.created_at,
                       c.nom AS category_name, c.slug AS category_slug
                FROM products p
                INNER JOIN categories c ON p.category_id = c.category_id
                WHERE p.disponible = 1
                ORDER BY p.product_id DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Récupère les produits d'une catégorie spécifique
     *
     * @param int $categoryId
     * @param bool $onlyAvailable Filtrer uniquement les produits disponibles
     * @return array
     */
    public function getByCategory(int $categoryId, bool $onlyAvailable = true): array
    {
        $sql = "SELECT p.product_id, p.category_id, p.nom, p.slug, p.description,
                       p.prix, p.image, p.disponible, p.created_at,
                       c.nom AS category_name, c.slug AS category_slug
                FROM products p
                INNER JOIN categories c ON p.category_id = c.category_id
                WHERE p.category_id = :category_id";

        if ($onlyAvailable) {
            $sql .= " AND p.disponible = 1";
        }

        $sql .= " ORDER BY p.product_id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':category_id' => $categoryId]);

        return $stmt->fetchAll();
    }

    /**
     * Recherche un produit par son identifiant unique
     *
     * @param int $productId
     * @return array|null
     */
    public function getById(int $productId): ?array
    {
        $sql = "SELECT p.product_id, p.category_id, p.nom, p.slug, p.description,
                       p.prix, p.image, p.disponible, p.created_at, p.updated_at,
                       c.nom AS category_name, c.slug AS category_slug
                FROM products p
                INNER JOIN categories c ON p.category_id = c.category_id
                WHERE p.product_id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $productId]);

        $product = $stmt->fetch();
        return $product ?: null;
    }

    /**
     * Recherche un produit par son slug
     *
     * @param string $slug
     * @return array|null
     */
    public function getBySlug(string $slug): ?array
    {
        $sql = "SELECT p.product_id, p.category_id, p.nom, p.slug, p.description,
                       p.prix, p.image, p.disponible, p.created_at, p.updated_at,
                       c.nom AS category_name, c.slug AS category_slug
                FROM products p
                INNER JOIN categories c ON p.category_id = c.category_id
                WHERE p.slug = :slug
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':slug' => trim($slug)]);

        $product = $stmt->fetch();
        return $product ?: null;
    }

    /**
     * Crée un nouveau produit
     *
     * @param array $data ['category_id', 'nom', 'description', 'prix', 'image', 'disponible']
     * @return int Identifiant du produit créé
     */
    public function create(array $data): int
    {
        $nom = trim($data['nom'] ?? '');
        $slug = $this->generateSlug($nom);
        $description = isset($data['description']) && trim($data['description']) !== ''
            ? trim($data['description'])
            : null;
        $image = !empty($data['image']) ? trim($data['image']) : null;
        $disponible = isset($data['disponible']) ? (int)$data['disponible'] : 1;
        $prix = (float)($data['prix'] ?? 0);

        $sql = "INSERT INTO products (category_id, nom, slug, description, prix, image, disponible, created_at, updated_at)
                VALUES (:category_id, :nom, :slug, :description, :prix, :image, :disponible, NOW(), NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':category_id' => (int)$data['category_id'],
            ':nom'         => $nom,
            ':slug'        => $slug,
            ':description' => $description,
            ':prix'        => $prix,
            ':image'       => $image,
            ':disponible'  => $disponible,
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Met à jour un produit existant
     *
     * @param int $productId
     * @param array $data
     * @return bool
     */
    public function update(int $productId, array $data): bool
    {
        $nom = trim($data['nom'] ?? '');
        $slug = $this->generateSlug($nom, $productId);
        $description = isset($data['description']) && trim($data['description']) !== ''
            ? trim($data['description'])
            : null;
        $disponible = isset($data['disponible']) ? (int)$data['disponible'] : 1;
        $prix = (float)($data['prix'] ?? 0);

        // Si une nouvelle image est spécifiée, on la met à jour, sinon on garde l'existante
        $updateImage = array_key_exists('image', $data);

        $sql = "UPDATE products
                SET category_id = :category_id,
                    nom = :nom,
                    slug = :slug,
                    description = :description,
                    prix = :prix,
                    disponible = :disponible,
                    updated_at = NOW()";

        if ($updateImage) {
            $sql .= ", image = :image";
        }

        $sql .= " WHERE product_id = :id";

        $params = [
            ':category_id' => (int)$data['category_id'],
            ':nom'         => $nom,
            ':slug'        => $slug,
            ':description' => $description,
            ':prix'        => $prix,
            ':disponible'  => $disponible,
            ':id'          => $productId,
        ];

        if ($updateImage) {
            $params[':image'] = !empty($data['image']) ? trim($data['image']) : null;
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Supprime définitivement un produit de la base
     *
     * @param int $productId
     * @return bool
     */
    public function delete(int $productId): bool
    {
        $sql = "DELETE FROM products WHERE product_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $productId]);
    }

    /**
     * Bascule la disponibilité d'un produit
     *
     * @param int $productId
     * @return bool
     */
    public function toggleAvailability(int $productId): bool
    {
        $sql = "UPDATE products SET disponible = 1 - disponible, updated_at = NOW() WHERE product_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $productId]);
    }

    /**
     * Génère un slug URL-friendly unique pour un produit
     *
     * @param string $name
     * @param int|null $excludeId Identifiant à exclure de la vérification d'unicité
     * @return string
     */
    public function generateSlug(string $name, ?int $excludeId = null): string
    {
        $slug = mb_strtolower(trim($name), 'UTF-8');
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
            $slug = 'produit';
        }

        $baseSlug = $slug;
        $counter = 1;

        while (true) {
            $sql = "SELECT product_id FROM products WHERE slug = :slug";
            $params = [':slug' => $slug];

            if ($excludeId !== null) {
                $sql .= " AND product_id != :exclude_id";
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
     * Compte le nombre total de produits enregistrés
     *
     * @return int
     */
    public function countAll(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM products");
        return (int)$stmt->fetchColumn();
    }

    /**
     * Compte le nombre de produits actuellement disponibles à la vente
     *
     * @return int
     */
    public function countAvailable(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM products WHERE disponible = 1");
        return (int)$stmt->fetchColumn();
    }

    /**
     * Recherche et filtre les produits avec pagination et tri sécurisé
     *
     * @param array $filters [
     *     'search'        => string,
     *     'category_id'   => int,
     *     'category_slug' => string,
     *     'availability'  => string ('1', '0', 'all'),
     *     'sort'          => string ('newest', 'price_asc', 'price_desc', 'name_asc')
     * ]
     * @param int $page Numéro de page courant (1-indexé)
     * @param int $perPage Nombre d'articles par page
     * @return array [
     *     'items'       => array,
     *     'total'       => int,
     *     'page'        => int,
     *     'per_page'    => int,
     *     'total_pages' => int
     * ]
     */
    public function searchAndFilter(array $filters = [], int $page = 1, int $perPage = 6): array
    {
        $page    = max(1, $page);
        $perPage = max(1, min(100, $perPage));

        $where = [];
        $params = [];

        // 1. Recherche textuelle multi-champs (Nom, Description, Catégorie)
        $search = trim($filters['search'] ?? ($filters['q'] ?? ''));
        if ($search !== '') {
            $where[] = "(p.nom LIKE :s1 OR p.description LIKE :s2 OR c.nom LIKE :s3)";
            $searchVal = '%' . $search . '%';
            $params[':s1'] = $searchVal;
            $params[':s2'] = $searchVal;
            $params[':s3'] = $searchVal;
        }

        // 2. Filtrage par catégorie (par identifiant ou par slug)
        if (!empty($filters['category_id']) && (int)$filters['category_id'] > 0) {
            $where[] = "p.category_id = :cat_id";
            $params[':cat_id'] = (int)$filters['category_id'];
        } elseif (!empty($filters['category_slug'])) {
            $where[] = "c.slug = :cat_slug";
            $params[':cat_slug'] = trim($filters['category_slug']);
        }

        // 3. Filtrage par disponibilité
        $availability = $filters['availability'] ?? null;
        if ($availability === '1' || $availability === 'in_stock') {
            $where[] = "p.disponible = 1";
        } elseif ($availability === '0' || $availability === 'out_of_stock') {
            $where[] = "p.disponible = 0";
        } elseif ($availability === 'all') {
            // Aucun filtre : affiche disponibles et en rupture
        } else {
            // Par défaut dans le catalogue : uniquement les produits disponibles
            $where[] = "p.disponible = 1";
        }

        $whereSql = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

        // 4. Calcul du nombre total d'enregistrements correspondants
        $countSql = "SELECT COUNT(*)
                     FROM products p
                     INNER JOIN categories c ON p.category_id = c.category_id
                     {$whereSql}";

        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $totalItems = (int)$countStmt->fetchColumn();

        $totalPages = max(1, (int)ceil($totalItems / $perPage));

        // Ajuster la page si hors limites et des éléments existent
        if ($page > $totalPages && $totalItems > 0) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;

        // 5. Tri validé par liste blanche stricte
        $allowedSorts = [
            'newest'     => 'p.product_id DESC',
            'price_asc'  => 'p.prix ASC, p.product_id DESC',
            'price_desc' => 'p.prix DESC, p.product_id DESC',
            'name_asc'   => 'p.nom ASC, p.product_id DESC',
            'name_desc'  => 'p.nom DESC, p.product_id DESC',
        ];
        $sortKey = $filters['sort'] ?? 'newest';
        $orderClause = $allowedSorts[$sortKey] ?? $allowedSorts['newest'];

        // 6. Récupération des données paginées
        $dataSql = "SELECT p.product_id, p.category_id, p.nom, p.slug, p.description,
                           p.prix, p.image, p.disponible, p.created_at, p.updated_at,
                           c.nom AS category_name, c.slug AS category_slug
                    FROM products p
                    INNER JOIN categories c ON p.category_id = c.category_id
                    {$whereSql}
                    ORDER BY {$orderClause}
                    LIMIT :limit OFFSET :offset";

        $dataStmt = $this->db->prepare($dataSql);
        foreach ($params as $key => $val) {
            $dataStmt->bindValue($key, $val);
        }
        $dataStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $dataStmt->execute();

        $items = $dataStmt->fetchAll();

        return [
            'items'       => $items,
            'total'       => $totalItems,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => $totalPages,
        ];
    }
}
