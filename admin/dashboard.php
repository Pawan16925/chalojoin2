<?php
// admin/dashboard.php
require_once '../config/db.php';
require_once '../includes/functions.php';

// 1. Check if user is logged in AND is an Admin
checkRole('admin');

// 2. Fetch Stats for the Cards
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'");
$total_students = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role='organizer'");
$total_organizers = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status='pending'");
$pending_users = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM opportunities");
$total_opportunities = $stmt->fetchColumn();

// 3. Fetch Pending Users (Organizers waiting for approval)
$stmt = $pdo->query("SELECT * FROM users WHERE status='pending' ORDER BY created_at DESC");
$pending_list = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - ChaloJoin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">ChaloJoin Admin</span>
        <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
</nav>

<div class="container mt-4">
    <div class="row text-center mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $total_students; ?></h5>
                    <p class="card-text">Total Students</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $total_organizers; ?></h5>
                    <p class="card-text">Total Organizers</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $pending_users; ?></h5>
                    <p class="card-text">Pending Approvals</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $total_opportunities; ?></h5>
                    <p class="card-text">Total Opportunities</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">Pending User Approvals</h5>
        </div>
        <div class="card-body">
            <?php if (count($pending_list) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Registered On</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_list as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo ucfirst($user['role']); ?></span></td>
                                <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <a href="approve_users.php?id=<?php echo $user['id']; ?>&action=approve" class="btn btn-success btn-sm" onclick="return confirm('Approve this user?');">Approve</a>
                                    <a href="approve_users.php?id=<?php echo $user['id']; ?>&action=reject" class="btn btn-danger btn-sm" onclick="return confirm('Reject this user?');">Reject</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted text-center">No pending users found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>