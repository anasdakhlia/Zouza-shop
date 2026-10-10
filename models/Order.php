<?php
/**
 * ZOUZA SHOP - Modèle Commande (Order)
 *
 * Gère la persistance et la consultation des commandes et de leurs lignes.
 * Utilise strictement des requêtes préparées via PDO et encapsule
 * la création de commande dans une transaction PDO atomique.
 */

require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/Product.php';

class Order extends Model
{
    private Product $productModel;

    /**
     * Liste stricte des statuts autorisés (alignée sur l'ENUM MySQL de la table orders)
     */
    public const ALLOWED_STATUSES = [
        'pending',
        'confirmed',
        'preparing',
        'ready',
        'delivered',
        'cancelled',
    ];

    /**
     * Libellés français conviviaux pour l'affichage des statuts
     */
    public const STATUS_LABELS = [
        'pending'   => 'En attente',
        'confirmed' => 'Confirmée',
        'preparing' => 'En préparation',
        'ready'     => 'Prête',
        'delivered' => 'Livrée',
        'cancelled' => 'Annulée',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->productModel = new Product();
    }

    /**
     * Génère une référence de commande unique et conviviale
     * Format : ZS-YYYYMMDD-XXXX (ex: ZS-20261010-A1B2)
     *
     * @return string
     */
    public function generateReference(): string
    {
        $datePart = date('Ymd');
        $randomPart = strtoupper(bin2hex(random_bytes(2)));
        $reference = sprintf('ZS-%s-%s', $datePart, $randomPart);

        // Vérifier l'unicité au cas où
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM orders WHERE reference = :ref');
        $stmt->execute([':ref' => $reference]);
        if ((int)$stmt->fetchColumn() > 0) {
            return $this->generateReference();
        }

        return $reference;
    }

    /**
     * Crée une commande et ses lignes associées dans une transaction PDO atomique.
     * Les prix unitaires sont interrogés directement depuis la base de données
     * pour garantir qu'aucun prix falsifié ne soit enregistré.
     *
     * @param array $customerData Coordonnées et options client
     * @param array $cartItems Lignes du panier actuel
     * @param int|null $userId Identifiant de l'utilisateur si connecté
     * @return array Informations sur la commande créée ['order_id', 'reference', 'total']
     * @throws RuntimeException En cas de produit indisponible ou d'erreur technique
     */
    public function createOrder(array $customerData, array $cartItems, ?int $userId = null): array
    {
        if (empty($cartItems)) {
            throw new RuntimeException('Le panier est vide. Impossible de créer la commande.');
        }

        $this->db->beginTransaction();

        try {
            $totalOrder = 0.0;
            $validatedItems = [];

            // 1. Validation de chaque produit en temps réel depuis la base de données
            foreach ($cartItems as $item) {
                $productId = (int)($item['product']['product_id'] ?? 0);
                $quantity  = (int)($item['quantity'] ?? 0);

                if ($productId <= 0 || $quantity <= 0) {
                    throw new RuntimeException('Un article du panier présente des données invalides.');
                }

                // Récupération fraîche du produit depuis la BDD (avec verrouillage implicite / lecture directe)
                $product = $this->productModel->getById($productId);

                if (!$product) {
                    throw new RuntimeException("Le produit ID {$productId} n'existe plus dans le catalogue.");
                }

                if ((int)$product['disponible'] !== 1) {
                    throw new RuntimeException("Le produit '{$product['nom']}' n'est plus disponible actuellement.");
                }

                $unitPrice = (float)$product['prix'];
                $subtotal  = $unitPrice * $quantity;
                $totalOrder += $subtotal;

                $validatedItems[] = [
                    'product_id'    => $productId,
                    'nom'           => $product['nom'],
                    'quantite'      => $quantity,
                    'prix_unitaire' => $unitPrice,
                    'sous_total'    => $subtotal,
                ];
            }

            // 2. Génération de la référence unique
            $reference = $this->generateReference();

            // 3. Insertion de la commande principale
            $sqlOrder = "INSERT INTO orders (
                            reference, user_id, nom_client, prenom_client,
                            telephone, email, adresse, ville,
                            type_livraison, total, statut, notes, date_commande
                         ) VALUES (
                            :reference, :user_id, :nom_client, :prenom_client,
                            :telephone, :email, :adresse, :ville,
                            :type_livraison, :total, 'pending', :notes, NOW()
                         )";

            $stmtOrder = $this->db->prepare($sqlOrder);
            $stmtOrder->execute([
                ':reference'      => $reference,
                ':user_id'        => $userId,
                ':nom_client'     => trim($customerData['nom_client']),
                ':prenom_client'  => trim($customerData['prenom_client']),
                ':telephone'      => trim($customerData['telephone']),
                ':email'          => mb_strtolower(trim($customerData['email'])),
                ':adresse'        => !empty($customerData['adresse']) ? trim($customerData['adresse']) : null,
                ':ville'          => !empty($customerData['ville']) ? trim($customerData['ville']) : null,
                ':type_livraison' => in_array($customerData['type_livraison'] ?? 'livraison', ['livraison', 'a_emporter'], true)
                                      ? $customerData['type_livraison']
                                      : 'livraison',
                ':total'          => $totalOrder,
                ':notes'          => !empty($customerData['notes']) ? trim($customerData['notes']) : null,
            ]);

            $orderId = (int)$this->db->lastInsertId();

            // 4. Insertion de chaque ligne de commande avec snapshot de prix
            $sqlItem = "INSERT INTO order_items (
                            order_id, product_id, quantite, prix_unitaire, sous_total
                        ) VALUES (
                            :order_id, :product_id, :quantite, :prix_unitaire, :sous_total
                        )";
            $stmtItem = $this->db->prepare($sqlItem);

