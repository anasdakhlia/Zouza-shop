<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0 text-dark">Ajouter un nouveau Produit</h1>
            <a href="<?= BASE_URL ?>/admin/products" class="btn btn-sm btn-outline-secondary">
                &larr; Retour aux produits
            </a>
        </div>

        <div class="card admin-card p-4">
            <form method="POST" action="<?= BASE_URL ?>/admin/products/create" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="nom" class="form-label fw-semibold">
                            Nom du produit <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               id="nom"
                               name="nom"
                               placeholder="Ex: Zouza Classique Caramel (500g)"
                               maxlength="150"
                               required
                               autofocus>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="category_id" class="form-label fw-semibold">
                            Catégorie <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">-- Choisir une catégorie --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int)$cat['category_id'] ?>">
                                    <?= e($cat['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
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
                                   placeholder="Ex: 18.500"
                                   required>
                            <span class="input-group-text"><?= defined('APP_CURRENCY_SYMBOL') ? APP_CURRENCY_SYMBOL : 'DT' ?></span>
                        </div>
                        <div class="form-text">Dinar tunisien avec 3 chiffres après la virgule.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="image" class="form-label fw-semibold">Photo du produit</label>
                        <input type="file"
                               class="form-control"
                               id="image"
                               name="image"
                               accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">Formats acceptés : JPG, PNG, WEBP (Max 2 Mo).</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">Description détaillée</label>
                    <textarea class="form-control"
                              id="description"
                              name="description"
                              rows="4"
                              placeholder="Composition gourmande, poids, ingrédients..."></textarea>
                </div>

                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               role="switch"
                               id="disponible"
                               name="disponible"
                               value="1"
                               checked>
                        <label class="form-check-label fw-semibold" for="disponible">
                            Produit disponible à la vente (affiché dans le catalogue)
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>/admin/products" class="btn btn-outline-secondary">Annuler</a>
                    <button type="submit" class="btn btn-zouza px-4">Enregistrer le produit</button>
                </div>
            </form>
        </div>
    </div>
</div>
