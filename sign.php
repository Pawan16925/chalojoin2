<?php
// Include the database connection
include 'config/db.php';

// Credentials provided
$full_name = "Super Admin";
$email = "admin@chalojoin.com";
$plain_password = "Pawan16925@";

// 1. Hash the password securely using PHP's default algorithm (Bcrypt)
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

// 2. Prepare the SQL statement to check if admin already exists
$checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    echo "<h3>Error: An account with email '$email' already exists.</h3>";
} else {
    // 3. Insert the new Admin user
    // Role is 'admin', Status is 'approved'
    $role = 'admin';
    $status = 'approved';
    
    $insertStmt = $conn->prepare("INSERT INTO users (full_name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
    $insertStmt->bind_param("sssss", $full_name, $email, $hashed_password, $role, $status);

    if ($insertStmt->execute()) {
        echo "<div style='font-family: sans-serif; padding: 20px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px;'>";
        echo "<h2>✅ Admin Account Created Successfully!</h2>";
        echo "<p><strong>Email:</strong> $email</p>";
        echo "<p><strong>Password:</strong> $plain_password</p>";
        echo "<p><a href='login.php'>Click here to Login</a></p>";
        echo "</div>";
    } else {
        echo "Error creating admin: " . $conn->error;
    }
}
?>