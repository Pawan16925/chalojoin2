<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// 1. Security Check
checkRole('organizer');

$organizer_id = $_SESSION['user_id'];
$organizer_name = $_SESSION['full_name'];

// 2. Fetch Stats
// A. Total Posts
$stmt = $pdo->prepare("SELECT COUNT(*) FROM opportunities WHERE organizer_id = ?");
$stmt->execute([$organizer_id]);
$total_posts = $stmt->fetchColumn();

// B. Total Applicants (Complex Join: Count applications linked to opportunities created by this organizer)
$sql_apps = "SELECT COUNT(*) FROM applications a 
             JOIN opportunities o ON a.opportunity_id = o.id 
             WHERE o.organizer_id = ?";
$stmt = $pdo->prepare($sql_apps);
$stmt->execute([$organizer_id]);
$total_applicants = $stmt->fetchColumn();

// 3. Fetch Recent Posts
$stmt = $pdo->prepare("SELECT * FROM opportunities WHERE organizer_id = ? ORDER BY created_at DESC");
$stmt->execute([$organizer_id]);
$my_posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Dashboard - ChaloJoin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <span class="navbar-brand mb-0 h1">ChaloJoin <small class="text-warning">Organizer</small></span>
        <div class="d-flex align-items-center">
            <span class="text-white me-3">Hello, <?php echo htmlspecialchars($organizer_name); ?></span>
            <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo $total_posts; ?></h3>
                        <div>Active Posts</div>
                    </div>
                    <i class="fas fa-briefcase fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo $total_applicants; ?></h3>
                        <div>Total Applicants</div>
                    </div>
                    <i class="fas fa-users fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">My Opportunities</h4>
        <a href="post_opportunity.php" class="btn btn-warning fw-bold">
            <i class="fas fa-plus me-1"></i> Post New Opportunity
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body p-0">
            <?php if(count($my_posts) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                th>Title</th>
                                <th>Type</th>
                                <th>Posted Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($my_posts as $post): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($post['title']); ?></div>
                                    <small class="text-muted">Deadline: <?php echo date('M d, Y', strtotime($post['deadline'])); ?></small>
                                </td>
                                <td><span class="badge bg-secondary"><?php echo ucfirst($post['type']); ?></span></td>
                                <td><?php echo date('d M Y', strtotime($post['created_at'])); ?></td>
                                <td>
                                    <?php if($post['status'] == 'approved'): ?>
                                        <span class="badge bg-success">Live</span>
                                    <?php elseif($post['status'] == 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Pending Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Rejected</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="view_applicants.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                        View Applicants
                                    </a>
                                    <a href="#" class="btn btn-sm btn-light border text-danger" onclick="alert('Delete feature coming soon!')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-5 text-center text-muted">
                    <h5>You haven't posted any opportunities yet.</h5>
                    <p>Click the "Post New Opportunity" button to get started.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

</body>
</html>