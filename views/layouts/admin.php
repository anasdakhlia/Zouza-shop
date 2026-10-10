<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Administration') ?> | <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f6f7fb;
            color: #2c2724;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .admin-navbar {
            background-color: #2b2118;
            border-bottom: 2px solid #b86b35;
        }
        .admin-navbar .navbar-brand {
            font-weight: 700;
            color: #f7d9b9;
        }
        .admin-navbar .nav-link {
            color: #d8c8b8;
            font-weight: 500;
            padding: 0.5rem 0.85rem;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        .admin-navbar .nav-link:hover,
        .admin-navbar .nav-link.active {
            color: #ffffff;
            background-color: rgba(184, 107, 53, 0.35);
        }
        .btn-zouza {
            background-color: #b86b35;
            color: #ffffff;
            border: none;
        }
        .btn-zouza:hover {
            background-color: #965324;
            color: #ffffff;
        }
        .admin-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
            background: #ffffff;
        }
        .table-admin th {
            background-color: #f8f9fa;
            color: #555;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        footer {
            margin-top: auto;
            background-color: #ffffff;
            border-top: 1px solid #e5e7eb;
            color: #6c757d;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg admin-navbar py-2">
            <div class="container-fluid px-4">
                <a class="navbar-brand me-4" href="<?= BASE_URL ?>/admin">
                    <?= APP_NAME ?> <span class="badge bg-warning text-dark fs-6 ms-1">Admin</span>
                </a>
                <button class="navbar-toggler text-light border-secondary" type="button" data-bs-dismiss="collapse" data-bs-target="#adminNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="adminNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/admin">Tableau de bord</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/admin/categories">Catégories</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/admin/products">Produits</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/admin/orders">Commandes</a>
                        </li>
                    </ul>
                    <div class="d-flex align-items-center gap-3">
                        <a href="<?= BASE_URL ?>/" class="btn btn-sm btn-outline-light" target="_blank">
                            Voir le site public &rarr;
                        </a>
                        <span class="text-light small d-none d-md-inline">
                            <?= e($currentUser['prenom'] ?? 'Admin') ?> (<strong><?= e($currentUser['role'] ?? 'admin') ?></strong>)
                        </span>
                        <a href="<?= BASE_URL ?>/logout" class="btn btn-sm btn-danger">
                            Déconnexion
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main class="py-4 flex-grow-1">
        <div class="container-fluid px-4">
            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <?= e($flashSuccess) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <?= e($flashError) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($flashInfo)): ?>
                <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                    <?= e($flashInfo) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </div>
    </main>

    <footer class="py-3 text-center">
        <div class="container-fluid">
            &copy; <?= date('Y') ?> <?= APP_NAME ?> — Espace d'administration sécurisé
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
