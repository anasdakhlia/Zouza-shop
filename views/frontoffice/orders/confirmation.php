<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/" class="text-decoration-none" style="color: #b86b35;">Accueil</a></li>
        <li class="breadcrumb-item active" aria-current="page">Confirmation de commande</li>
    </ol>
</nav>

<div class="card border-0 shadow-sm rounded-4 p-5 bg-white text-center mb-4">
    <div class="mb-3">
        <div class="display-3 text-success">&#10004;</div>
    </div>
    <h1 class="h3 fw-bold text-dark mb-2">Merci pour votre commande !</h1>
    <p class="text-muted mx-auto" style="max-width: 650px;">
        Votre commande a été enregistrée avec succès. Notre atelier artisanal prépare vos Zouzas avec le plus grand soin.
    </p>

    <div class="d-inline-flex align-items-center justify-content-center gap-2 p-3 bg-light rounded-3 border mx-auto mt-2">
        <span class="text-muted">Référence de commande :</span>
        <strong class="fs-5 text-dark"><?= e($order['reference']) ?></strong>
    </div>
</div>

<div class="row g-4">
    <!-- Détails de la commande et du client -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
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
                    <span class="text-muted small d-block">Email de confirmation :</span>
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

        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h2 class="h5 fw-bold text-dark mb-3 border-bottom pb-2">Prochaines étapes</h2>
            <ol class="text-muted mb-0 ps-3">
                <li class="mb-2"><strong>Confirmation téléphonique :</strong> Notre équipe vous contactera brièvement au <strong><?= e($order['telephone']) ?></strong> pour valider l'heure de livraison ou de retrait.</li>
                <li class="mb-2"><strong>Préparation artisanale :</strong> Vos délices sont confectionnés à la commande pour une fraîcheur optimale.</li>
                <li><strong>Règlement :</strong> Le paiement de <strong><?= formatPrice($order['total']) ?></strong> sera effectué en espèces à la réception.</li>
            </ol>
        </div>
    </div>

    <!-- Récapitulatif des articles commandés -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <h2 class="h5 fw-bold text-dark mb-0">Articles commandés</h2>
                <span class="badge bg-warning text-dark">
                    <?= e($order['statut']) === 'pending' ? 'En attente' : e($order['statut']) ?>
                </span>
            </div>

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
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Date :</span>
                    <span class="small text-dark"><?= date('d/m/Y à H:i', strtotime($order['date_commande'])) ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <span class="fs-5 fw-bold text-dark">Total payé/dû :</span>
                    <span class="fs-4 fw-bold" style="color: #b86b35;">
                        <?= formatPrice($order['total']) ?>
                    </span>
                </div>
            </div>

            <div class="d-grid gap-2 mt-4">
                <a href="<?= BASE_URL ?>/products" class="btn btn-outline-secondary py-2">
                    &larr; Découvrir d'autres délices
                </a>
                <?php if (Session::isLoggedIn()): ?>
                    <a href="<?= BASE_URL ?>/orders" class="btn text-white py-2" style="background-color: #b86b35;">
                        Consulter mes commandes
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
