<?php
$host = 'db.fr-pari1.bengt.wasmernet.com';
$port = '10272';
$db_name = 'dbizcJoNvgRxbjw6S2tUbnDY';
$username = '6b4e22d4732a80001d09d3995e4f';
$password = '06946b4e-22d4-7799-8000-584501023797';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db_name;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5
        ]
    );

    echo "✅ Database connected successfully";
} catch (PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage());
}
?>
