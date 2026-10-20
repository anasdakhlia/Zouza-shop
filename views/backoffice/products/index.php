<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="h3 mb-0 text-dark">Gestion des Produits</h1>
        <p class="text-muted small mb-0">Consultez, modifiez ou ajoutez vos créations de pâtisserie</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/products/create" class="btn btn-zouza">
        + Nouveau Produit
    </a>
</div>

<div class="card admin-card">
    <div class="card-body p-0">
        <?php if (empty($products)): ?>
            <div class="text-center py-5 text-muted">
                <p class="mb-3 fs-5">Aucun produit dans le catalogue.</p>
                <a href="<?= BASE_URL ?>/admin/products/create" class="btn btn-sm btn-zouza">Ajouter le premier produit</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-admin align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 70px;">Visuel</th>
                            <th>Nom du produit</th>
                            <th>Catégorie</th>
                            <th>Prix</th>
                            <th class="text-center">Statut</th>
                            <th class="text-end pe-4" style="width: 170px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $prod): ?>
                            <tr>
                                <td class="ps-4">
                                    <?php if (!empty($prod['image'])): ?>
                                        <img src="<?= BASE_URL ?>/uploads/products/<?= e($prod['image']) ?>"
                                             alt="<?= e($prod['nom']) ?>"
                                             class="rounded border"
                                             style="width: 48px; height: 48px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="rounded bg-light border d-flex align-items-center justify-content-center text-muted small"
                                             style="width: 48px; height: 48px;">
                                            &mdash;
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= e($prod['nom']) ?></div>
                                    <small class="text-muted"><code><?= e($prod['slug']) ?></code></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= e($prod['category_name']) ?>
                                    </span>
                                </td>
                                <td class="fw-bold text-nowrap">
                                    <?= formatPrice($prod['prix']) ?>
                                </td>
                                <td class="text-center">
                                    <?php if ((int)$prod['disponible'] === 1): ?>
                                        <span class="badge bg-success">Disponible</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Indisponible</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        <a href="<?= BASE_URL ?>/admin/products/edit?id=<?= (int)$prod['product_id'] ?>" class="btn btn-sm btn-outline-primary" title="Modifier">
                                            Modifier
                                        </a>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/products/delete" onsubmit="return confirm('Confirmez-vous la suppression de ce produit ?');" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                            <input type="hidden" name="id" value="<?= (int)$prod['product_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
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
