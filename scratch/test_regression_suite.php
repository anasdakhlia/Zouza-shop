<?php
/**
 * ZOUZA SHOP - Script Automatisé de Tests de Non-Régression (Phase 14)
 *
 * Exécute une suite exhaustive de tests HTTP et BDD sans altérer ni détruire les données.
 */

$baseUrl = 'http://localhost/zouza-shop';
$cookieDir = __DIR__ . '/cookies';
if (!is_dir($cookieDir)) {
    mkdir($cookieDir, 0777, true);
}

$results = [
    'total'  => 0,
    'passed' => 0,
    'failed' => 0,
    'errors' => []
];

function test(string $name, callable $fn) {
    global $results;
    $results['total']++;
    try {
        $res = $fn();
        if ($res === true) {
            $results['passed']++;
            echo "  [PASS] {$name}\n";
        } else {
            $results['failed']++;
            $msg = is_string($res) ? $res : 'Assertion failed';
            $results['errors'][] = "{$name}: {$msg}";
            echo "  [FAIL] {$name} - {$msg}\n";
        }
    } catch (Throwable $e) {
        $results['failed']++;
        $results['errors'][] = "{$name}: Exception: " . $e->getMessage();
        echo "  [FAIL] {$name} - Exception: {$e->getMessage()}\n";
    }
}

function http_req(string $method, string $url, array $options = []): array {
    $ch = curl_init();
    $cookieFile = $options['cookie_jar'] ?? null;
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Do not auto-follow to inspect 302/redirects
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }

    if (!empty($options['post_fields'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($options['post_fields']));
    }

    if (!empty($options['headers'])) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $options['headers']);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    
    $headerText = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    // Parse Location header if present
    $redirectLocation = null;
    if (preg_match('/^Location:\s*(.*?)$/mi', $headerText, $matches)) {
        $redirectLocation = trim($matches[1]);
    }

    curl_close($ch);

    return [
        'code'     => $httpCode,
        'headers'  => $headerText,
        'body'     => $body,
        'location' => $redirectLocation
    ];
}

function extract_csrf(string $html): ?string {
    if (preg_match('/name=["\']csrf_token["\']\s+value=["\']([^"\']+)["\']/i', $html, $m)) {
        return $m[1];
    }
    if (preg_match('/value=["\']([^"\']+)["\']\s+name=["\']csrf_token["\']/i', $html, $m)) {
        return $m[1];
    }
    return null;
}

echo "====================================================================\n";
echo "   ZOUZA SHOP - SUITE DE TESTS DE NON-RÉGRESSION (PHASE 14)\n";
echo "====================================================================\n\n";

// -------------------------------------------------------------------
// 1. Contrôle des En-têtes de Sécurité HTTP
// -------------------------------------------------------------------
echo "--- 1. SÉCURITÉ & EN-TÊTES HTTP ---\n";

test("En-têtes de sécurité sur la page d'accueil", function() use ($baseUrl) {
    $res = http_req('GET', $baseUrl . '/');
    if ($res['code'] !== 200) return "Code HTTP {$res['code']} au lieu de 200";
    if (!str_contains($res['headers'], 'X-Content-Type-Options: nosniff')) return "Header X-Content-Type-Options manquant";
    if (!str_contains($res['headers'], 'X-Frame-Options: SAMEORIGIN')) return "Header X-Frame-Options manquant";
    if (!str_contains($res['headers'], 'Referrer-Policy: strict-origin-when-cross-origin')) return "Header Referrer-Policy manquant";
    if (str_contains($res['headers'], 'X-Powered-By: PHP')) return "Header X-Powered-By présent (devrait être masqué)";
    return true;
});

// -------------------------------------------------------------------
// 2. Contrôle d'Accès & Autorisations (Mode Invité / Non-Authentifié)
// -------------------------------------------------------------------
echo "\n--- 2. AUTORISATIONS & ACCÈS INVITÉ (NON CONNECTÉ) ---\n";

$protectedGuestRoutes = [
    '/profile'          => '/login',
    '/orders'           => '/login',
    '/admin'            => '/login',
    '/admin/dashboard'  => '/login',
    '/admin/products'   => '/login',
    '/admin/categories' => '/login',
    '/admin/orders'     => '/login',
];

