<div class="container-fluid px-0">
    <!-- En-tête de bienvenue et actions rapides -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Tableau de Bord Administrateur</h1>
            <p class="text-muted mb-0 small">
                Bienvenue, <strong><?= e($currentUser['prenom'] . ' ' . $currentUser['nom']) ?></strong> &bull; Aperçu en direct de l'activité commerciale de <strong><?= APP_NAME ?></strong>
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill small">
                &#9679; Boutique en ligne active
            </span>
            <a href="<?= BASE_URL ?>/admin/products/create" class="btn btn-sm btn-zouza shadow-sm">
                + Nouveau Produit
            </a>
            <a href="<?= BASE_URL ?>/admin/orders" class="btn btn-sm btn-outline-dark shadow-sm">
                &#128220; Commandes
            </a>
        </div>
    </div>

    <!-- Indicateurs clés de performance (KPI Cards) -->
    <div class="row g-3 mb-4">
        <!-- Chiffre d'Affaires -->
        <div class="col-sm-6 col-xl-3">
            <div class="card admin-card p-3 h-100 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Chiffre d'Affaires</span>
                        <h3 class="fw-bold text-success mb-0 mt-1"><?= formatPrice($totalRevenue) ?></h3>
                        <small class="text-muted text-xs">Hors commandes annulées</small>
                    </div>
                    <div class="rounded-circle bg-success-subtle p-3 text-success fs-4">
                        &#128176;
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Commandes -->
        <div class="col-sm-6 col-xl-3">
            <div class="card admin-card p-3 h-100 border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Commandes</span>
                        <h3 class="fw-bold text-primary mb-0 mt-1"><?= (int)$totalOrders ?></h3>
                        <small class="text-muted text-xs">
                            <?= (int)($statusCounts['pending'] ?? 0) ?> en attente &bull; <?= (int)($statusCounts['preparing'] ?? 0) ?> en cuisine
                        </small>
                    </div>
                    <div class="rounded-circle bg-primary-subtle p-3 text-primary fs-4">
                        &#128220;
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Clients -->
        <div class="col-sm-6 col-xl-2">
            <div class="card admin-card p-3 h-100 border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Clients</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1"><?= (int)$totalCustomers ?></h3>
                        <small class="text-muted text-xs">Comptes enregistrés</small>
                    </div>
                    <div class="rounded-circle bg-info-subtle p-3 text-info fs-4">
                        &#128101;
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Produits -->
        <div class="col-sm-6 col-xl-2">
            <div class="card admin-card p-3 h-100 border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Produits</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1"><?= (int)$totalProducts ?></h3>
                        <small class="text-muted text-xs"><?= (int)$totalAvailableProducts ?> disponibles</small>
                    </div>
                    <div class="rounded-circle bg-warning-subtle p-3 text-warning fs-4">
                        &#129367;
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Catégories -->
        <div class="col-sm-6 col-xl-2">
            <div class="card admin-card p-3 h-100 border-start border-4 border-secondary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Catégories</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1"><?= (int)$totalCategories ?></h3>
                        <small class="text-muted text-xs">Gammes actives</small>
                    </div>
                    <div class="rounded-circle bg-secondary-subtle p-3 text-secondary fs-4">
                        &#128193;
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- État d'avancement des commandes (Order Pipeline) -->
    <div class="card admin-card p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h5 class="fw-bold text-dark mb-0">Répartition des Commandes par Statut</h5>
                <small class="text-muted">Cliquez sur un statut pour filtrer directement la liste des commandes</small>
            </div>
            <a href="<?= BASE_URL ?>/admin/orders" class="btn btn-sm btn-outline-secondary">
                Voir toutes (<?= (int)($statusCounts['all'] ?? 0) ?>) &rarr;
            </a>
        </div>

        <div class="row g-2">
            <?php
            $statusBadgesConfig = [
                'pending'   => ['bg' => 'bg-warning text-dark', 'label' => 'En attente', 'desc' => 'À valider'],
                'confirmed' => ['bg' => 'bg-info text-white', 'label' => 'Confirmées', 'desc' => 'Validées'],
                'preparing' => ['bg' => 'bg-primary text-white', 'label' => 'En préparation', 'desc' => 'En cuisine'],
                'ready'     => ['bg' => 'bg-teal text-white', 'label' => 'Prêtes', 'desc' => 'Prêtes à livrer', 'custom_style' => 'background-color: #20c997;'],
                'delivered' => ['bg' => 'bg-success text-white', 'label' => 'Livrées', 'desc' => 'Clôturées'],
                'cancelled' => ['bg' => 'bg-danger text-white', 'label' => 'Annulées', 'desc' => 'Rejetées'],
            ];
            ?>
            <?php foreach ($allowedStatuses as $st): ?>
                <?php
                $cfg = $statusBadgesConfig[$st] ?? ['bg' => 'bg-secondary text-white', 'label' => ucfirst($st), 'desc' => ''];
                $count = (int)($statusCounts[$st] ?? 0);
                $styleAttr = isset($cfg['custom_style']) ? 'style="' . $cfg['custom_style'] . '"' : '';
                ?>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= BASE_URL ?>/admin/orders?status=<?= urlencode($st) ?>" class="text-decoration-none">
                        <div class="p-3 rounded-3 text-center border transition-hover bg-light h-100">
                            <span class="badge <?= $cfg['bg'] ?> fs-5 px-3 py-2 rounded-pill mb-1 d-inline-block" <?= $styleAttr ?>>
                                <?= $count ?>
                            </span>
                            <div class="fw-semibold text-dark small"><?= e($cfg['label']) ?></div>
                            <small class="text-muted text-xs"><?= e($cfg['desc']) ?></small>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Synthèse Opérationnelle : Dernières Commandes et Meilleures Ventes -->
    <div class="row g-4 mb-4">
        <!-- Dernières Commandes -->
        <div class="col-lg-7">
            <div class="card admin-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">&#128220; Dernières Commandes Reçues</h5>
                        <small class="text-muted">Les 5 dernières commandes passées en ligne</small>
                    </div>
                    <a href="<?= BASE_URL ?>/admin/orders" class="btn btn-sm btn-outline-dark">
                        Gérer les commandes &rarr;
                    </a>
                </div>

                <?php if (empty($recentOrders)): ?>
                    <div class="alert alert-light text-center py-4 text-muted my-auto">
                        <span class="fs-1 d-block mb-2">&#128230;</span>
                        Aucune commande enregistrée pour le moment.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Référence</th>
                                    <th>Client</th>
                                    <th>Articles</th>
                                    <th>Total</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                    <?php
                                    $st = $order['statut'];
                                    $stClass = match($st) {
                                        'pending'   => 'bg-warning text-dark',
                                        'confirmed' => 'bg-info text-white',
                                        'preparing' => 'bg-primary text-white',
                                        'ready'     => 'bg-teal text-white',
                                        'delivered' => 'bg-success text-white',
                                        'cancelled' => 'bg-danger text-white',
                                        default     => 'bg-secondary text-white',
                                    };
                                    $stLabel = $statusLabels[$st] ?? ucfirst($st);
                                    ?>
                                    <tr>
                                        <td>
                                            <a href="<?= BASE_URL ?>/admin/orders/detail?id=<?= (int)$order['order_id'] ?>" class="fw-bold text-decoration-none text-dark">
                                                <?= e($order['reference']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= e($order['prenom_client'] . ' ' . $order['nom_client']) ?></div>
                                            <div class="text-muted text-xs"><?= e($order['telephone']) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?= (int)$order['items_count'] ?> article(s)
                                            </span>
                                        </td>
                                        <td class="fw-bold text-dark">
                                            <?= formatPrice($order['total']) ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $stClass ?> px-2 py-1 rounded-pill" <?= $st === 'ready' ? 'style="background-color: #20c997;"' : '' ?>>
                                                <?= e($stLabel) ?>
                                            </span>
                                        </td>
                                        <td class="text-muted text-xs">
                                            <?= date('d/m/Y H:i', strtotime($order['date_commande'])) ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= BASE_URL ?>/admin/orders/detail?id=<?= (int)$order['order_id'] ?>" class="btn btn-sm btn-outline-secondary py-0 px-2" title="Consulter la commande">
                                                Voir &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Meilleures Ventes / Recettes Populaires -->
        <div class="col-lg-5">
            <div class="card admin-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">&#129367; Recettes les Plus Vendues</h5>
                        <small class="text-muted">Top 5 selon les quantités commandées</small>
                    </div>
                    <a href="<?= BASE_URL ?>/admin/products" class="btn btn-sm btn-outline-dark">
                        Catalogue &rarr;
                    </a>
                </div>

                <?php if (empty($bestSellers)): ?>
                    <div class="alert alert-light text-center py-4 text-muted my-auto">
                        <span class="fs-1 d-block mb-2">&#127856;</span>
                        Aucune vente enregistrée pour le moment.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Produit</th>
                                    <th class="text-center">Qté vendue</th>
                                    <th class="text-end">Chiffre d'Affaires</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bestSellers as $idx => $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-light text-muted border rounded-circle" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center;">
                                                    #<?= $idx + 1 ?>
                                                </span>
                                                <div>
                                                    <a href="<?= BASE_URL ?>/admin/products/edit?id=<?= (int)$item['product_id'] ?>" class="fw-bold text-decoration-none text-dark d-block">
                                                        <?= e($item['nom']) ?>
                                                    </a>
                                                    <?php if (!empty($item['category_name'])): ?>
                                                        <span class="text-muted text-xs"><?= e($item['category_name']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1">
                                                <?= (int)$item['total_sold'] ?> boîtes
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            <?= formatPrice($item['total_revenue']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Raccourcis de Gestion Rapide -->
    <div class="row g-4 mb-4">
        <!-- Raccourci Produits -->
        <div class="col-md-4">
            <div class="card admin-card p-4 h-100 bg-white">
                <div class="d-flex align-items-center mb-3">
                    <span class="fs-1 me-3">&#129367;</span>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Catalogue Produits</h5>
                        <small class="text-muted">Recettes & Variétés de Zouza</small>
                    </div>
                </div>
                <p class="small text-muted mb-4">
                    Gérez vos <?= (int)$totalProducts ?> recettes de Zouza, leurs prix en dinars, descriptions, photos et disponibilité immédiate.
                </p>
                <div class="mt-auto d-flex gap-2">
                    <a href="<?= BASE_URL ?>/admin/products" class="btn btn-outline-dark btn-sm">
                        Gérer le catalogue &rarr;
                    </a>
                    <a href="<?= BASE_URL ?>/admin/products/create" class="btn btn-zouza btn-sm">
                        + Nouveau Produit
                    </a>
                </div>
            </div>
        </div>

        <!-- Raccourci Catégories -->
        <div class="col-md-4">
            <div class="card admin-card p-4 h-100 bg-white">
                <div class="d-flex align-items-center mb-3">
                    <span class="fs-1 me-3">&#128193;</span>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Familles & Catégories</h5>
                        <small class="text-muted">Organiser les gammes de pâtisserie</small>
                    </div>
                </div>
                <p class="small text-muted mb-4">
                    Structurez vos <?= (int)$totalCategories ?> catégories (Traditionnelle, Gourmande, Coffrets...) avec génération automatique de slugs.
                </p>
                <div class="mt-auto d-flex gap-2">
                    <a href="<?= BASE_URL ?>/admin/categories" class="btn btn-outline-dark btn-sm">
                        Gérer les familles &rarr;
                    </a>
                    <a href="<?= BASE_URL ?>/admin/categories/create" class="btn btn-zouza btn-sm">
                        + Catégorie
                    </a>
                </div>
            </div>
        </div>

        <!-- Raccourci Commandes -->
        <div class="col-md-4">
            <div class="card admin-card p-4 h-100 bg-white">
                <div class="d-flex align-items-center mb-3">
                    <span class="fs-1 me-3">&#128220;</span>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Commandes Clients</h5>
                        <small class="text-muted">Suivi, cuisine et livraison</small>
                    </div>
                </div>
                <p class="small text-muted mb-4">
                    Traitez les <?= (int)$totalOrders ?> commandes clients, coordonnez la livraison et modifiez les statuts en un clic avec protection CSRF.
                </p>
                <div class="mt-auto">
                    <a href="<?= BASE_URL ?>/admin/orders" class="btn btn-zouza btn-sm w-100">
                        Accéder aux commandes &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations de session administrateur -->
    <div class="card admin-card p-3 mb-2">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 small">
            <div>
                <span class="text-muted">Connecté en tant que :</span>
                <strong><?= e($currentUser['prenom'] . ' ' . $currentUser['nom']) ?></strong>
                <span class="text-muted">(<?= e($currentUser['email']) ?>)</span>
            </div>
            <div>
                <span class="text-muted">Rôle vérifié :</span>
                <span class="badge bg-success ms-1"><?= e($currentUser['role']) ?></span>
                <span class="text-muted ms-3">Identifiant système :</span>
                <strong class="ms-1">#<?= (int)$currentUser['user_id'] ?></strong>
            </div>
        </div>
    </div>
</div>
