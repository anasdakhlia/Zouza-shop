<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Connexion', ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #faf7f2;
            color: #3d2f24;
            font-family: system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #ffffff;
            border: 1px solid #ebd9c8;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(139, 90, 43, 0.08);
            max-width: 440px;
            width: 100%;
            padding: 2.5rem;
        }
        .brand-title {
            color: #b86b35;
            font-weight: 700;
        }
        .btn-zouza {
            background-color: #b86b35;
            color: #ffffff;
            border: none;
            padding: 0.65rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            transition: background-color 0.2s;
        }
        .btn-zouza:hover {
            background-color: #965324;
            color: #ffffff;
        }
        .form-control:focus {
            border-color: #b86b35;
            box-shadow: 0 0 0 0.25rem rgba(184, 107, 53, 0.2);
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="login-card mx-auto">
            <div class="text-center mb-4">
                <h1 class="h3 brand-title mb-1"><?= APP_NAME ?></h1>
                <p class="text-muted small">Espace de Connexion</p>
            </div>

            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger py-2 small mb-3" role="alert">
                    <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success py-2 small mb-3" role="alert">
                    <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/login" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold">Adresse Email</label>
                    <input type="email" class="form-control" id="email" name="email" required autocomplete="email" autofocus>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label small fw-semibold">Mot de passe</label>
                    <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                </div>

                <button type="submit" class="btn btn-zouza">
                    Se connecter
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="<?= BASE_URL ?>/" class="text-decoration-none text-muted small">
                    &larr; Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</body>
</html>
