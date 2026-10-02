<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0 text-dark">Modifier la Catégorie #<?= (int)$category['category_id'] ?></h1>
            <a href="<?= BASE_URL ?>/admin/categories" class="btn btn-sm btn-outline-secondary">
                &larr; Retour aux catégories
            </a>
        </div>

        <div class="card admin-card p-4">
            <form method="POST" action="<?= BASE_URL ?>/admin/categories/edit" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                <input type="hidden" name="id" value="<?= (int)$category['category_id'] ?>">

                <div class="mb-3">
                    <label for="nom" class="form-label fw-semibold">
                        Nom de la catégorie <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           class="form-control"
                           id="nom"
                           name="nom"
                           value="<?= e($category['nom']) ?>"
                           maxlength="100"
                           required
                           autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted">Slug actuel</label>
                    <input type="text" class="form-control bg-light" value="<?= e($category['slug']) ?>" readonly>
                    <div class="form-text">Le slug sera automatiquement mis à jour et garanti unique si vous changez le nom.</div>
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea class="form-control"
                              id="description"
                              name="description"
                              rows="4"
                              maxlength="1000"><?= e($category['description'] ?? '') ?></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>/admin/categories" class="btn btn-outline-secondary">Annuler</a>
                    <button type="submit" class="btn btn-zouza px-4">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>
</div>