foreach ($protectedGuestRoutes as $route => $expectedRedirect) {
    test("Accès invité à {$route} doit rediriger vers {$expectedRedirect}", function() use ($baseUrl, $route, $expectedRedirect) {
        $res = http_req('GET', $baseUrl . $route);
        if ($res['code'] !== 302) return "Code HTTP {$res['code']} au lieu de 302";
        if (!$res['location'] || !str_contains($res['location'], $expectedRedirect)) {
            return "Redirection vers '{$res['location']}' au lieu de '{$expectedRedirect}'";
        }
        return true;
    });
}

// -------------------------------------------------------------------
// 3. Catalogue Public, Recherche, Filtres & Pagination
// -------------------------------------------------------------------
echo "\n--- 3. CATALOGUE PUBLIC, RECHERCHE & FILTRES ---\n";

test("Page d'accueil publique (200 OK)", function() use ($baseUrl) {
    $res = http_req('GET', $baseUrl . '/');
    return ($res['code'] === 200 && str_contains($res['body'], 'Zouza Shop')) ? true : "Échec chargement accueil";
});

test("Catalogue complet /products (200 OK)", function() use ($baseUrl) {
    $res = http_req('GET', $baseUrl . '/products');
    return ($res['code'] === 200 && str_contains($res['body'], 'Catalogue')) ? true : "Échec chargement catalogue";
});

test("Filtre catalogue par catégorie valide (slug: zouza-traditionnelle)", function() use ($baseUrl) {
    $res = http_req('GET', $baseUrl . '/products?category=zouza-traditionnelle');
    return ($res['code'] === 200 && str_contains($res['body'], 'Zouza Traditionnelle')) ? true : "Échec filtre catégorie";
});

test("Filtre catalogue par recherche textuelle (q=Caramel)", function() use ($baseUrl) {
    $res = http_req('GET', $baseUrl . '/products?q=Caramel');
    return ($res['code'] === 200 && str_contains($res['body'], 'Caramel')) ? true : "Échec recherche Caramel";
});

test("Tri catalogue par prix croissant (sort=price_asc)", function() use ($baseUrl) {
    $res = http_req('GET', $baseUrl . '/products?sort=price_asc');
    return ($res['code'] === 200) ? true : "Échec tri price_asc";
});

test("Tri catalogue par prix décroissant (sort=price_desc)", function() use ($baseUrl) {
    $res = http_req('GET', $baseUrl . '/products?sort=price_desc');
    return ($res['code'] === 200) ? true : "Échec tri price_desc";
});

test("Fiche produit par ID valide (/products/detail?id=1)", function() use ($baseUrl) {
    $res = http_req('GET', $baseUrl . '/products/detail?id=1');
    return ($res['code'] === 200 && str_contains($res['body'], 'Zouza Classique Caramel')) ? true : "Échec détail id=1";
});

test("Fiche produit par SLUG valide (/products/detail?slug=zouza-classique-caramel)", function() use ($baseUrl) {
    $res = http_req('GET', $baseUrl . '/products/detail?slug=zouza-classique-caramel');
    return ($res['code'] === 200 && str_contains($res['body'], 'Zouza Classique Caramel')) ? true : "Échec détail slug";
});

test("Fiche produit inexistante (/products/detail?id=99999) donne 404", function() use ($baseUrl) {
    $res = http_req('GET', $baseUrl . '/products/detail?id=99999');
    return ($res['code'] === 404) ? true : "Code {$res['code']} au lieu de 404";
});

// -------------------------------------------------------------------
// 4. Authentification & Cycle de Vie des Sessions
// -------------------------------------------------------------------
echo "\n--- 4. AUTHENTIFICATION & SESSIONS ---\n";

$customerCookie = $cookieDir . '/customer.txt';
if (file_exists($customerCookie)) unlink($customerCookie);

test("Affichage du formulaire de connexion /login (200 OK)", function() use ($baseUrl) {
    $res = http_req('GET', $baseUrl . '/login');
    return ($res['code'] === 200 && str_contains($res['body'], 'Connexion')) ? true : "Formulaire login inaccessible";
});

