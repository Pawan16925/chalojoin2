<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// 1. Security Check
checkRole('admin');

$message = '';

// 2. Handle Actions (Approve / Reject)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $post_id = intval($_GET['id']);
    $action = $_GET['action'];
    
    $new_status = ($action === 'approve') ? 'approved' : 'rejected';
    
    $stmt = $pdo->prepare("UPDATE opportunities SET status = ? WHERE id = ?");
    if ($stmt->execute([$new_status, $post_id])) {
        $message = "Post has been " . $new_status . ".";
    }
}

// 3. Fetch Pending Opportunities
// We join users (to get organizer name) and categories (to get category name)
$sql = "SELECT o.*, u.full_name as organizer_name, c.name as category_name 
        FROM opportunities o
        JOIN users u ON o.organizer_id = u.id
        JOIN categories c ON o.category_id = c.id
        WHERE o.status = 'pending'
        ORDER BY o.created_at ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$pending_posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Posts - ChaloJoin Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-danger">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">Admin Panel</a>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm">Back to Dashboard</a>
    </div>
</nav>

<div class="container mt-5">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Pending Post Approvals</h3>
        <span class="badge bg-primary fs-6"><?php echo count($pending_posts); ?> Pending</span>
    </div>

    <?php if($message): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-body p-0">
            <?php if(count($pending_posts) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 20%;">Title</th>
                                <th>Organizer</th>
                                <th>Category</th>
                                <th style="width: 30%;">Description Preview</th>
                                <th>Posted On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($pending_posts as $post): ?>
                            <tr>
                                <td class="fw-bold"><?php echo htmlspecialchars($post['title']); ?></td>
                                
                                <td>
                                    <?php echo htmlspecialchars($post['organizer_name']); ?>
                                    <br>
                                    <small class="text-muted">ID: <?php echo $post['organizer_id']; ?></small>
                                </td>
                                
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($post['category_name']); ?></span></td>
                                
                                <td>
                                    <small class="text-muted">
                                        <?php 
                                            // Truncate description for the table view
                                            $desc = htmlspecialchars($post['description']);
                                            echo strlen($desc) > 80 ? substr($desc, 0, 80) . "..." : $desc; 
                                        ?>
                                    </small>
                                </td>

                                <td><?php echo date('M d', strtotime($post['created_at'])); ?></td>

                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="verify_posts.php?action=approve&id=<?php echo $post['id']; ?>" 
                                           class="btn btn-success btn-sm"
                                           onclick="return confirm('Make this post live?');">
                                            Approve
                                        </a>
                                        <a href="verify_posts.php?action=reject&id=<?php echo $post['id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Reject this post?');">
                                            Reject
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-5 text-center text-muted">
                    <h5>All caught up! 🎉</h5>
                    <p>There are no pending posts to review.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

</body>
</html>