<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? '404 - Page Non Trouvée', ENT_QUOTES, 'UTF-8') ?> | <?= defined('APP_NAME') ? APP_NAME : 'Zouza Shop' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fcf9f5;
            color: #4a3828;
            font-family: system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-card {
            background: #ffffff;
            border: 1px solid #ebd9c8;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(139, 90, 43, 0.08);
            max-width: 520px;
            width: 100%;
            padding: 2.5rem;
            text-align: center;
        }
        .error-code {
            font-size: 5rem;
            font-weight: 800;
            color: #b86b35;
            line-height: 1;
            margin-bottom: 1rem;
        }
        .btn-zouza {
            background-color: #b86b35;
            color: #ffffff;
            border: none;
            padding: 0.6rem 1.8rem;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: background-color 0.2s;
        }
        .btn-zouza:hover {
            background-color: #965324;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-card mx-auto">
            <div class="error-code">404</div>
            <h1 class="h4 mb-3">Douceur introuvable...</h1>
            <p class="text-muted mb-4">
                <?= htmlspecialchars($message ?? 'La page que vous recherchez n\'existe pas ou a été déplacée.', ENT_QUOTES, 'UTF-8') ?>
            </p>
            <a href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>" class="btn-zouza d-inline-block">
                Retour à la boutique
            </a>
        </div>
    </div>
</body>
</html>
