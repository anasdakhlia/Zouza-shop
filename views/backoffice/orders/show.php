<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin" class="text-decoration-none">Tableau de bord</a></li>
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/orders" class="text-decoration-none">Commandes</a></li>
        <li class="breadcrumb-item active" aria-current="page">Commande #<?= e($order['reference']) ?></li>
    </ol>
</nav>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Commande #<?= e($order['reference']) ?></h1>
        <p class="text-muted small mb-0">
            Enregistrée le <strong><?= date('d/m/Y à H:i', strtotime($order['date_commande'])) ?></strong>
        </p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/admin/orders" class="btn btn-outline-secondary btn-sm">
            &larr; Retour à la liste
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Colonne Principale (Gauche) : Articles et Détails de livraison -->
    <div class="col-lg-8">
        <!-- Carte Articles Commandés -->
        <div class="card admin-card p-4 mb-4">
            <h2 class="h5 fw-bold text-dark mb-3 border-bottom pb-2">Articles commandés</h2>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Produit</th>
                            <th class="text-center">Prix unitaire (figé)</th>
                            <th class="text-center">Quantité</th>
                            <th class="text-end pe-3">Sous-total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if (!empty($item['product_image'])): ?>
                                            <img src="<?= BASE_URL ?>/uploads/products/<?= e($item['product_image']) ?>"
                                                 alt="<?= e($item['product_nom']) ?>"
                                                 class="rounded-3 border"
                                                 style="width: 48px; height: 48px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center text-muted"
                                                 style="width: 48px; height: 48px; font-size: 1.3rem;">
                                                &#129367;
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-semibold text-dark small mb-0">
                                                <?= e($item['product_nom'] ?? 'Produit inconnu') ?>
                                            </div>
                                            <span class="badge bg-light text-muted border small">
                                                <?= e($item['category_name'] ?? 'Catégorie') ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center fw-medium">
                                    <?= formatPrice($item['prix_unitaire']) ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill bg-light text-dark border px-3 py-2">
                                        &times; <?= (int)$item['quantite'] ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3 fw-bold" style="color: #b86b35;">
                                    <?= formatPrice($item['sous_total']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="border-top">
                        <tr>
                            <td colspan="3" class="text-end fw-bold py-3">Montant Total de la commande :</td>
                            <td class="text-end pe-3 fs-5 fw-bold py-3" style="color: #b86b35;">
                                <?= formatPrice($order['total']) ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Carte Informations de Réception / Livraison -->
        <div class="card admin-card p-4">
            <h2 class="h5 fw-bold text-dark mb-3 border-bottom pb-2">Informations de livraison / retrait</h2>

            <div class="row g-3">
                <div class="col-sm-6">
                    <span class="text-muted small d-block">Mode de réception :</span>
                    <strong class="text-dark">
                        <?= $order['type_livraison'] === 'a_emporter' ? '&#127978; Retrait en atelier' : '&#128666; Livraison à domicile' ?>
                    </strong>
                </div>

                <?php if ($order['type_livraison'] === 'livraison'): ?>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Ville :</span>
                        <strong class="text-dark"><?= e($order['ville'] ?? 'Non précisée') ?></strong>
                    </div>
                    <div class="col-12">
                        <span class="text-muted small d-block">Adresse complète de livraison :</span>
                        <div class="p-3 bg-light rounded-3 border mt-1">
                            <?= nl2br(e($order['adresse'] ?? 'Non précisée')) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($order['notes'])): ?>
                    <div class="col-12">
                        <span class="text-muted small d-block">Instructions spéciales du client :</span>
                        <div class="p-3 bg-light rounded-3 border fst-italic text-secondary mt-1">
                            <?= nl2br(e($order['notes'])) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Colonne Latérale (Droite) : Gestion du Statut et Infos Client -->
    <div class="col-lg-4">
        <!-- Formulaire de Modification du Statut -->
        <div class="card admin-card p-4 mb-4 border-start border-4 border-primary">
            <h2 class="h5 fw-bold text-dark mb-3">Statut de la commande</h2>

            <div class="mb-3">
                <span class="text-muted small d-block mb-1">Statut actuel :</span>
                <?php
                    $statusBadge = match($order['statut']) {
                        'pending'   => '<span class="badge bg-warning text-dark px-3 py-2 fs-6">En attente</span>',
                        'confirmed' => '<span class="badge bg-info text-dark px-3 py-2 fs-6">Confirmée</span>',
                        'preparing' => '<span class="badge bg-primary px-3 py-2 fs-6">En préparation</span>',
                        'ready'     => '<span class="badge text-white px-3 py-2 fs-6" style="background-color: #20c997;">Prête</span>',
                        'delivered' => '<span class="badge bg-success px-3 py-2 fs-6">Livrée</span>',
                        'cancelled' => '<span class="badge bg-danger px-3 py-2 fs-6">Annulée</span>',
                        default     => '<span class="badge bg-secondary px-3 py-2 fs-6">' . e($order['statut']) . '</span>',
                    };
                    echo $statusBadge;
                ?>
            </div>

            <form method="POST" action="<?= BASE_URL ?>/admin/orders/status">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                <input type="hidden" name="order_id" value="<?= (int)$order['order_id'] ?>">

                <div class="mb-3">
                    <label for="statut" class="form-label small fw-semibold">Modifier le statut :</label>
                    <select id="statut" name="statut" class="form-select form-select-sm" required>
                        <?php foreach ($allowedStatuses as $st): ?>
                            <option value="<?= e($st) ?>" <?= $order['statut'] === $st ? 'selected' : '' ?>>
                                <?= e($statusLabels[$st] ?? $st) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-zouza btn-sm w-100 py-2 fw-semibold">
                    &#10003; Mettre à jour le statut
                </button>
            </form>
        </div>

        <!-- Coordonnées Client -->
        <div class="card admin-card p-4">
            <h2 class="h5 fw-bold text-dark mb-3 border-bottom pb-2">Client</h2>

            <div class="mb-3">
                <span class="text-muted small d-block">Nom et prénom :</span>
                <strong class="text-dark fs-6"><?= e($order['prenom_client'] . ' ' . $order['nom_client']) ?></strong>
            </div>

            <div class="mb-3">
                <span class="text-muted small d-block">Téléphone :</span>
                <a href="tel:<?= e($order['telephone']) ?>" class="fw-semibold text-decoration-none" style="color: #b86b35;">
                    &#128222; <?= e($order['telephone']) ?>
                </a>
            </div>

            <div class="mb-3">
                <span class="text-muted small d-block">Email :</span>
                <a href="mailto:<?= e($order['email']) ?>" class="text-muted small text-decoration-none">
                    &#9993; <?= e($order['email']) ?>
                </a>
            </div>

            <div class="border-top pt-3">
                <span class="text-muted small d-block">Compte utilisateur :</span>
                <?php if (!empty($order['user_id'])): ?>
                    <span class="badge bg-success">Client inscrit (ID #<?= (int)$order['user_id'] ?>)</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Commande invité (Sans compte)</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
