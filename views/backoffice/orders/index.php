<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Gestion des Commandes</h1>
        <p class="text-muted small mb-0">Suivez, traitez et mettez à jour les commandes passées par vos clients.</p>
    </div>
</div>

<!-- Filtres par statut -->
<div class="card admin-card p-3 mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <!-- Onglets / Badges de filtre par statut -->
        <div class="d-flex flex-wrap gap-2">
            <?php
                $allUrl = BASE_URL . '/admin/orders' . (!empty($search) ? '?search=' . urlencode($search) : '');
            ?>
            <a href="<?= $allUrl ?>"
               class="btn btn-sm rounded-pill <?= empty($currentStatus) ? 'btn-dark' : 'btn-outline-secondary' ?>">
                Toutes <span class="badge bg-secondary ms-1"><?= (int)($statusCounts['all'] ?? 0) ?></span>
            </a>

            <?php foreach ($allowedStatuses as $st): ?>
                <?php
                    $isActive = ($currentStatus === $st);
                    $url = BASE_URL . '/admin/orders?status=' . urlencode($st) . (!empty($search) ? '&search=' . urlencode($search) : '');
                    $count = (int)($statusCounts[$st] ?? 0);
                    $label = $statusLabels[$st] ?? $st;
                    $btnClass = match($st) {
                        'pending'   => $isActive ? 'btn-warning text-dark' : 'btn-outline-warning text-dark',
                        'confirmed' => $isActive ? 'btn-info text-dark' : 'btn-outline-info text-dark',
                        'preparing' => $isActive ? 'btn-primary' : 'btn-outline-primary',
                        'ready'     => $isActive ? 'btn-success' : 'btn-outline-success',
                        'delivered' => $isActive ? 'btn-success' : 'btn-outline-secondary',
                        'cancelled' => $isActive ? 'btn-danger' : 'btn-outline-danger',
                        default     => $isActive ? 'btn-dark' : 'btn-outline-secondary',
                    };
                ?>
                <a href="<?= $url ?>" class="btn btn-sm rounded-pill <?= $btnClass ?>">
                    <?= e($label) ?> <span class="badge bg-light text-dark ms-1"><?= $count ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Formulaire de recherche textuelle -->
        <form method="GET" action="<?= BASE_URL ?>/admin/orders" class="d-flex gap-2">
            <?php if (!empty($currentStatus)): ?>
                <input type="hidden" name="status" value="<?= e($currentStatus) ?>">
            <?php endif; ?>
            <div class="input-group input-group-sm" style="min-width: 260px;">
                <input type="text"
                       name="search"
                       class="form-control"
                       placeholder="Réf, nom, email, tél..."
                       value="<?= e($search) ?>">
                <button class="btn btn-outline-secondary" type="submit">Rechercher</button>
            </div>
            <?php if (!empty($search) || !empty($currentStatus)): ?>
                <a href="<?= BASE_URL ?>/admin/orders" class="btn btn-sm btn-outline-danger" title="Réinitialiser les filtres">
                    &times;
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Tableau des commandes -->
<div class="card admin-card overflow-hidden">
    <?php if (empty($orders)): ?>
        <div class="text-center py-5">
            <div class="display-4 text-muted mb-3">&#128220;</div>
            <h5 class="text-dark">Aucune commande trouvée</h5>
            <p class="text-muted small mb-3">
                <?php if (!empty($search) || !empty($currentStatus)): ?>
                    Aucune commande ne correspond aux filtres appliqués.
                <?php else: ?>
                    Aucune commande n'a encore été enregistrée dans la boutique.
                <?php endif; ?>
            </p>
            <?php if (!empty($search) || !empty($currentStatus)): ?>
                <a href="<?= BASE_URL ?>/admin/orders" class="btn btn-sm btn-outline-dark">
                    Effacer les filtres
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-admin table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Référence</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Mode</th>
                        <th class="text-center">Articles</th>
                        <th>Total</th>
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
                                'ready'     => '<span class="badge text-white" style="background-color: #20c997;">Prête</span>',
                                'delivered' => '<span class="badge bg-success">Livrée</span>',
                                'cancelled' => '<span class="badge bg-danger">Annulée</span>',
                                default     => '<span class="badge bg-secondary">' . e($ord['statut']) . '</span>',
                            };

                            $deliveryBadge = ($ord['type_livraison'] === 'a_emporter')
                                ? '<span class="badge bg-light text-dark border">&#127978; Retrait</span>'
                                : '<span class="badge bg-light text-dark border">&#128666; Livraison</span>';
                        ?>
                        <tr>
                            <td class="ps-4">
                                <a href="<?= BASE_URL ?>/admin/orders/detail?id=<?= (int)$ord['order_id'] ?>" class="fw-bold text-dark text-decoration-none">
                                    <?= e($ord['reference']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark small">
                                    <?= e($ord['prenom_client'] . ' ' . $ord['nom_client']) ?>
                                </div>
                                <div class="text-muted small">
                                    <?= e($ord['telephone']) ?> &bull; <?= e($ord['email']) ?>
                                </div>
                            </td>
                            <td>
                                <span class="small text-muted">
                                    <?= date('d/m/Y à H:i', strtotime($ord['date_commande'])) ?>
                                </span>
                            </td>
                            <td>
                                <?= $deliveryBadge ?>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill bg-light text-dark border">
                                    <?= (int)$ord['items_count'] ?> ligne(s)
                                </span>
                            </td>
                            <td>
                                <strong style="color: #b86b35;">
                                    <?= formatPrice($ord['total']) ?>
                                </strong>
                            </td>
                            <td class="text-center">
                                <?= $statusBadge ?>
                            </td>
                            <td class="text-end pe-4">
                                <a href="<?= BASE_URL ?>/admin/orders/detail?id=<?= (int)$ord['order_id'] ?>" class="btn btn-sm btn-outline-dark">
                                    Consulter &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="p-3 bg-light border-top d-flex justify-content-between align-items-center small text-muted">
            <span>Affichage de <strong><?= count($orders) ?></strong> commande(s)</span>
            <span>Tri chronologique décroissant</span>
        </div>
    <?php endif; ?>
</div>
