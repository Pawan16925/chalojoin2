<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// 1. Security Check
checkRole('organizer');

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$opp_id = intval($_GET['id']);
$organizer_id = $_SESSION['user_id'];
$message = '';

// 2. Verify Ownership (Security: Does this post belong to this organizer?)
$stmt = $pdo->prepare("SELECT title FROM opportunities WHERE id = ? AND organizer_id = ?");
$stmt->execute([$opp_id, $organizer_id]);
$post = $stmt->fetch();

if (!$post) {
    die("Access Denied: You cannot view applicants for this opportunity.");
}

// 3. Handle Status Updates (Select / Reject Candidate)
if (isset($_GET['action']) && isset($_GET['app_id'])) {
    $app_id = intval($_GET['app_id']);
    $action = $_GET['action'];
    $new_status = ($action === 'select') ? 'selected' : 'rejected';

    $update_stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
    if ($update_stmt->execute([$new_status, $app_id])) {
        $message = "Candidate status updated to " . ucfirst($new_status) . ".";
    }
}

// 4. Fetch Applicants
// Join 'applications' table with 'users' table to get student details
$sql = "SELECT a.*, u.full_name, u.email, u.phone, u.resume_file, u.profile_image, u.skills, u.linkedin_url 
        FROM applications a 
        JOIN users u ON a.student_id = u.id 
        WHERE a.opportunity_id = ? 
        ORDER BY a.applied_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$opp_id]);
$applicants = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Applicants for <?php echo htmlspecialchars($post['title']); ?> - ChaloJoin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <span class="navbar-brand mb-0 h1">ChaloJoin <small class="text-warning">Organizer</small></span>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm">Back to Dashboard</a>
    </div>
</nav>

<div class="container mt-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Applicants for: <span class="text-primary"><?php echo htmlspecialchars($post['title']); ?></span></h4>
        <span class="badge bg-secondary fs-6"><?php echo count($applicants); ?> Applications</span>
    </div>

    <?php if($message): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(count($applicants) > 0): ?>
        <div class="row">
            <?php foreach($applicants as $app): ?>
            <div class="col-md-12 mb-3">
                <div class="card shadow-sm <?php echo ($app['status'] == 'selected') ? 'border-success border-2' : ''; ?>">
                    <div class="card-body">
                        <div class="row align-items-center">
                            
                            <div class="col-md-1 text-center">
                                <img src="../uploads/avatars/<?php echo $app['profile_image'] ?: 'default_avatar.png'; ?>" 
                                     class="rounded-circle" width="60" height="60" style="object-fit:cover;">
                            </div>

                            <div class="col-md-4">
                                <h5 class="mb-1 fw-bold"><?php echo htmlspecialchars($app['full_name']); ?></h5>
                                <p class="mb-0 text-muted small"><i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($app['email']); ?></p>
                                <p class="mb-0 text-muted small"><i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($app['phone']); ?></p>
                                <?php if($app['linkedin_url']): ?>
                                    <a href="<?php echo htmlspecialchars($app['linkedin_url']); ?>" target="_blank" class="small text-primary">LinkedIn Profile</a>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-4">
                                <strong class="small text-dark">Cover Letter:</strong>
                                <p class="small text-muted fst-italic mb-2">
                                    "<?php echo htmlspecialchars(substr($app['cover_letter'], 0, 100)) . '...'; ?>"
                                </p>
                                <?php if($app['resume_file']): ?>
                                    <a href="../uploads/resumes/<?php echo $app['resume_file']; ?>" target="_blank" class="btn btn-sm btn-outline-dark">
                                        <i class="fas fa-file-pdf me-1"></i> Download Resume
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted">No Resume</span>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-3 text-end">
                                <div class="mb-2">
                                    Status: 
                                    <?php if($app['status'] == 'applied'): ?>
                                        <span class="badge bg-warning text-dark">New</span>
                                    <?php elseif($app['status'] == 'selected'): ?>
                                        <span class="badge bg-success">Selected</span>
                                    <?php elseif($app['status'] == 'rejected'): ?>
                                        <span class="badge bg-danger">Rejected</span>
                                    <?php endif; ?>
                                </div>

                                <div class="btn-group">
                                    <a href="view_applicants.php?id=<?php echo $opp_id; ?>&app_id=<?php echo $app['id']; ?>&action=select" 
                                       class="btn btn-success btn-sm" onclick="return confirm('Select this candidate?');">
                                        <i class="fas fa-check"></i> Select
                                    </a>
                                    <a href="view_applicants.php?id=<?php echo $opp_id; ?>&app_id=<?php echo $app['id']; ?>&action=reject" 
                                       class="btn btn-outline-danger btn-sm" onclick="return confirm('Reject this candidate?');">
                                        <i class="fas fa-times"></i> Reject
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center py-5">
            <h4>No applications yet.</h4>
            <p>Wait for students to discover your opportunity!</p>
        </div>
    <?php endif; ?>

</div>

</body>
</html>