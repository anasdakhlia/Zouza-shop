<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h1 class="h4 mb-0 text-dark">Modifier le Produit #<?= (int)$product['product_id'] ?></h1>
            <a href="<?= BASE_URL ?>/admin/products" class="btn btn-sm btn-outline-secondary">
                &larr; Retour aux produits
            </a>
        </div>

        <div class="card admin-card p-4">
            <form method="POST" action="<?= BASE_URL ?>/admin/products/edit" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                <input type="hidden" name="id" value="<?= (int)$product['product_id'] ?>">

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="nom" class="form-label fw-semibold">
                            Nom du produit <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               id="nom"
                               name="nom"
                               value="<?= e($product['nom']) ?>"
                               maxlength="150"
                               required
                               autofocus>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="category_id" class="form-label fw-semibold">
                            Catégorie <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int)$cat['category_id'] ?>" <?= ((int)$cat['category_id'] === (int)$product['category_id']) ? 'selected' : '' ?>>
                                    <?= e($cat['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted">Slug actuel</label>
                    <input type="text" class="form-control bg-light" value="<?= e($product['slug']) ?>" readonly>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="prix" class="form-label fw-semibold">
                            Prix (en <?= defined('APP_CURRENCY_SYMBOL') ? APP_CURRENCY_SYMBOL : 'DT' ?>) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number"
                                   step="0.001"
                                   min="0"
                                   class="form-control"
                                   id="prix"
                                   name="prix"
                                   value="<?= (float)$product['prix'] ?>"
                                   required>
                            <span class="input-group-text"><?= defined('APP_CURRENCY_SYMBOL') ? APP_CURRENCY_SYMBOL : 'DT' ?></span>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="image" class="form-label fw-semibold">Remplacer la photo</label>
                        <input type="file"
                               class="form-control"
                               id="image"
                               name="image"
                               accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">Laisser vide pour conserver la photo existante.</div>
                    </div>
                </div>

                <?php if (!empty($product['image'])): ?>
                    <div class="mb-3 p-3 bg-light rounded border d-flex align-items-center gap-3">
                        <img src="<?= BASE_URL ?>/uploads/products/<?= e($product['image']) ?>"
                             alt="<?= e($product['nom']) ?>"
                             class="rounded border"
                             style="width: 60px; height: 60px; object-fit: cover;">
                        <div class="small">
                            <strong>Photo actuelle :</strong> <code><?= e($product['image']) ?></code>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea class="form-control"
                              id="description"
                              name="description"
                              rows="4"><?= e($product['description'] ?? '') ?></textarea>
                </div>

                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               role="switch"
                               id="disponible"
                               name="disponible"
                               value="1"
                               <?= ((int)$product['disponible'] === 1) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="disponible">
                            Produit disponible à la vente (affiché dans le catalogue)
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>/admin/products" class="btn btn-outline-secondary">Annuler</a>
                    <button type="submit" class="btn btn-zouza px-4">Mettre à jour le produit</button>
                </div>
            </form>
        </div>
    </div>
</div>
