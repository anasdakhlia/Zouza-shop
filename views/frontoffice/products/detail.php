<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/" class="text-decoration-none" style="color: #b86b35;">Accueil</a></li>
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/products" class="text-decoration-none" style="color: #b86b35;">Nos Zouzas</a></li>
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/products?category=<?= urlencode($product['category_slug']) ?>" class="text-decoration-none" style="color: #b86b35;"><?= e($product['category_name']) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= e($product['nom']) ?></li>
    </ol>
</nav>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden p-4 p-md-5 bg-white">
    <div class="row g-5 align-items-center">
        <!-- Photo du produit -->
        <div class="col-lg-6">
            <div class="rounded-4 overflow-hidden bg-light text-center border" style="max-height: 440px;">
                <?php if (!empty($product['image'])): ?>
                    <img src="<?= BASE_URL ?>/uploads/products/<?= e($product['image']) ?>"
                         alt="<?= e($product['nom']) ?>"
                         class="img-fluid w-100"
                         style="object-fit: cover; max-height: 440px;">
                <?php else: ?>
                    <div class="py-5 d-flex flex-column align-items-center justify-content-center text-muted" style="min-height: 340px;">
                        <span style="font-size: 5rem;">&#129367;</span>
                        <span class="fs-5 mt-2 text-secondary">Photo artisanale à venir</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Informations détaillées -->
        <div class="col-lg-6">
            <span class="badge rounded-pill bg-light text-dark border px-3 py-2 mb-2">
                <?= e($product['category_name']) ?>
            </span>

            <h1 class="display-6 fw-bold text-dark mb-3">
                <?= e($product['nom']) ?>
            </h1>

            <div class="mb-4">
                <span class="display-6 fw-bold" style="color: #b86b35;">
                    <?= formatPrice($product['prix']) ?>
                </span>
                <span class="text-muted ms-2 small">TTC</span>
            </div>

            <div class="mb-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-semibold">Disponibilité :</span>
                    <?php if ((int)$product['disponible'] === 1): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">En stock / Confection sur commande</span>
                    <?php else: ?>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1">Actuellement indisponible</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mb-5">
                <h5 class="fw-semibold text-dark mb-2">Description</h5>
                <p class="text-muted leading-relaxed mb-0">
                    <?= !empty($product['description']) ? nl2br(e($product['description'])) : 'Nos Zouzas sont confectionnées à la main dans le respect des traditions tunisiennes, dorées à point et garnies avec générosité.' ?>
                </p>
            </div>

            <div class="d-flex gap-3">
                <a href="<?= BASE_URL ?>/products" class="btn btn-outline-secondary px-4 py-2">
                    &larr; Retour au catalogue
                </a>
            </div>
        </div>
    </div>
</div>
