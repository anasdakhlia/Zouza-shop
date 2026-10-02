<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Administration', ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            color: #2b2b2b;
            min-height: 100vh;
        }
        .admin-header {
            background: #2b2118;
            color: #ffffff;
            padding: 1rem 0;
        }
        .btn-outline-light-custom {
            border-color: #ebd9c8;
            color: #ebd9c8;
        }
        .btn-outline-light-custom:hover {
            background-color: #b86b35;
            color: #ffffff;
            border-color: #b86b35;
        }
    </style>
</head>
<body>
    <header class="admin-header mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <span class="fw-bold fs-5 text-warning"><?= APP_NAME ?></span>
                <span class="badge bg-danger ms-2">Back-Office (Test Accès)</span>
            </div>
            <div>
                <span class="me-3 small text-light">
                    Connecté : <strong><?= htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom'], ENT_QUOTES, 'UTF-8') ?></strong> (<?= htmlspecialchars($currentUser['role'], ENT_QUOTES, 'UTF-8') ?>)
                </span>
                <a href="<?= BASE_URL ?>/logout" class="btn btn-sm btn-outline-light-custom">
                    Déconnexion
                </a>
            </div>
        </div>
    </header>

    <main class="container py-4">
        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm p-4 rounded-3">
            <h1 class="h4 mb-3 text-dark">Vérification de l'Accès Administrateur</h1>
            <div class="alert alert-info mb-4">
                <strong>✓ Contrôle d'accès réussi :</strong> Seul un utilisateur disposant du rôle <code>admin</code> peut atteindre cette page. Les requêtes non authentifiées sont redirigées vers <code>/login</code> et les utilisateurs de rôle <code>customer</code> reçoivent un statut HTTP <code>403 Forbidden</code>.
            </div>

            <h5 class="card-title h6 text-uppercase text-muted">Informations de session courante</h5>
            <ul class="list-group list-group-flush mb-4">
                <li class="list-group-item px-0">Identifiant : <strong>#<?= (int)$currentUser['user_id'] ?></strong></li>
                <li class="list-group-item px-0">Nom complet : <strong><?= htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom'], ENT_QUOTES, 'UTF-8') ?></strong></li>
                <li class="list-group-item px-0">Adresse Email : <strong><?= htmlspecialchars($currentUser['email'], ENT_QUOTES, 'UTF-8') ?></strong></li>
                <li class="list-group-item px-0">Rôle validé : <span class="badge bg-success"><?= htmlspecialchars($currentUser['role'], ENT_QUOTES, 'UTF-8') ?></span></li>
            </ul>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card bg-light border p-3 rounded-3 h-100">
                        <h6 class="fw-bold mb-1">Catégories de Pâtisseries</h6>
                        <p class="small text-muted mb-3">Créer et gérer les catégories de Zouza.</p>
                        <a href="<?= BASE_URL ?>/admin/categories" class="btn btn-outline-dark btn-sm mt-auto">Gérer les catégories &rarr;</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light border p-3 rounded-3 h-100">
                        <h6 class="fw-bold mb-1">Catalogue Produits</h6>
                        <p class="small text-muted mb-3">Ajouter des délices, ajuster les prix et photos.</p>
                        <a href="<?= BASE_URL ?>/admin/products" class="btn btn-outline-dark btn-sm mt-auto">Gérer les produits &rarr;</a>
                    </div>
                </div>
            </div>

            <div>
                <a href="<?= BASE_URL ?>/" class="btn btn-secondary btn-sm me-2">Voir le site public</a>
                <a href="<?= BASE_URL ?>/logout" class="btn btn-danger btn-sm">Se déconnecter</a>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