test("Échec de connexion avec mot de passe erroné", function() use ($baseUrl, $customerCookie) {
    $loginPage = http_req('GET', $baseUrl . '/login', ['cookie_jar' => $customerCookie]);
    $token = extract_csrf($loginPage['body']);
    if (!$token) return "Impossible de récupérer le token CSRF sur /login";

    $postRes = http_req('POST', $baseUrl . '/login', [
        'cookie_jar'  => $customerCookie,
        'post_fields' => [
            'csrf_token' => $token,
            'email'      => 'customer@zouza.local',
            'password'   => 'mauvaismotdepasse'
        ]
    ]);
    if ($postRes['code'] !== 302) return "Code HTTP {$postRes['code']} au lieu de 302";

    // Suivre la redirection pour vérifier le message d'erreur
    $follow = http_req('GET', $baseUrl . '/login', ['cookie_jar' => $customerCookie]);
    return str_contains($follow['body'], 'incorrect') ? true : "Message d'erreur absent";
});

test("Connexion réussie du client de test (customer@zouza.local / customer123)", function() use ($baseUrl, $customerCookie) {
    $loginPage = http_req('GET', $baseUrl . '/login', ['cookie_jar' => $customerCookie]);
    $token = extract_csrf($loginPage['body']);
    
    $postRes = http_req('POST', $baseUrl . '/login', [
        'cookie_jar'  => $customerCookie,
        'post_fields' => [
            'csrf_token' => $token,
            'email'      => 'customer@zouza.local',
            'password'   => 'customer123'
        ]
    ]);
    if ($postRes['code'] !== 302) return "Code HTTP {$postRes['code']} au lieu de 302";
    if (str_contains($postRes['location'] ?? '', '/login')) {
        return "Redirection vers /login au lieu de la page d'accueil (échec authentification)";
    }
    return true;
});

test("Client connecté accède à son profil /profile (200 OK)", function() use ($baseUrl, $customerCookie) {
    $res = http_req('GET', $baseUrl . '/profile', ['cookie_jar' => $customerCookie]);
    return ($res['code'] === 200 && str_contains($res['body'], 'customer@zouza.local')) ? true : "Profil client inaccessible";
});

test("Client connecté accède à ses commandes /orders (200 OK)", function() use ($baseUrl, $customerCookie) {
    $res = http_req('GET', $baseUrl . '/orders', ['cookie_jar' => $customerCookie]);
    return ($res['code'] === 200) ? true : "Mes commandes inaccessible";
});

test("Client ordinaire se voit refuser l'accès à /admin (403 Forbidden)", function() use ($baseUrl, $customerCookie) {
    $res = http_req('GET', $baseUrl . '/admin', ['cookie_jar' => $customerCookie]);
    return ($res['code'] === 403) ? true : "Code {$res['code']} au lieu de 403";
});

test("Déconnexion du client via /logout", function() use ($baseUrl, $customerCookie) {
    $res = http_req('GET', $baseUrl . '/logout', ['cookie_jar' => $customerCookie]);
    if ($res['code'] !== 302) return "Code HTTP {$res['code']} au lieu de 302";
    
    // Vérifier que le profil n'est plus accessible
    $check = http_req('GET', $baseUrl . '/profile', ['cookie_jar' => $customerCookie]);
    return ($check['code'] === 302 && str_contains($check['location'] ?? '', '/login')) ? true : "Session non détruite";
});

// -------------------------------------------------------------------
// 5. Backoffice Administrateur
// -------------------------------------------------------------------
echo "\n--- 5. BACKOFFICE & WORKFLOW ADMIN ---\n";

$adminCookie = $cookieDir . '/admin.txt';
if (file_exists($adminCookie)) unlink($adminCookie);

test("Connexion réussie de l'administrateur (admin@zouza.local / admin123)", function() use ($baseUrl, $adminCookie) {
    $loginPage = http_req('GET', $baseUrl . '/login', ['cookie_jar' => $adminCookie]);
    $token = extract_csrf($loginPage['body']);
    
    $postRes = http_req('POST', $baseUrl . '/login', [
        'cookie_jar'  => $adminCookie,
        'post_fields' => [
            'csrf_token' => $token,
            'email'      => 'admin@zouza.local',
            'password'   => 'admin123'
        ]
    ]);
    if ($postRes['code'] !== 302) return "Code HTTP {$postRes['code']} au lieu de 302";
    if (!str_contains($postRes['location'] ?? '', '/admin')) {
        return "Redirection vers {$postRes['location']} au lieu de /admin";
    }
    return true;
});

