<?php
// includes/header.php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChaloJoin - Connect with Opportunities</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="assets/css/style.css"> </head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/index.php">ChaloJoin 🚀</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <?php if(isset($_SESSION['user_id'])): ?>
            <li class="nav-item">
                <span class="nav-link text-white">Hello, <?= htmlspecialchars($_SESSION['full_name']); ?></span>
            </li>
            <li class="nav-item">
                <a class="nav-link btn btn-danger text-white btn-sm ms-2" href="/logout.php">Logout</a>
            </li>
        <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="/login.php">Login</a></li>
            <li class="nav-item"><a class="nav-link" href="/register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4"> ```

---

### **3. Common Footer (`includes/footer.php`)**
Closes the HTML tags opened in the header.

```php
</div> <footer class="bg-dark text-white text-center py-3 mt-5">
    <p>&copy; 2025 ChaloJoin. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>