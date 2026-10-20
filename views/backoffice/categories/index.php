<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="h3 mb-0 text-dark">Gestion des Catégories</h1>
        <p class="text-muted small mb-0">Organisez les familles de pâtisseries Zouza</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/categories/create" class="btn btn-zouza">
        + Nouvelle Catégorie
    </a>
</div>

<div class="card admin-card">
    <div class="card-body p-0">
        <?php if (empty($categories)): ?>
            <div class="text-center py-5 text-muted">
                <p class="mb-3 fs-5">Aucune catégorie trouvée.</p>
                <a href="<?= BASE_URL ?>/admin/categories/create" class="btn btn-sm btn-zouza">Créer la première catégorie</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-admin align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 80px;">ID</th>
                            <th>Nom</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th class="text-center" style="width: 120px;">Produits</th>
                            <th class="text-end pe-4" style="width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-muted">#<?= (int)$cat['category_id'] ?></td>
                                <td class="fw-semibold text-dark"><?= e($cat['nom']) ?></td>
                                <td><code class="text-secondary"><?= e($cat['slug']) ?></code></td>
                                <td class="text-muted small">
                                    <?= !empty($cat['description']) ? e(mb_strimwidth($cat['description'], 0, 60, '...')) : '<em>Aucune description</em>' ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary rounded-pill px-3">
                                        <?= (int)$cat['total_products'] ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        <a href="<?= BASE_URL ?>/admin/categories/edit?id=<?= (int)$cat['category_id'] ?>" class="btn btn-sm btn-outline-primary" title="Modifier">
                                            Modifier
                                        </a>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/categories/delete" onsubmit="return confirm('Confirmez-vous la suppression définitive de cette catégorie ?');" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                            <input type="hidden" name="id" value="<?= (int)$cat['category_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer" <?= ((int)$cat['total_products'] > 0) ? 'title="Produits associés existants"' : '' ?>>
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