test("Admin accède au Tableau de Bord /admin (200 OK)", function() use ($baseUrl, $adminCookie) {
    $res = http_req('GET', $baseUrl . '/admin', ['cookie_jar' => $adminCookie]);
    return ($res['code'] === 200 && str_contains($res['body'], 'Tableau de bord')) ? true : "Dashboard admin inaccessible";
});

test("Admin accède au catalogue backoffice /admin/products (200 OK)", function() use ($baseUrl, $adminCookie) {
    $res = http_req('GET', $baseUrl . '/admin/products', ['cookie_jar' => $adminCookie]);
    return ($res['code'] === 200 && str_contains($res['body'], 'Gestion des Produits')) ? true : "Admin products inaccessible";
});

test("Admin accède aux catégories backoffice /admin/categories (200 OK)", function() use ($baseUrl, $adminCookie) {
    $res = http_req('GET', $baseUrl . '/admin/categories', ['cookie_jar' => $adminCookie]);
    return ($res['code'] === 200 && str_contains($res['body'], 'Gestion des Catégories')) ? true : "Admin categories inaccessible";
});

test("Admin accède aux commandes backoffice /admin/orders (200 OK)", function() use ($baseUrl, $adminCookie) {
    $res = http_req('GET', $baseUrl . '/admin/orders', ['cookie_jar' => $adminCookie]);
    return ($res['code'] === 200 && str_contains($res['body'], 'Gestion des Commandes')) ? true : "Admin orders inaccessible";
});

test("Protection anti-IDOR : un client ne peut pas voir la commande d'un tiers (/orders/detail?id=1 -> 403)", function() use ($baseUrl, $customerCookie) {
    $res = http_req('GET', $baseUrl . '/orders/detail?id=1', ['cookie_jar' => $customerCookie]);
    return ($res['code'] === 403) ? true : "Code {$res['code']} au lieu de 403 (faille IDOR potentielle)";
});

test("Protection CSRF : un POST sans jeton est rejeté (403 Forbidden)", function() use ($baseUrl) {
    $res = http_req('POST', $baseUrl . '/cart/add', [
        'post_fields' => ['product_id' => 1, 'quantity' => 1] // Aucun token csrf
    ]);
    return ($res['code'] === 403) ? true : "Code {$res['code']} au lieu de 403";
});

test("Admin formulaire création catégorie (/admin/categories/create -> 200 OK)", function() use ($baseUrl, $adminCookie) {
    $res = http_req('GET', $baseUrl . '/admin/categories/create', ['cookie_jar' => $adminCookie]);
    return ($res['code'] === 200 && stripos($res['body'], 'nouvelle') !== false) ? true : "Échec create category";
});

test("Admin formulaire édition catégorie (/admin/categories/edit?id=1 -> 200 OK)", function() use ($baseUrl, $adminCookie) {
    $res = http_req('GET', $baseUrl . '/admin/categories/edit?id=1', ['cookie_jar' => $adminCookie]);
    return ($res['code'] === 200 && str_contains($res['body'], 'Zouza Traditionnelle')) ? true : "Échec edit category";
});

test("Admin formulaire création produit (/admin/products/create -> 200 OK)", function() use ($baseUrl, $adminCookie) {
    $res = http_req('GET', $baseUrl . '/admin/products/create', ['cookie_jar' => $adminCookie]);
    return ($res['code'] === 200 && stripos($res['body'], 'nouveau') !== false) ? true : "Échec create product";
});

test("Admin formulaire édition produit (/admin/products/edit?id=1 -> 200 OK)", function() use ($baseUrl, $adminCookie) {
    $res = http_req('GET', $baseUrl . '/admin/products/edit?id=1', ['cookie_jar' => $adminCookie]);
    return ($res['code'] === 200 && str_contains($res['body'], 'Zouza Classique Caramel')) ? true : "Échec edit product";
});

