<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/" class="text-decoration-none text-zouza">Accueil</a></li>
        <li class="breadcrumb-item active" aria-current="page">Mes Commandes</li>
    </ol>
</nav>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Mon Historique de Commandes</h1>
        <p class="text-muted small mb-0">Retrouvez toutes vos commandes de Zouzas et suivez leur statut.</p>
    </div>
    <a href="<?= BASE_URL ?>/products" class="btn btn-sm btn-outline-secondary">
        Nouvelle commande &rarr;
    </a>
</div>

<?php if (empty($orders)): ?>
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 text-center my-4 bg-white card-zouza">
        <div class="py-4">
            <div class="display-3 mb-3 text-muted">&#128220;</div>
            <h2 class="h4 text-dark mb-2">Aucune commande enregistrée</h2>
            <p class="text-muted mb-4">Vous n'avez pas encore passé de commande avec votre compte client.</p>
            <a href="<?= BASE_URL ?>/products" class="btn btn-zouza px-4 py-2">
                Découvrir nos Zouzas artisanales &rarr;
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white card-zouza">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Référence</th>
                        <th>Date de commande</th>
                        <th>Mode</th>
                        <th class="text-center">Articles</th>
                        <th>Montant total</th>
                        <th class="text-center">Statut</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $ord): ?>
                        <?php
                            $statusBadge = match($ord['statut']) {
                                'pending'   => '<span class="badge bg-warning text-dark">En attente</span>',
                                'confirmed' => '<span class="badge bg-info text-dark">Confirmée</span>',
                                'preparing' => '<span class="badge bg-primary">En préparation</span>',
                                'ready'     => '<span class="badge bg-teal text-white" style="background-color: #20c997;">Prête</span>',
                                'delivered' => '<span class="badge bg-success">Livrée</span>',
                                'cancelled' => '<span class="badge bg-danger">Annulée</span>',
                                default     => '<span class="badge bg-secondary">' . e($ord['statut']) . '</span>',
                            };

                            $deliveryLabel = ($ord['type_livraison'] === 'a_emporter')
                                ? '<span class="badge bg-light text-dark border">&#127978; Retrait</span>'
                                : '<span class="badge bg-light text-dark border">&#128666; Livraison</span>';
                        ?>
                        <tr>
                            <td class="ps-4">
                                <strong class="text-dark"><?= e($ord['reference']) ?></strong>
                            </td>
                            <td>
                                <?= date('d/m/Y à H:i', strtotime($ord['date_commande'])) ?>
                            </td>
                            <td>
                                <?= $deliveryLabel ?>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill bg-light text-dark border">
                                    <?= (int)$ord['items_count'] ?> ligne(s)
                                </span>
                            </td>
                            <td class="fw-bold" style="color: #b86b35;">
                                <?= formatPrice($ord['total']) ?>
                            </td>
                            <td class="text-center">
                                <?= $statusBadge ?>
                            </td>
                            <td class="text-end pe-4">
                                <a href="<?= BASE_URL ?>/orders/detail?id=<?= (int)$ord['order_id'] ?>" class="btn btn-sm btn-outline-dark">
                                    Détails &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
