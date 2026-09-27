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
            <div class="container">
                <a class="navbar-brand" href="<?= BASE_URL ?>/">
                    <?= APP_NAME ?>
                </a>
                <span class="navbar-text small text-muted">
                    <?= APP_TAGLINE ?>
                </span>
            </div>
        </nav>
    </header>

    <main class="py-5">
        <div class="container">
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
