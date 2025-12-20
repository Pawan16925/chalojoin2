<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

checkRole('admin');

// Fetch all Organizers
$stmt = $pdo->query("SELECT * FROM users WHERE role='organizer' ORDER BY created_at DESC");
$organizers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Organizers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-danger mb-4">
    <div class="container">
        <span class="navbar-brand">Admin Control</span>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm">Back</a>
    </div>
</nav>

<div class="container">
    <h3>Manage Organizers & Verification</h3>
    <div class="card shadow">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Organizer Name</th>
                        <th>Email</th>
                        <th>Verified?</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($organizers as $org): ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($org['full_name']); ?>
                            <?php if($org['is_verified'] == 1): ?>
                                <i class="fas fa-check-circle text-primary" title="Verified"></i>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($org['email']); ?></td>
                        <td>
                            <?php if($org['is_verified'] == 1): ?>
                                <span class="badge bg-primary">Verified</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Unverified</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($org['is_verified'] == 1): ?>
                                <a href="toggle_verification.php?id=<?php echo $org['id']; ?>" class="btn btn-sm btn-outline-danger">
                                    Remove Badge
                                </a>
                            <?php else: ?>
                                <a href="toggle_verification.php?id=<?php echo $org['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-check-circle"></i> Give Blue Tick
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>