<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/" class="text-decoration-none" style="color: #b86b35;">Accueil</a></li>
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/products" class="text-decoration-none" style="color: #b86b35;">Nos Zouzas</a></li>
        <li class="breadcrumb-item active" aria-current="page">Mon Panier</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Mon Panier Gourmand</h1>
        <p class="text-muted small mb-0">Vérifiez vos délices sélectionnés avant de passer commande</p>
    </div>
    <?php if (!empty($items)): ?>
        <form method="POST" action="<?= BASE_URL ?>/cart/clear" onsubmit="return confirm('Êtes-vous sûr de vouloir vider tout votre panier ?');">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <button type="submit" class="btn btn-sm btn-outline-danger">
                &#128465; Vider le panier
            </button>
        </form>
    <?php endif; ?>
</div>

<?php if (empty($items)): ?>
    <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4 bg-white">
        <div class="py-4">
            <div class="display-3 mb-3 text-muted">&#128722;</div>
            <h2 class="h4 text-dark mb-2">Votre panier est actuellement vide</h2>
            <p class="text-muted mb-4">Vous n'avez pas encore sélectionné de délicieuses Zouzas.</p>
            <a href="<?= BASE_URL ?>/products" class="btn-zouza-sm px-4 py-2 fs-6">
                Découvrir nos créations &rarr;
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <!-- Tableau des articles -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="min-width: 220px;">Délice</th>
                                <th class="text-center" style="width: 130px;">Prix unit.</th>
                                <th class="text-center" style="width: 170px;">Quantité</th>
                                <th class="text-end" style="width: 130px;">Sous-total</th>
                                <th class="text-center pe-4" style="width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <?php $prod = $item['product']; ?>
                                <tr>
                                    <!-- Photo + Nom -->
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <?php if (!empty($prod['image'])): ?>
                                                <img src="<?= BASE_URL ?>/uploads/products/<?= e($prod['image']) ?>"
                                                     alt="<?= e($prod['nom']) ?>"
                                                     class="rounded-3 border"
                                                     style="width: 54px; height: 54px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center text-muted"
                                                     style="width: 54px; height: 54px; font-size: 1.5rem;">
                                                    &#129367;
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <a href="<?= BASE_URL ?>/products/detail?id=<?= (int)$prod['product_id'] ?>" class="fw-semibold text-dark text-decoration-none d-block">
                                                    <?= e($prod['nom']) ?>
                                                </a>
                                                <span class="badge bg-light text-muted border small">
                                                    <?= e($prod['category_name']) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Prix unitaire -->
                                    <td class="text-center fw-medium">
                                        <?= formatPrice($prod['prix']) ?>
                                    </td>

                                    <!-- Formulaire de modification de quantité -->
                                    <td class="text-center">
                                        <form method="POST" action="<?= BASE_URL ?>/cart/update" class="d-inline-flex align-items-center justify-content-center gap-1">
                                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                            <input type="hidden" name="product_id" value="<?= (int)$prod['product_id'] ?>">
                                            <input type="number"
                                                   name="quantity"
                                                   value="<?= (int)$item['quantity'] ?>"
                                                   min="1"
                                                   max="99"
                                                   class="form-control form-control-sm text-center"
                                                   style="width: 65px;"
                                                   required>
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Mettre à jour la quantité">
                                                &#10003;
                                            </button>
                                        </form>
                                    </td>

                                    <!-- Sous-total -->
                                    <td class="text-end fw-bold" style="color: #b86b35;">
                                        <?= formatPrice($item['subtotal']) ?>
                                    </td>

                                    <!-- Bouton de suppression -->
                                    <td class="text-center pe-4">
                                        <form method="POST" action="<?= BASE_URL ?>/cart/remove" onsubmit="return confirm('Retirer ce produit du panier ?');" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                            <input type="hidden" name="product_id" value="<?= (int)$prod['product_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer cet article">
                                                &times;
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-3 bg-light border-top d-flex justify-content-between align-items-center">
                    <a href="<?= BASE_URL ?>/products" class="btn btn-sm btn-outline-secondary">
                        &larr; Continuer mes achats
                    </a>
                    <span class="text-muted small">
                        <?= count($items) ?> variété(s) de douceurs sélectionnée(s)
                    </span>
                </div>
            </div>
        </div>

        <!-- Récapitulatif de commande -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h4 class="h5 fw-bold text-dark mb-3">Récapitulatif</h4>

                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Sous-total articles :</span>
                    <span class="fw-semibold"><?= formatPrice($total) ?></span>
                </div>

                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Estimation livraison :</span>
                    <span class="text-success small fw-medium">Calculée à la commande</span>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-3 mb-4">
                    <span class="fs-5 fw-bold text-dark">Total :</span>
                    <span class="fs-4 fw-bold" style="color: #b86b35;">
                        <?= formatPrice($total) ?>
                    </span>
                </div>

                <a href="<?= BASE_URL ?>/checkout" class="btn text-white w-100 py-3 fw-bold mb-2 shadow-sm rounded-3" style="background-color: #b86b35;">
                    Passer la commande &rarr;
                </a>

                <a href="<?= BASE_URL ?>/products" class="btn btn-outline-secondary w-100 py-2">
                    Ajouter d'autres pâtisseries
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>