// -------------------------------------------------------------------
// 6. Workflow Panier (Cart)
// -------------------------------------------------------------------
echo "\n--- 6. WORKFLOW PANIER (CART) ---\n";

$cartCookie = $cookieDir . '/cart.txt';
if (file_exists($cartCookie)) unlink($cartCookie);

test("Consultation d'un panier vierge (/cart)", function() use ($baseUrl, $cartCookie) {
    $res = http_req('GET', $baseUrl . '/cart', ['cookie_jar' => $cartCookie]);
    return ($res['code'] === 200 && (str_contains($res['body'], 'vide') || str_contains($res['body'], 'Panier'))) ? true : "Échec /cart";
});

test("Ajout d'un article au panier (POST /cart/add)", function() use ($baseUrl, $cartCookie) {
    $productPage = http_req('GET', $baseUrl . '/products/detail?id=1', ['cookie_jar' => $cartCookie]);
    $token = extract_csrf($productPage['body']);
    if (!$token) return "Token CSRF introuvable sur la fiche produit";

    $addRes = http_req('POST', $baseUrl . '/cart/add', [
        'cookie_jar'  => $cartCookie,
        'post_fields' => [
            'csrf_token' => $token,
            'product_id' => 1,
            'quantity'   => 2
        ]
    ]);
    if ($addRes['code'] !== 302) return "Code HTTP {$addRes['code']} au lieu de 302";

    $cartPage = http_req('GET', $baseUrl . '/cart', ['cookie_jar' => $cartCookie]);
    return str_contains($cartPage['body'], 'Zouza Classique Caramel') ? true : "Article absent du panier";
});

test("Mise à jour de la quantité dans le panier (POST /cart/update)", function() use ($baseUrl, $cartCookie) {
    $cartPage = http_req('GET', $baseUrl . '/cart', ['cookie_jar' => $cartCookie]);
    $token = extract_csrf($cartPage['body']);
    if (!$token) return "Token CSRF introuvable sur la page panier";

    $upRes = http_req('POST', $baseUrl . '/cart/update', [
        'cookie_jar'  => $cartCookie,
        'post_fields' => [
            'csrf_token' => $token,
            'product_id' => 1,
            'quantity'   => 4
        ]
    ]);
    if ($upRes['code'] !== 302) return "Code HTTP {$upRes['code']} au lieu de 302";

    $cartPage2 = http_req('GET', $baseUrl . '/cart', ['cookie_jar' => $cartCookie]);
    return str_contains($cartPage2['body'], 'value="4"') ? true : "Quantité non mise à jour à 4";
});

test("Suppression d'un article du panier (POST /cart/remove)", function() use ($baseUrl, $cartCookie) {
    $cartPage = http_req('GET', $baseUrl . '/cart', ['cookie_jar' => $cartCookie]);
    $token = extract_csrf($cartPage['body']);
    if (!$token) return "Token CSRF introuvable";

    $delRes = http_req('POST', $baseUrl . '/cart/remove', [
        'cookie_jar'  => $cartCookie,
        'post_fields' => [
            'csrf_token' => $token,
            'product_id' => 1
        ]
    ]);
    if ($delRes['code'] !== 302) return "Code HTTP {$delRes['code']} au lieu de 302";

    $cartPage2 = http_req('GET', $baseUrl . '/cart', ['cookie_jar' => $cartCookie]);
    return (str_contains($cartPage2['body'], 'vide') || !str_contains($cartPage2['body'], 'value="4"')) ? true : "Article non supprimé";
});

// -------------------------------------------------------------------
// 7. Validation Checkout & Protection Panier Vide
// -------------------------------------------------------------------
echo "\n--- 7. CHECKOUT & VALIDATION ---\n";

test("Accès à /checkout avec panier vide redirige vers /cart", function() use ($baseUrl, $cartCookie) {
    $res = http_req('GET', $baseUrl . '/checkout', ['cookie_jar' => $cartCookie]);
    return ($res['code'] === 302 && str_contains($res['location'] ?? '', '/cart')) ? true : "Échec redirection panier vide";
});

