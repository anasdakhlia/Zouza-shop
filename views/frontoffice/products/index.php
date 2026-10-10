<div class="mb-5 text-center">
    <h1 class="display-5 fw-bold text-dark mb-2">Nos Zouzas & Douceurs</h1>
    <p class="lead text-muted mx-auto" style="max-width: 600px;">
        Découvrez notre collection de pâtisseries tunisiennes traditionnelles et créations gourmandes, confectionnées avec passion.
    </p>

    <!-- Filtres par catégorie -->
    <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
        <a href="<?= BASE_URL ?>/products"
           class="btn rounded-pill px-4 <?= empty($selectedCategory) ? 'btn-zouza' : 'btn-outline-secondary' ?>">
            Tous nos délices
        </a>
        <?php foreach ($categories as $cat): ?>
            <?php $isActive = !empty($selectedCategory) && (int)$selectedCategory['category_id'] === (int)$cat['category_id']; ?>
            <a href="<?= BASE_URL ?>/products?category=<?= urlencode($cat['slug']) ?>"
               class="btn rounded-pill px-4 <?= $isActive ? 'btn-zouza' : 'btn-outline-secondary' ?>">
                <?= e($cat['nom']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php if (!empty($selectedCategory) && !empty($selectedCategory['description'])): ?>
    <div class="alert alert-light border text-center mb-5 py-3 rounded-4 shadow-sm">
        <p class="mb-0 text-muted fst-italic"><?= e($selectedCategory['description']) ?></p>
    </div>
<?php endif; ?>

<?php if (empty($products)): ?>
    <div class="card border-0 shadow-sm p-5 text-center my-4 rounded-4">
        <h3 class="h5 text-muted mb-3">Aucun produit disponible dans cette catégorie pour le moment.</h3>
        <p class="text-secondary small mb-3">Revenez bientôt ou parcourez nos autres délices !</p>
        <div>
            <a href="<?= BASE_URL ?>/products" class="btn btn-zouza-sm">Voir tout le catalogue</a>
        </div>
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($products as $prod): ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden d-flex flex-column" style="transition: transform 0.2s, box-shadow 0.2s;">
                    <!-- Image du produit -->
                    <div class="position-relative bg-light text-center" style="height: 220px; overflow: hidden;">
                        <?php if (!empty($prod['image'])): ?>
                            <img src="<?= BASE_URL ?>/uploads/products/<?= e($prod['image']) ?>"
                                 alt="<?= e($prod['nom']) ?>"
                                 class="w-100 h-100"
                                 style="object-fit: cover;">
                        <?php else: ?>
                            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-muted">
                                <span class="fs-1">&#129367;</span>
                                <span class="small mt-1">Zouza Artisanale</span>
                            </div>
                        <?php endif; ?>

                        <span class="position-absolute top-0 start-0 m-3 badge bg-white text-dark shadow-sm rounded-pill px-3 py-2 border">
                            <?= e($prod['category_name']) ?>
                        </span>
                    </div>

                    <!-- Contenu de la fiche -->
                    <div class="card-body p-4 d-flex flex-column flex-grow-1">
                        <h2 class="card-title h5 fw-bold text-dark mb-2">
                            <a href="<?= BASE_URL ?>/products/detail?id=<?= (int)$prod['product_id'] ?>" class="text-decoration-none text-dark">
                                <?= e($prod['nom']) ?>
                            </a>
                        </h2>

                        <p class="card-text text-muted small flex-grow-1">
                            <?= !empty($prod['description']) ? e(mb_strimwidth($prod['description'], 0, 110, '...')) : 'Pâtisserie artisanale faite maison avec des ingrédients de premier choix.' ?>
                        </p>

                        <div class="d-flex justify-content-between align-items-center pt-3 mt-3 border-top">
                            <div>
                                <span class="small text-muted d-block">Prix unitaire</span>
                                <span class="fs-5 fw-bold" style="color: #b86b35;">
                                    <?= formatPrice($prod['prix']) ?>
                                </span>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="<?= BASE_URL ?>/products/detail?id=<?= (int)$prod['product_id'] ?>" class="btn btn-sm btn-outline-secondary">
                                    Détails
                                </a>
                                <form method="POST" action="<?= BASE_URL ?>/cart/add" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                    <input type="hidden" name="product_id" value="<?= (int)$prod['product_id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn-zouza-sm border-0" title="Ajouter au panier">
                                        &#128722; Ajouter
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
