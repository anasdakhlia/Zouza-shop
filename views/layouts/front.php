<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #faf7f2;
            color: #3d2f24;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .navbar-zouza {
            background-color: #ffffff;
            border-bottom: 1px solid #ebd9c8;
        }
        .navbar-brand {
            font-weight: 700;
            color: #b86b35;
        }
        .btn-zouza-sm {
            background-color: #b86b35;
            color: #ffffff;
            border: none;
            padding: 0.35rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
        }
        .btn-zouza-sm:hover {
            background-color: #965324;
            color: #ffffff;
        }
        main {
            flex: 1;
        }
        footer {
            background-color: #2b2118;
            color: #d8c8b8;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-zouza py-3">
            <div class="container d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <a class="navbar-brand me-3" href="<?= BASE_URL ?>/">
                        <?= APP_NAME ?>
                    </a>
                    <span class="navbar-text small text-muted d-none d-md-inline me-4">
                        <?= APP_TAGLINE ?>
                    </span>
                    <a href="<?= BASE_URL ?>/products" class="btn btn-sm btn-outline-secondary d-none d-sm-inline-block">
                        Nos Zouzas
                    </a>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="<?= BASE_URL ?>/cart" class="btn btn-sm btn-outline-dark position-relative me-2">
                        &#128722; Panier
                        <?php
                            $cartCount = class_exists('Cart') ? Cart::countItems() : 0;
                            if ($cartCount > 0):
                        ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.7rem;">
                                <?= $cartCount ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <?php if (!empty($currentUser)): ?>
                        <span class="small text-muted me-2">
                            Bonjour, <strong><?= htmlspecialchars($currentUser['prenom'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </span>
                        <a href="<?= BASE_URL ?>/profile" class="btn btn-sm btn-outline-secondary">Mon Profil</a>
                        <a href="<?= BASE_URL ?>/orders" class="btn btn-sm btn-outline-dark">Mes Commandes</a>
                        <?php if ($currentUser['role'] === 'admin'): ?>
                            <a href="<?= BASE_URL ?>/admin" class="btn btn-outline-dark btn-sm">Administration</a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/logout" class="btn btn-sm btn-outline-secondary">Déconnexion</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/login" class="btn-zouza-sm">Connexion</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <main class="py-5">
        <div class="container">
            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </div>
    </main>

    <footer class="py-4 text-center mt-auto">
        <div class="container">
            <p class="mb-0">&copy; <?= date('Y') ?> <?= APP_NAME ?> — Tous droits réservés.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
