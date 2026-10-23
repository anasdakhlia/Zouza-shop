<?php
$dsn = "mysql:host=localhost;port=3306;dbname=zouza_shop;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_TIMEOUT => 3
];
try {
    echo "Connecting to localhost...\n";
    $pdo = new PDO($dsn, "root", "", $options);
    echo "Connected!\n";
    $res = $pdo->query("SELECT DATABASE()")->fetchColumn();
    echo "Current DB: " . $res . "\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(", ", $tables) . "\n";
    $users = $pdo->query("SELECT user_id, email, password, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $u) {
        $pAdmin = password_verify('admin123', $u['password']) ? 'YES' : 'NO';
        $pCustomer = password_verify('customer123', $u['password']) ? 'YES' : 'NO';
        $pSecret = password_verify('password', $u['password']) ? 'YES' : 'NO';
        $p123456 = password_verify('123456', $u['password']) ? 'YES' : 'NO';
        echo "User {$u['email']}: admin123=$pAdmin, customer123=$pCustomer, password=$pSecret, 123456=$p123456\n";
    }
} catch (Throwable $e) {
    echo "Caught: " . $e->getMessage() . "\n";
}
