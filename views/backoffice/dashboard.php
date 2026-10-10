<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card admin-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h4 mb-0 text-dark">Tableau de Bord Administrateur</h1>
                <span class="badge bg-success px-3 py-2">Session Administrateur Active</span>
            </div>

            <p class="text-muted">
                Bienvenue dans l'espace de gestion de <strong><?= APP_NAME ?></strong>, <?= e($currentUser['prenom'] . ' ' . $currentUser['nom']) ?>.
            </p>

            <div class="row g-4 my-2">
                <!-- Raccourci Produits -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-light">
                        <div class="d-flex align-items-center mb-3">
                            <span class="fs-1 me-3">&#129367;</span>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Catalogue Produits</h5>
                                <small class="text-muted">Gérer les recettes</small>
                            </div>
                        </div>
                        <p class="small text-muted mb-4">
                            Gérez les recettes Zouza, fixez les prix en dinars, photos et disponibilité.
                        </p>
                        <div class="mt-auto d-flex gap-2">
                            <a href="<?= BASE_URL ?>/admin/products" class="btn btn-outline-dark btn-sm">
                                Gérer &rarr;
                            </a>
                            <a href="<?= BASE_URL ?>/admin/products/create" class="btn btn-zouza btn-sm">
                                + Produit
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Raccourci Catégories -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-light">
                        <div class="d-flex align-items-center mb-3">
                            <span class="fs-1 me-3">&#128193;</span>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Familles & Catégories</h5>
                                <small class="text-muted">Organiser les gammes</small>
                            </div>
                        </div>
                        <p class="small text-muted mb-4">
                            Créez les catégories (Traditionnelle, Gourmande...), slugs automatiques.
                        </p>
                        <div class="mt-auto d-flex gap-2">
                            <a href="<?= BASE_URL ?>/admin/categories" class="btn btn-outline-dark btn-sm">
                                Gérer &rarr;
                            </a>
                            <a href="<?= BASE_URL ?>/admin/categories/create" class="btn btn-zouza btn-sm">
                                + Catégorie
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Raccourci Commandes -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-light">
                        <div class="d-flex align-items-center mb-3">
                            <span class="fs-1 me-3">&#128220;</span>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Commandes Clients</h5>
                                <small class="text-muted">Suivi & Traitement</small>
                            </div>
                        </div>
                        <p class="small text-muted mb-4">
                            Consultez les commandes, coordonnez la livraison et mettez à jour les statuts.
                        </p>
                        <div class="mt-auto">
                            <a href="<?= BASE_URL ?>/admin/orders" class="btn btn-zouza btn-sm w-100">
                                Gérer les commandes &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations utilisateur -->
        <div class="card admin-card p-4">
            <h6 class="text-uppercase text-muted fw-bold small mb-3">Informations de Session</h6>
            <div class="row g-3 small">
                <div class="col-sm-4">
                    <span class="text-muted d-block">Identifiant Admin :</span>
                    <strong>#<?= (int)$currentUser['user_id'] ?></strong>
                </div>
                <div class="col-sm-4">
                    <span class="text-muted d-block">Email de connexion :</span>
                    <strong><?= e($currentUser['email']) ?></strong>
                </div>
                <div class="col-sm-4">
                    <span class="text-muted d-block">Rôle vérifié :</span>
                    <span class="badge bg-success"><?= e($currentUser['role']) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