test("Validation checkout refuse formulaire incomplet (champs obligatoires)", function() use ($baseUrl, $cartCookie) {
    // 1. Ajouter un article d'abord
    $prodPage = http_req('GET', $baseUrl . '/products/detail?id=2', ['cookie_jar' => $cartCookie]);
    $token = extract_csrf($prodPage['body']);
    http_req('POST', $baseUrl . '/cart/add', [
        'cookie_jar'  => $cartCookie,
        'post_fields' => ['csrf_token' => $token, 'product_id' => 2, 'quantity' => 1]
    ]);

    // 2. Accéder au checkout
    $checkoutPage = http_req('GET', $baseUrl . '/checkout', ['cookie_jar' => $cartCookie]);
    $checkoutToken = extract_csrf($checkoutPage['body']);

    // 3. Soumettre sans nom ni téléphone
    $postRes = http_req('POST', $baseUrl . '/checkout', [
        'cookie_jar'  => $cartCookie,
        'post_fields' => [
            'csrf_token'     => $checkoutToken,
            'prenom'         => 'Test',
            'nom'            => '', // MANQUANT
            'email'          => 'invalide-email', // INVALIDE
            'telephone'      => '', // MANQUANT
            'type_livraison' => 'livraison'
        ]
    ]);
    if ($postRes['code'] !== 302) return "Code {$postRes['code']} au lieu de 302";

    // Vérifier retour sur checkout avec message d'erreur
    $follow = http_req('GET', $baseUrl . '/checkout', ['cookie_jar' => $cartCookie]);
    return (str_contains($follow['body'], 'obligatoire') || str_contains($follow['body'], 'invalide')) ? true : "Pas d'erreur affichée";
});

// -------------------------------------------------------------------
// 8. Gestion des Pages d'Erreurs Contrôlées
// -------------------------------------------------------------------
echo "\n--- 8. GESTION DES ERREURS ---\n";

test("Route inexistante (/route-inexistante-404) retourne 404 personnalisé", function() use ($baseUrl) {
    $res = http_req('GET', $baseUrl . '/route-inexistante-404');
    return ($res['code'] === 404 && str_contains($res['body'], '404')) ? true : "Page 404 incorrecte (code {$res['code']})";
});

// -------------------------------------------------------------------
// 9. Intégrité et Cohérence de la Base de Données
// -------------------------------------------------------------------
echo "\n--- 9. INTÉGRITÉ & COHÉRENCE BASE DE DONNÉES ---\n";

test("Connexion PDO et intégrité référentielle", function() {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';
    
    $pdo = Database::getConnection();
    
    // Vérifier tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $requiredTables = ['categories', 'products', 'users', 'orders', 'order_items'];
    foreach ($requiredTables as $req) {
        if (!in_array($req, $tables, true)) {
            return "Table manquante : {$req}";
        }
    }

    // Vérifier intégrité products -> categories
    $orphanedProducts = $pdo->query("
        SELECT COUNT(*) FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE c.category_id IS NULL
    ")->fetchColumn();
    if ($orphanedProducts > 0) return "Produits orphelins détectés ({$orphanedProducts})";

    // Vérifier intégrité order_items -> orders
    $orphanedItems = $pdo->query("
        SELECT COUNT(*) FROM order_items oi 
        LEFT JOIN orders o ON oi.order_id = o.order_id 
        WHERE o.order_id IS NULL
    ")->fetchColumn();
    if ($orphanedItems > 0) return "Lignes de commandes orphelines ({$orphanedItems})";

    return true;
});

// -------------------------------------------------------------------
// Résumé Final
// -------------------------------------------------------------------
echo "\n====================================================================\n";
echo "   RÉSUMÉ : {$results['passed']} / {$results['total']} tests réussis\n";
if ($results['failed'] > 0) {
    echo "   ÉCHECS : {$results['failed']} test(s) en erreur :\n";
    foreach ($results['errors'] as $err) {
        echo "     - {$err}\n";
    }
} else {
    echo "   SUCCÈS TOTAL : 100% des tests de régression sont passés !\n";
}
echo "====================================================================\n";

exit($results['failed'] > 0 ? 1 : 0);
