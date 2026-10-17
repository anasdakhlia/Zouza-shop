<div class="mb-5 text-center">
    <h1 class="display-5 fw-bold text-dark mb-2">Nos Zouzas & Douceurs</h1>
    <p class="lead text-muted mx-auto" style="max-width: 600px;">
        Découvrez notre collection de pâtisseries tunisiennes traditionnelles et créations gourmandes, confectionnées avec passion.
    </p>

    <!-- Formulaire de recherche textuelle -->
    <div class="row justify-content-center mt-4">
        <div class="col-md-8 col-lg-6">
            <form method="GET" action="<?= BASE_URL ?>/products" class="input-group shadow-sm rounded-pill overflow-hidden border">
                <?php if (!empty($selectedCategory)): ?>
                    <input type="hidden" name="category" value="<?= e($selectedCategory['slug']) ?>">
                <?php endif; ?>
                <?php if ($availability !== 'available' && $availability !== '1'): ?>
                    <input type="hidden" name="availability" value="<?= e($availability) ?>">
                <?php endif; ?>
                <?php if ($sort !== 'newest'): ?>
                    <input type="hidden" name="sort" value="<?= e($sort) ?>">
                <?php endif; ?>

                <input type="search"
                       name="q"
                       class="form-control border-0 ps-4 py-2"
                       placeholder="Rechercher une Zouza (ex: caramel, amande, pistache...)"
                       value="<?= e($search) ?>"
                       aria-label="Recherche de délices">
                <button class="btn btn-zouza px-4" type="submit" title="Lancer la recherche">
                    &#128269; Rechercher
                </button>
            </form>
        </div>
    </div>

    <!-- Filtres par catégorie -->
    <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
        <?php
        $allParams = $queryParams;
        unset($allParams['category'], $allParams['page']);
        $allUrl = BASE_URL . '/products' . (!empty($allParams) ? '?' . http_build_query($allParams) : '');
        ?>
        <a href="<?= $allUrl ?>"
           class="btn rounded-pill px-4 <?= empty($selectedCategory) ? 'btn-zouza' : 'btn-outline-secondary' ?>">
            Tous nos délices
        </a>

        <?php foreach ($categories as $cat): ?>
            <?php
            $catParams = $queryParams;
            $catParams['category'] = $cat['slug'];
            unset($catParams['page']);
            $catUrl = BASE_URL . '/products?' . http_build_query($catParams);
            $isActive = !empty($selectedCategory) && (int)$selectedCategory['category_id'] === (int)$cat['category_id'];
            ?>
            <a href="<?= $catUrl ?>"
               class="btn rounded-pill px-4 <?= $isActive ? 'btn-zouza' : 'btn-outline-secondary' ?>">
                <?= e($cat['nom']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Bandeau d'informations catégorie si sélectionnée -->
<?php if (!empty($selectedCategory) && !empty($selectedCategory['description'])): ?>
    <div class="alert alert-light border text-center mb-4 py-3 rounded-4 shadow-sm">
        <p class="mb-0 text-muted fst-italic"><?= e($selectedCategory['description']) ?></p>
    </div>
<?php endif; ?>

<!-- Barre d'outils des résultats : Compteur, Disponibilité, Tri et Réinitialisation -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
    <form method="GET" action="<?= BASE_URL ?>/products" id="filterForm">
        <?php if ($search !== ''): ?>
            <input type="hidden" name="q" value="<?= e($search) ?>">
        <?php endif; ?>
        <?php if (!empty($selectedCategory)): ?>
            <input type="hidden" name="category" value="<?= e($selectedCategory['slug']) ?>">
        <?php endif; ?>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <!-- Compteur de résultats -->
            <div class="d-flex align-items-center gap-2">
                <span class="fs-5 fw-bold text-dark"><?= (int)$totalItems ?></span>
                <span class="text-muted small"><?= (int)$totalItems > 1 ? 'délices trouvés' : 'délice trouvé' ?></span>

                <?php if ($search !== '' || !empty($selectedCategory) || ($availability !== 'available' && $availability !== '1') || $sort !== 'newest'): ?>
                    <a href="<?= BASE_URL ?>/products" class="btn btn-sm btn-link text-danger text-decoration-none ms-2 py-0">
                        &times; Réinitialiser les filtres
                    </a>
                <?php endif; ?>
            </div>

            <!-- Filtres sélectifs : Disponibilité et Tri -->
            <div class="d-flex flex-wrap align-items-center gap-3 ms-auto">
                <!-- Filtre Disponibilité -->
                <div class="d-flex align-items-center gap-2">
                    <label for="availabilitySelect" class="form-label text-muted small mb-0 d-none d-sm-inline">Disponibilité :</label>
                    <select name="availability" id="availabilitySelect" class="form-select form-select-sm rounded-3" style="width: auto;" onchange="this.form.submit()">
                        <option value="available" <?= ($availability === 'available' || $availability === '1') ? 'selected' : '' ?>>En stock uniquement</option>
                        <option value="all" <?= ($availability === 'all') ? 'selected' : '' ?>>Tous les produits</option>
                        <option value="0" <?= ($availability === '0' || $availability === 'out_of_stock') ? 'selected' : '' ?>>En rupture de stock</option>
                    </select>
                </div>

                <!-- Filtre de Tri -->
                <div class="d-flex align-items-center gap-2">
                    <label for="sortSelect" class="form-label text-muted small mb-0 d-none d-sm-inline">Trier par :</label>
                    <select name="sort" id="sortSelect" class="form-select form-select-sm rounded-3" style="width: auto;" onchange="this.form.submit()">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Nouveautés</option>
                        <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Prix : croissant &uarr;</option>
                        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Prix : décroissant &darr;</option>
                        <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Nom : A &rarr; Z</option>
                    </select>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Liste des Produits -->
<?php if (empty($products)): ?>
    <div class="card border-0 shadow-sm p-5 text-center my-4 rounded-4 bg-white">
        <span class="fs-1 text-muted mb-3">&#128269;</span>
        <h3 class="h5 text-dark mb-2">Aucun délice ne correspond à votre recherche.</h3>
        <p class="text-muted small mb-4">
            Essayez de modifier vos termes de recherche ou de retirer certains filtres.
        </p>
        <div>
            <a href="<?= BASE_URL ?>/products" class="btn btn-zouza-sm px-4 py-2">
                Voir tout le catalogue de Zouzas
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
        <?php foreach ($products as $prod): ?>
            <?php $isAvailable = (int)$prod['disponible'] === 1; ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden d-flex flex-column <?= !$isAvailable ? 'opacity-75' : '' ?>" style="transition: transform 0.2s, box-shadow 0.2s;">
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

                        <!-- Badge Catégorie -->
                        <span class="position-absolute top-0 start-0 m-3 badge bg-white text-dark shadow-sm rounded-pill px-3 py-2 border">
                            <?= e($prod['category_name']) ?>
                        </span>

                        <!-- Badge Disponibilité si rupture -->
                        <?php if (!$isAvailable): ?>
                            <span class="position-absolute top-0 end-0 m-3 badge bg-secondary text-white shadow-sm rounded-pill px-3 py-2">
                                Épuisé
                            </span>
                        <?php endif; ?>
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
                                <?php if ($isAvailable): ?>
                                    <form method="POST" action="<?= BASE_URL ?>/cart/add" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                        <input type="hidden" name="product_id" value="<?= (int)$prod['product_id'] ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn-zouza-sm border-0" title="Ajouter au panier">
                                            &#128722; Ajouter
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" disabled title="Temporairement indisponible">
                                        Épuisé
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination côté serveur avec conservation des filtres -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Pagination du catalogue" class="d-flex flex-column align-items-center gap-2 mb-5">
            <ul class="pagination pagination-md shadow-sm rounded-pill overflow-hidden mb-0">
                <!-- Bouton Précédent -->
                <?php
                $prevParams = $queryParams;
                $prevParams['page'] = $currentPage - 1;
                $prevUrl = BASE_URL . '/products?' . http_build_query($prevParams);
                ?>
                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link text-dark" href="<?= $currentPage > 1 ? $prevUrl : '#' ?>" tabindex="<?= $currentPage <= 1 ? '-1' : '0' ?>" aria-label="Précédent">
                        &laquo; Précédent
                    </a>
                </li>

                <!-- Numéros de pages -->
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <?php
                    $pageParams = $queryParams;
                    $pageParams['page'] = $p;
                    $pageUrl = BASE_URL . '/products?' . http_build_query($pageParams);
                    $isActive = ($p === $currentPage);
                    ?>
                    <li class="page-item <?= $isActive ? 'active' : '' ?>">
                        <?php if ($isActive): ?>
                            <span class="page-link bg-zouza text-white border-0 fw-bold" style="background-color: #b86b35;">
                                <?= $p ?>
                            </span>
                        <?php else: ?>
                            <a class="page-link text-dark" href="<?= $pageUrl ?>">
                                <?= $p ?>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>

                <!-- Bouton Suivant -->
                <?php
                $nextParams = $queryParams;
                $nextParams['page'] = $currentPage + 1;
                $nextUrl = BASE_URL . '/products?' . http_build_query($nextParams);
                ?>
                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link text-dark" href="<?= $currentPage < $totalPages ? $nextUrl : '#' ?>" tabindex="<?= $currentPage >= $totalPages ? '-1' : '0' ?>" aria-label="Suivant">
                        Suivant &raquo;
                    </a>
                </li>
            </ul>

            <small class="text-muted">
                Page <?= (int)$currentPage ?> sur <?= (int)$totalPages ?> &bull; Affichage de <?= count($products) ?> sur <?= (int)$totalItems ?> articles
            </small>
        </nav>
    <?php endif; ?>
<?php endif; ?>
