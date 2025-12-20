<?php
$host = 'db.fr-pari1.bengt.wasmernet.com';
$db_name = 'dbizcJoNvgRxbjw6S2tUbnDY'; // Update if your DB name is different
$username = '6b4e22d4732a80001d09d3995e4f';
$password = '06946b4e-22d4-7799-8000-584501023797';
$port = 10272;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    // Set error mode to exception for easier debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>