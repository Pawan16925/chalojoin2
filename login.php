<?php
require_once 'config/db.php';
require_once 'includes/functions.php'; // For the redirectBasedOnRole function

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter email and password.";
    } else {
        // 1. Fetch user by email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // 2. Verify User and Password
        if ($user && password_verify($password, $user['password'])) {
            
            // 3. Check Account Status
            if ($user['status'] === 'pending') {
                $error = "Your account is pending approval by the Admin.";
            } elseif ($user['status'] === 'rejected') {
                $error = "Your account has been rejected. Contact support.";
            } else {
                // 4. Success! Set Session Variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];

                // 5. Redirect based on role
                redirectBasedOnRole($user['role']);
            }

        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - ChaloJoin</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #1877f2;
            --bg-color: #f0f2f5;
            --text-dark: #050505;
            --text-muted: #65676b;
        }

        body {
            background-color: var(--bg-color);
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: none;
            padding: 30px;
            width: 100%;
            max-width: 450px;
        }

        .brand-logo {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary-color);
            text-decoration: none;
            letter-spacing: -1px;
            display: block;
            text-align: center;
            margin-bottom: 10px;
        }

        .page-title {
            text-align: center;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 30px;
            font-size: 1.1rem;
        }

        .form-control {
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 1rem;
            background-color: #fcfcfc;
        }
        .form-control:focus {
            background-color: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(24, 119, 242, 0.15);
        }

        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #333;
            margin-bottom: 6px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            width: 100%;
            transition: background 0.2s;
        }
        .btn-primary:hover {
            background-color: #1565c0;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 0.95rem;
            color: var(--text-muted);
        }
        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        .register-link a:hover { text-decoration: underline; }

        .input-group-text {
            background: #f8f9fa;
            border-color: #ddd;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 d-flex justify-content-center">
            
            <div class="auth-card">
                <a href="index.php" class="brand-logo">ChaloJoin</a>
                <h5 class="page-title">Welcome back!</h5>

                <?php if($error): ?>
                    <div class="alert alert-danger border-0 shadow-sm rounded-3 py-2 text-center small">
                        <i class="fas fa-exclamation-circle me-1"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Log In</button>
                </form>

                <div class="register-link">
                    Don't have an account? <a href="register.php">Sign Up</a>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>