<?php
$seoDesc = !empty($metaDescription) ? $metaDescription : (APP_NAME . ' — Pâtisserie artisanale tunisienne d’excellence. Découvrez nos Zouzas faites maison aux amandes, noisettes, pistaches et chocolat.');
$seoCanonical = !empty($canonicalUrl) ? $canonicalUrl : (BASE_URL . strtok($_SERVER['REQUEST_URI'] ?? '/', '?'));
$seoRobots = !empty($robotsMeta) ? $robotsMeta : 'index, follow';
$seoOgType = !empty($ogType) ? $ogType : 'website';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($seoDesc, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="robots" content="<?= htmlspecialchars($seoRobots, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="canonical" href="<?= htmlspecialchars($seoCanonical, ENT_QUOTES, 'UTF-8') ?>">

    <!-- Open Graph / Réseaux Sociaux -->
    <meta property="og:locale" content="fr_FR">
    <meta property="og:type" content="<?= htmlspecialchars($seoOgType, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle ?? APP_NAME, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($seoDesc, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:url" content="<?= htmlspecialchars($seoCanonical, ENT_QUOTES, 'UTF-8') ?>">
    <?php if (!empty($ogImage)): ?>
    <meta property="og:image" content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/front.css" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-zouza py-2 py-lg-3">
            <div class="container">
                <!-- Brand Logo & Tagline -->
                <a class="navbar-brand d-flex align-items-center me-3" href="<?= BASE_URL ?>/">
                    <span><?= APP_NAME ?></span>
                </a>
                <span class="navbar-text small text-muted d-none d-xl-inline me-3">
                    <?= APP_TAGLINE ?>
                </span>

                <!-- Quick Action on Mobile: Cart Badge + Navbar Toggler -->
                <div class="d-flex align-items-center gap-2 order-lg-last">
                    <a href="<?= BASE_URL ?>/cart" class="btn btn-sm btn-outline-dark position-relative me-1" title="Voir le panier">
                        &#128722; <span class="d-none d-sm-inline">Panier</span>
                        <?php
                            $cartCount = class_exists('Cart') ? Cart::countItems() : 0;
                            if ($cartCount > 0):
                        ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.7rem;">
                                <?= $cartCount ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <button class="navbar-toggler border-0 p-2" type="button" data-bs-toggle="collapse" data-bs-target="#frontNav" aria-controls="frontNav" aria-expanded="false" aria-label="Menu de navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>

                <!-- Collapsible Navigation Menu -->
                <div class="collapse navbar-collapse" id="frontNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 py-2 py-lg-0">
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>/products" class="nav-link">
                                &#129367; Nos Zouzas
                            </a>
                        </li>
                    </ul>

                    <!-- User Account / Authentication Links -->
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-2 pt-2 pt-lg-0 border-top border-lg-0">
                        <?php if (!empty($currentUser)): ?>
                            <span class="small text-muted me-lg-2 py-1">
                                Bonjour, <strong><?= htmlspecialchars($currentUser['prenom'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </span>
                            <a href="<?= BASE_URL ?>/profile" class="btn btn-sm btn-outline-secondary">
                                Mon Profil
                            </a>
                            <a href="<?= BASE_URL ?>/orders" class="btn btn-sm btn-outline-dark">
                                Mes Commandes
                            </a>
                            <?php if ($currentUser['role'] === 'admin'): ?>
                                <a href="<?= BASE_URL ?>/admin" class="btn btn-sm btn-zouza">
                                    Administration &rarr;
                                </a>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/logout" class="btn btn-sm btn-outline-danger">
                                Déconnexion
                            </a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/login" class="btn-zouza-sm">
                                &#128274; Connexion
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main class="py-4 py-lg-5">
        <div class="container">
            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                    <span class="fs-5">&#10003;</span>
                    <div class="flex-grow-1"><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                    <span class="fs-5">&#9888;</span>
                    <div class="flex-grow-1"><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($flashInfo)): ?>
                <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                    <span class="fs-5">&#8505;</span>
                    <div class="flex-grow-1"><?= htmlspecialchars($flashInfo, ENT_QUOTES, 'UTF-8') ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </div>
    </main>

    <footer class="py-4 text-center mt-auto border-top bg-white">
        <div class="container">
            <nav class="d-flex flex-wrap justify-content-center gap-3 mb-2 small" aria-label="Navigation pied de page">
                <a href="<?= BASE_URL ?>/" class="text-decoration-none text-muted">Accueil</a>
                <a href="<?= BASE_URL ?>/products" class="text-decoration-none text-muted">Nos Zouzas</a>
                <a href="<?= BASE_URL ?>/cart" class="text-decoration-none text-muted">Mon Panier</a>
                <?php if (!empty($currentUser)): ?>
                    <a href="<?= BASE_URL ?>/profile" class="text-decoration-none text-muted">Mon Profil</a>
                    <a href="<?= BASE_URL ?>/orders" class="text-decoration-none text-muted">Mes Commandes</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/login" class="text-decoration-none text-muted">Connexion</a>
                <?php endif; ?>
            </nav>
            <p class="mb-0 text-muted small">&copy; <?= date('Y') ?> <?= APP_NAME ?> — Tous droits réservés.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
