<?php
require_once 'config/db.php';

// --- CONFIGURATION ---
$full_name = "Super Admin";
$email     = "admin@chalojoin.com";
$password  = "Pawan16925@"; // CHANGE THIS PASSWORD!
// ---------------------

// 1. Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 2. Prepare SQL
// We manually force the role to 'admin' and status to 'approved'
$sql = "INSERT INTO users (full_name, email, password, role, status) 
        VALUES (:name, :email, :pass, 'admin', 'approved')";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name' => $full_name,
        ':email' => $email,
        ':pass' => $hashed_password
    ]);
    
    echo "<h3 style='color: green;'>Success!</h3>";
    echo "Admin created.<br>";
    echo "Email: <strong>$email</strong><br>";
    echo "Password: <strong>$password</strong><br><br>";
    echo "<a href='admin/login.php'>Go to Admin Login</a>";

} catch (PDOException $e) {
    echo "<h3 style='color: red;'>Error</h3>";
    if ($e->getCode() == 23000) {
        echo "This email is already registered. Please check your database.";
    } else {
        echo "Database Error: " . $e->getMessage();
    }
}
?>