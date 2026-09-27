<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? '500 - Erreur Serveur', ENT_QUOTES, 'UTF-8') ?> | <?= defined('APP_NAME') ? APP_NAME : 'Zouza Shop' ?></title>
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
            max-width: 680px;
            width: 100%;
            padding: 2.5rem;
        }
        .error-code {
            font-size: 4rem;
            font-weight: 800;
            color: #c94a29;
            line-height: 1;
            margin-bottom: 0.8rem;
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
        .debug-box {
            background: #2b2b2b;
            color: #f8f8f2;
            border-radius: 8px;
            padding: 1rem;
            font-size: 0.82rem;
            max-height: 280px;
            overflow-y: auto;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="error-card mx-auto text-center">
            <div class="error-code">500</div>
            <h1 class="h4 mb-3">Une erreur inattendue est survenue</h1>
            <p class="text-muted mb-4">
                <?= (defined('APP_ENV') && APP_ENV === 'production')
                    ? 'Notre équipe technique a été notifiée. Veuillez nous excuser pour la gêne occasionnée et réessayer dans quelques instants.'
                    : 'Une exception non interceptée a interrompu l\'exécution de la requête.'
                ?>
            </p>

            <?php if (defined('APP_ENV') && APP_ENV === 'development' && isset($e)): ?>
                <div class="text-start mb-4">
                    <div class="alert alert-warning mb-2 py-2">
                        <strong>Message :</strong> <?= htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') ?><br>
                        <strong>Fichier :</strong> <?= htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8') ?>:<?= (int)$e->getLine() ?>
                    </div>
                    <details>
                        <summary class="text-muted small cursor-pointer mb-2" style="cursor: pointer;">Afficher la pile d'exécution (Trace)</summary>
                        <pre class="debug-box"><?= htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') ?></pre>
                    </details>
                </div>
            <?php endif; ?>

            <a href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>" class="btn-zouza d-inline-block">
                Retour à l'accueil
            </a>
        </div>
    </div>
</body>
</html>
