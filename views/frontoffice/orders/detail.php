<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/" class="text-decoration-none text-zouza">Accueil</a></li>
        <?php if (Session::isLoggedIn()): ?>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/orders" class="text-decoration-none text-zouza">Mes Commandes</a></li>
        <?php endif; ?>
        <li class="breadcrumb-item active" aria-current="page">Commande #<?= e($order['reference']) ?></li>
    </ol>
</nav>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Commande #<?= e($order['reference']) ?></h1>
        <p class="text-muted small mb-0">
            Passée le <?= date('d/m/Y à H:i', strtotime($order['date_commande'])) ?>
        </p>
    </div>
    <div>
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
</div>

<div class="row g-4">
    <!-- Détails livraison -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4 card-zouza">
            <h2 class="h5 fw-bold text-dark mb-3 border-bottom pb-2">Informations de livraison</h2>

            <div class="row g-3">
                <div class="col-sm-6">
                    <span class="text-muted small d-block">Destinataire :</span>
                    <strong class="text-dark"><?= e($order['prenom_client'] . ' ' . $order['nom_client']) ?></strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted small d-block">Téléphone :</span>
                    <strong class="text-dark"><?= e($order['telephone']) ?></strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted small d-block">Email :</span>
                    <span class="text-dark"><?= e($order['email']) ?></span>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted small d-block">Mode de réception :</span>
                    <span class="badge bg-secondary">
                        <?= $order['type_livraison'] === 'a_emporter' ? '&#127978; Retrait en boutique' : '&#128666; Livraison à domicile' ?>
                    </span>
                </div>

                <?php if ($order['type_livraison'] === 'livraison'): ?>
                    <div class="col-12">
                        <span class="text-muted small d-block">Adresse de livraison :</span>
                        <div class="p-3 bg-light rounded-3 border">
                            <?= e($order['adresse']) ?><br>
                            <strong><?= e($order['ville']) ?></strong>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($order['notes'])): ?>
                    <div class="col-12">
                        <span class="text-muted small d-block">Instructions particulières :</span>
                        <div class="p-3 bg-light rounded-3 border fst-italic text-secondary">
                            <?= nl2br(e($order['notes'])) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Articles commandés -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white card-zouza">
            <h2 class="h5 fw-bold text-dark mb-3 border-bottom pb-2">Articles commandés</h2>

            <div class="list-group list-group-flush mb-3">
                <?php foreach ($order['items'] as $item): ?>
                    <div class="list-group-item px-0 py-3 d-flex align-items-center gap-3">
                        <?php if (!empty($item['product_image'])): ?>
                            <img src="<?= BASE_URL ?>/uploads/products/<?= e($item['product_image']) ?>"
                                 alt="<?= e($item['product_nom']) ?>"
                                 class="rounded-3 border"
                                 style="width: 50px; height: 50px; object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center text-muted"
                                 style="width: 50px; height: 50px; font-size: 1.3rem;">
                                &#129367;
                            </div>
                        <?php endif; ?>

                        <div class="flex-grow-1">
                            <div class="fw-semibold text-dark small mb-0"><?= e($item['product_nom']) ?></div>
                            <span class="text-muted small">
                                <?= (int)$item['quantite'] ?> &times; <?= formatPrice($item['prix_unitaire']) ?>
                            </span>
                        </div>

                        <div class="text-end fw-bold" style="color: #b86b35;">
                            <?= formatPrice($item['sous_total']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="border-top pt-3">
                <div class="d-flex justify-content-between align-items-center pt-2">
                    <span class="fs-5 fw-bold text-dark">Montant total :</span>
                    <span class="fs-4 fw-bold" style="color: #b86b35;">
                        <?= formatPrice($order['total']) ?>
                    </span>
                </div>
            </div>

            <div class="mt-4">
                <?php if (Session::isLoggedIn()): ?>
                    <a href="<?= BASE_URL ?>/orders" class="btn btn-outline-secondary w-100 py-2">
                        &larr; Retour à mes commandes
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/products" class="btn btn-outline-secondary w-100 py-2">
                        &larr; Continuer mes achats
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