            foreach ($validatedItems as $vItem) {
                $stmtItem->execute([
                    ':order_id'      => $orderId,
                    ':product_id'    => $vItem['product_id'],
                    ':quantite'      => $vItem['quantite'],
                    ':prix_unitaire' => $vItem['prix_unitaire'],
                    ':sous_total'    => $vItem['sous_total'],
                ]);
            }

            // 5. Validation atomique de la transaction
            $this->db->commit();

            return [
                'order_id'  => $orderId,
                'reference' => $reference,
                'total'     => $totalOrder,
            ];

        } catch (Throwable $e) {
            // Annulation complète en cas d'incident
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Recherche une commande par sa référence unique
     *
     * @param string $reference
     * @return array|null
     */
    public function findByReference(string $reference): ?array
    {
        $sql = "SELECT o.*, u.email AS user_email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.user_id
                WHERE o.reference = :reference
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':reference' => trim($reference)]);

        $order = $stmt->fetch();
        return $order ?: null;
    }

    /**
     * Recherche une commande par son identifiant unique
     *
     * @param int $orderId
     * @return array|null
     */
    public function findById(int $orderId): ?array
    {
        $sql = "SELECT o.*, u.email AS user_email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.user_id
                WHERE o.order_id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $orderId]);

        $order = $stmt->fetch();
        return $order ?: null;
    }

    /**
     * Récupère une commande et ses lignes complètes par référence
     *
     * @param string $reference
     * @return array|null
     */
    public function findWithItemsByReference(string $reference): ?array
    {
        $order = $this->findByReference($reference);
        if (!$order) {
            return null;
        }

        $order['items'] = $this->getItems((int)$order['order_id']);
        return $order;
    }

    /**
     * Récupère une commande et ses lignes complètes par identifiant
     *
     * @param int $orderId
     * @return array|null
     */
    public function findWithItemsById(int $orderId): ?array
    {
        $order = $this->findById($orderId);
        if (!$order) {
            return null;
        }

        $order['items'] = $this->getItems($orderId);
        return $order;
    }

    /**
     * Récupère les lignes d'une commande avec les détails du produit au moment présent
     *
     * @param int $orderId
     * @return array
     */
    public function getItems(int $orderId): array
    {
        $sql = "SELECT oi.order_item_id, oi.order_id, oi.product_id,
                       oi.quantite, oi.prix_unitaire, oi.sous_total,
                       p.nom AS product_nom, p.slug AS product_slug,
                       p.image AS product_image, c.nom AS category_name
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.product_id
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE oi.order_id = :order_id
                ORDER BY oi.order_item_id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':order_id' => $orderId]);

        return $stmt->fetchAll();
    }

    /**
     * Récupère la liste des commandes d'un utilisateur
     *
     * @param int $userId
     * @return array
     */
    public function getByUserId(int $userId): array
    {
        $sql = "SELECT o.order_id, o.reference, o.type_livraison, o.total,
                       o.statut, o.date_commande,
                       COUNT(oi.order_item_id) AS items_count
                FROM orders o
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                WHERE o.user_id = :user_id
                GROUP BY o.order_id
                ORDER BY o.date_commande DESC, o.order_id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll();
    }

    /**
     * Récupère la liste de toutes les commandes pour l'administration
     * avec filtres optionnels par statut et recherche par mot-clé.
     *
     * @param string|null $status Statut à filtrer (validé par liste blanche)
     * @param string|null $search Terme de recherche (référence, client, email, téléphone)
     * @return array
     */
    public function getAllOrders(?string $status = null, ?string $search = null): array
    {
        $sql = "SELECT o.order_id, o.reference, o.user_id, o.nom_client, o.prenom_client,
                       o.telephone, o.email, o.type_livraison, o.total, o.statut,
                       o.date_commande,
                       COUNT(oi.order_item_id) AS items_count
                FROM orders o
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                WHERE 1=1";

        $params = [];

        // Filtre par statut si valide
        if (!empty($status) && in_array($status, self::ALLOWED_STATUSES, true)) {
            $sql .= " AND o.statut = :status";
            $params[':status'] = $status;
        }

        // Filtre par recherche textuelle (référence, nom, prénom, email, téléphone)
        if (!empty($search)) {
            $sql .= " AND (
                        o.reference LIKE :s1
                        OR o.nom_client LIKE :s2
                        OR o.prenom_client LIKE :s3
                        OR o.email LIKE :s4
                        OR o.telephone LIKE :s5
                    )";
            $searchVal = '%' . trim($search) . '%';
            $params[':s1'] = $searchVal;
            $params[':s2'] = $searchVal;
            $params[':s3'] = $searchVal;
            $params[':s4'] = $searchVal;
            $params[':s5'] = $searchVal;
        }

        $sql .= " GROUP BY o.order_id
                  ORDER BY o.date_commande DESC, o.order_id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Calcule le nombre total de commandes groupé par statut
     * Utile pour afficher les compteurs sur les onglets de filtres
     *
     * @return array Tableau associatif ['pending' => X, 'confirmed' => Y, ..., 'total' => Z]
     */
    public function getStatusCounts(): array
    {
        $counts = array_fill_keys(self::ALLOWED_STATUSES, 0);
        $counts['all'] = 0;

        $sql = "SELECT statut, COUNT(*) AS total
                FROM orders
                GROUP BY statut";

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();

        foreach ($rows as $row) {
            $st = $row['statut'];
            $ct = (int)$row['total'];
            if (isset($counts[$st])) {
                $counts[$st] = $ct;
            }
            $counts['all'] += $ct;
        }

        return $counts;
    }

    /**
     * Met à jour le statut d'une commande
     *
     * @param int $orderId
     * @param string $newStatus Nouveau statut (strictement validé)
     * @return bool
     * @throws InvalidArgumentException Si le statut n'est pas autorisé
     */
    public function updateStatus(int $orderId, string $newStatus): bool
    {
        if (!in_array($newStatus, self::ALLOWED_STATUSES, true)) {
            throw new InvalidArgumentException("Statut '{$newStatus}' invalide ou non autorisé.");
        }

        $sql = "UPDATE orders SET statut = :statut WHERE order_id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':statut' => $newStatus,
            ':id'     => $orderId,
        ]);
    }
}
