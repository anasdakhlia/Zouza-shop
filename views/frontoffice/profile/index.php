<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/" class="text-decoration-none text-zouza">Accueil</a></li>
        <li class="breadcrumb-item active" aria-current="page">Mon Profil</li>
    </ol>
</nav>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4 card-zouza">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fs-3 fw-bold bg-zouza"
                 style="width: 64px; height: 64px;">
                <?= mb_strtoupper(mb_substr($user['prenom'], 0, 1)) . mb_strtoupper(mb_substr($user['nom'], 0, 1)) ?>
            </div>
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">
                    <?= e($user['prenom'] . ' ' . $user['nom']) ?>
                </h1>
                <p class="text-muted small mb-0">
                    Membre gourmand depuis le <?= date('d/m/Y', strtotime($user['created_at'])) ?> &bull; <?= e($user['email']) ?>
                </p>
            </div>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/orders" class="btn btn-outline-dark btn-sm">
                &#128220; Voir mes commandes (<?= count($orders) ?>)
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Colonne Gauche : Formulaires Profil & Mot de passe -->
    <div class="col-lg-7">
        <!-- Formulaire Informations Personnelles -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4 card-zouza">
            <h2 class="h5 fw-bold text-dark mb-3 border-bottom pb-2">Informations personnelles</h2>

            <form method="POST" action="<?= BASE_URL ?>/profile">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                <div class="row g-3">
                    <div class="col-sm-6">
                        <label for="prenom" class="form-label small fw-semibold">Prénom <span class="text-danger">*</span></label>
                        <input type="text"
                               id="prenom"
                               name="prenom"
                               class="form-control"
                               value="<?= e($user['prenom']) ?>"
                               required
                               maxlength="100">
                    </div>

                    <div class="col-sm-6">
                        <label for="nom" class="form-label small fw-semibold">Nom <span class="text-danger">*</span></label>
                        <input type="text"
                               id="nom"
                               name="nom"
                               class="form-control"
                               value="<?= e($user['nom']) ?>"
                               required
                               maxlength="100">
                    </div>

                    <div class="col-sm-6">
                        <label for="email" class="form-label small fw-semibold">Adresse email <span class="text-danger">*</span></label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-control"
                               value="<?= e($user['email']) ?>"
                               required
                               maxlength="191">
                    </div>

                    <div class="col-sm-6">
                        <label for="telephone" class="form-label small fw-semibold">Numéro de téléphone</label>
                        <input type="tel"
                               id="telephone"
                               name="telephone"
                               class="form-control"
                               value="<?= e($user['telephone'] ?? '') ?>"
                               placeholder="+216 20 000 000"
                               maxlength="30">
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-zouza px-4 fw-semibold">
                        &#10003; Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>

        <!-- Formulaire Changement de Mot de Passe -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white card-zouza">
            <h2 class="h5 fw-bold text-dark mb-3 border-bottom pb-2">Sécurité & Mot de passe</h2>

            <form method="POST" action="<?= BASE_URL ?>/profile/password">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                <div class="mb-3">
                    <label for="current_password" class="form-label small fw-semibold">Mot de passe actuel <span class="text-danger">*</span></label>
                    <input type="password"
                           id="current_password"
                           name="current_password"
                           class="form-control"
                           required>
                </div>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <label for="new_password" class="form-label small fw-semibold">Nouveau mot de passe <span class="text-danger">*</span></label>
                        <input type="password"
                           id="new_password"
                           name="new_password"
                           class="form-control"
                           placeholder="6 caractères minimum"
                           required
                           minlength="6">
                    </div>

                    <div class="col-sm-6">
                        <label for="confirm_password" class="form-label small fw-semibold">Confirmer le mot de passe <span class="text-danger">*</span></label>
                        <input type="password"
                               id="confirm_password"
                               name="confirm_password"
                               class="form-control"
                               placeholder="Répétez le mot de passe"
                               required
                               minlength="6">
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-outline-dark px-4 fw-semibold">
                        &#128274; Mettre à jour mon mot de passe
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Colonne Droite : Adresse connue & Aperçu Commandes Récentes -->
    <div class="col-lg-5">
        <!-- Carte Adresse de livraison connue -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4 card-zouza">
            <h2 class="h5 fw-bold text-dark mb-3 border-bottom pb-2 d-flex align-items-center gap-2">
                <span>&#128205;</span> Adresse de livraison
            </h2>

            <?php if (!empty($latestAddress)): ?>
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <span class="text-muted small d-block">Dernière adresse enregistrée :</span>
                    <strong class="text-dark d-block"><?= e($latestAddress['adresse']) ?></strong>
                    <span class="badge bg-secondary"><?= e($latestAddress['ville']) ?></span>
                </div>
                <p class="small text-muted mb-0">
                    Cette adresse est automatiquement proposée lors de vos commandes et peut être ajustée à chaque validation d'achat.
                </p>
            <?php else: ?>
                <div class="p-3 bg-light rounded-3 border text-center text-muted small">
                    Aucune adresse enregistrée pour le moment.<br>
                    Votre adresse sera mémorisée lors de votre première commande en livraison.
                </div>
            <?php endif; ?>
        </div>

        <!-- Carte Commandes Récentes -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white card-zouza">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h2 class="h5 fw-bold text-dark mb-0">Commandes récentes</h2>
                <a href="<?= BASE_URL ?>/orders" class="small text-decoration-none text-zouza">
                    Tout voir &rarr;
                </a>
            </div>

            <?php if (empty($orders)): ?>
                <div class="text-center py-4 text-muted small">
                    <div class="fs-2 mb-2">&#128722;</div>
                    Vous n'avez pas encore passé de commande.<br>
                    <a href="<?= BASE_URL ?>/products" class="btn btn-sm btn-outline-secondary mt-2">
                        Découvrir nos Zouzas
                    </a>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php
                        $recentOrders = array_slice($orders, 0, 4);
                        foreach ($recentOrders as $ord):
                            $statusBadge = match($ord['statut']) {
                                'pending'   => '<span class="badge bg-warning text-dark">En attente</span>',
                                'confirmed' => '<span class="badge bg-info text-dark">Confirmée</span>',
                                'preparing' => '<span class="badge bg-primary">En préparation</span>',
                                'ready'     => '<span class="badge text-white" style="background-color: #20c997;">Prête</span>',
                                'delivered' => '<span class="badge bg-success">Livrée</span>',
                                'cancelled' => '<span class="badge bg-danger">Annulée</span>',
                                default     => '<span class="badge bg-secondary">' . e($ord['statut']) . '</span>',
                            };
                    ?>
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <a href="<?= BASE_URL ?>/orders/detail?id=<?= (int)$ord['order_id'] ?>" class="fw-bold text-dark text-decoration-none small d-block">
                                    #<?= e($ord['reference']) ?>
                                </a>
                                <span class="text-muted" style="font-size: 0.75rem;">
                                    <?= date('d/m/Y', strtotime($ord['date_commande'])) ?> &bull; <?= formatPrice($ord['total']) ?>
                                </span>
                            </div>
                            <div>
                                <?= $statusBadge ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-3 text-center">
                    <a href="<?= BASE_URL ?>/orders" class="btn btn-sm btn-outline-secondary w-100">
                        Consulter tout l'historique (<?= count($orders) ?> commandes)
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
