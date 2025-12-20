<?php
session_start();
require_once '../config/db.php';

// If already logged in as admin, send to dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // 1. Fetch user by email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // 2. Verify Password
        if ($user && password_verify($password, $user['password'])) {
            
            // 3. STRICT CHECK: Is this user actually an Admin?
            if ($user['role'] !== 'admin') {
                $error = "Access Denied. You are not an administrator.";
            } else {
                // 4. Success - Set Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];

                // 5. Redirect to Admin Dashboard
                header("Location: dashboard.php");
                exit();
            }

        } else {
            $error = "Invalid admin credentials.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ChaloJoin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #212529; color: #fff; }
        .card { border: none; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center" style="height: 100vh;">

<div class="col-md-4">
    <div class="card shadow-lg">
        <div class="card-header bg-danger text-white text-center">
            <h4>Admin Portal</h4>
        </div>
        <div class="card-body text-dark">
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Admin Email</label>
                    <input type="email" name="email" class="form-control" placeholder="admin@chalojoin.com" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-danger w-100">Access Dashboard</button>
            </form>
        </div>
        <div class="card-footer text-center bg-light">
            <small class="text-muted">Not an admin? <a href="../login.php">Go to Main Site</a></small>
        </div>
    </div>
</div>

</body>
</html>