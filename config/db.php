<?php
$host = 'db.fr-pari1.bengt.wasmernet.com';
$port = 10272;
$db_name = 'dbizcJoNvgRxbjw6S2tUbnDY';
$username = '6b4e22d4732a80001d09d3995e4f';
$password = '06946b4e-22d4-7799-8000-584501023797'; // 👈 REQUIRED

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db_name;charset=utf8mb4";

    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
        ]
    );

    // echo "✅ Connected to Wasmer DB successfully";
} catch (PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage());
}
