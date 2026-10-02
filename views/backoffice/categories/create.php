<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0 text-dark">Ajouter une nouvelle Catégorie</h1>
            <a href="<?= BASE_URL ?>/admin/categories" class="btn btn-sm btn-outline-secondary">
                &larr; Retour aux catégories
            </a>
        </div>

        <div class="card admin-card p-4">
            <form method="POST" action="<?= BASE_URL ?>/admin/categories/create" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                <div class="mb-3">
                    <label for="nom" class="form-label fw-semibold">
                        Nom de la catégorie <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           class="form-control"
                           id="nom"
                           name="nom"
                           placeholder="Ex: Zouza Traditionnelle"
                           maxlength="100"
                           required
                           autofocus>
                    <div class="form-text">Ce nom apparaîtra sur le menu et les filtres du catalogue.</div>
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea class="form-control"
                              id="description"
                              name="description"
                              rows="4"
                              maxlength="1000"
                              placeholder="Brève présentation gourmande de cette catégorie..."></textarea>
                    <div class="form-text">Facultatif (1000 caractères max).</div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>/admin/categories" class="btn btn-outline-secondary">Annuler</a>
                    <button type="submit" class="btn btn-zouza px-4">Créer la catégorie</button>
                </div>
            </form>
        </div>
    </div>
</div>
