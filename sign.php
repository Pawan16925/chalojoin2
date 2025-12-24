<?php
// Include PDO connection
include 'config/db.php';

$full_name = "Super Admin";
$email = "admin@chalojoin.com";
$plain_password = "Pawan16925@";

// Hash password
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

// Check if admin already exists
$checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$checkStmt->execute([$email]);

if ($checkStmt->rowCount() > 0) {
    echo "<h3>❌ Error: An account with email '$email' already exists.</h3>";
    exit;
}

// Insert admin
$role = 'admin';
$status = 'approved';
$is_verified = 1; // optional but recommended

$insertStmt = $pdo->prepare("
    INSERT INTO users (full_name, email, password, role, status, is_verified)
    VALUES (?, ?, ?, ?, ?, ?)
");

if ($insertStmt->execute([
    $full_name,
    $email,
    $hashed_password,
    $role,
    $status,
    $is_verified
])) {
    echo "
    <div style='font-family:sans-serif;padding:20px;background:#d4edda;color:#155724;border-radius:6px'>
        <h2>✅ Admin Account Created Successfully!</h2>
        <p><b>Email:</b> $email</p>
        <p><b>Password:</b> $plain_password</p>
        <a href='login.php'>Login Now</a>
    </div>";
} else {
    echo "❌ Error creating admin";
}
