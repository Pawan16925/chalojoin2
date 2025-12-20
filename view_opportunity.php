<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

session_start();

// 1. Get Opportunity ID
if (!isset($_GET['id'])) {
    header("Location: student/dashboard.php");
    exit();
}

$opp_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'] ?? 0;
$user_role = $_SESSION['role'] ?? '';
$message = '';
$error = '';

// 2. Handle Application
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply_now'])) {
    if ($user_role !== 'student') {
        $error = "You must be logged in as a Student to apply.";
    } else {
        $cover_letter = trim($_POST['cover_letter']);
        $check_stmt = $pdo->prepare("SELECT id FROM applications WHERE student_id = ? AND opportunity_id = ?");
        $check_stmt->execute([$user_id, $opp_id]);
        
        if ($check_stmt->rowCount() > 0) {
            $error = "You have already applied for this opportunity.";
        } else {
            $sql = "INSERT INTO applications (student_id, opportunity_id, cover_letter, status) VALUES (?, ?, ?, 'applied')";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$user_id, $opp_id, $cover_letter])) {
                $message = "Application submitted successfully!";
            } else {
                $error = "Failed to submit application.";
            }
        }
    }
}

// 3. Fetch Opportunity
// Fetch 'is_verified' for blue tick
$sql = "SELECT o.*, c.name AS category_name, u.full_name AS organizer_name, u.email AS organizer_email, u.profile_image, u.bio as org_bio, u.is_verified
        FROM opportunities o 
        JOIN categories c ON o.category_id = c.id 
        JOIN users u ON o.organizer_id = u.id 
        WHERE o.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$opp_id]);
$post = $stmt->fetch();

if (!$post) die("Opportunity not found.");

// 4. Check application status
$has_applied = false;
if ($user_role === 'student') {
    $chk = $pdo->prepare("SELECT id FROM applications WHERE student_id = ? AND opportunity_id = ?");
    $chk->execute([$user_id, $opp_id]);
    if ($chk->rowCount() > 0) $has_applied = true;
}

// Blue Tick SVG
$blue_tick = '<span class="verified-badge"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22.5 12.0001C22.5 13.3857 21.9427 14.6543 21.0425 15.5866C20.8252 15.8118 20.7308 16.1266 20.7853 16.4335C21.0118 17.708 20.6549 19.0345 19.6644 20.0249C18.674 21.0154 17.3474 21.3723 16.0729 21.1458C15.766 21.0913 15.4513 21.1857 15.226 21.4031C14.2938 22.3032 13.0252 22.8606 11.6395 22.8606C10.2539 22.8606 8.98528 22.3032 8.05307 21.4031C7.82782 21.1857 7.51307 21.0913 7.20619 21.1458C5.93171 21.3723 4.60515 21.0154 3.61469 20.0249C2.62423 19.0345 2.26732 17.708 2.49383 16.4335C2.54833 16.1266 2.45395 15.8118 2.23658 15.5866C1.33642 14.6543 0.779053 13.3857 0.779053 12.0001C0.779053 10.6144 1.33642 9.34582 2.23658 8.41357C2.45395 8.18833 2.54833 7.87358 2.49383 7.56667C2.26732 6.29215 2.62423 4.96564 3.61469 3.97518C4.60515 2.98472 5.93171 2.62781 7.20619 2.85433C7.51307 2.90883 7.82782 2.81445 8.05307 2.59708C8.98528 1.69692 10.2539 1.13953 11.6395 1.13953C13.0252 1.13953 14.2938 1.69692 15.226 2.59708C15.4513 2.81445 15.766 2.90883 16.0729 2.85433C17.3474 2.62781 18.674 2.98472 19.6644 3.97518C20.6549 4.96564 21.0118 6.29215 20.7853 7.56667C20.7308 7.87358 20.8252 8.18833 21.0425 8.41357C21.9427 9.34582 22.5 10.6144 22.5 12.0001Z" fill="#0095F6"/><path d="M10.0957 16.5925L6.3421 12.839C6.0142 12.5111 6.0142 11.9794 6.3421 11.6515L7.20573 10.7879C7.53363 10.4599 8.06528 10.4599 8.39318 10.7879L10.6894 13.0841L15.9333 7.84024C16.2612 7.51234 16.7928 7.51234 17.1207 7.84024L17.9844 8.70387C18.3123 9.03177 18.3123 9.56342 17.9844 9.89132L11.2831 16.5925C10.9552 16.9204 10.4236 16.9204 10.0957 16.5925Z" fill="white"/></svg></span>';

// Organizer Avatar
$org_avatar = $post['profile_image'] ? "uploads/avatars/" . $post['profile_image'] : "https://via.placeholder.com/150";

// Back Link logic
$back_link = ($user_role == 'student') ? "student/dashboard.php" : (($user_role == 'organizer') ? "organizer/dashboard.php" : "index.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo htmlspecialchars($post['title']); ?> - ChaloJoin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
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
            padding-bottom: 90px; /* Space for mobile bottom bar */
        }

        /* --- NAVBAR --- */
        .navbar {
            background: #fff;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            height: 60px;
        }
        .navbar-brand { font-weight: 800; color: var(--primary-color) !important; font-size: 1.4rem; }

        /* --- LAYOUT --- */
        .main-container { margin-top: 80px; }

        /* --- HEADER CARD --- */
        .header-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 24px;
            margin-bottom: 20px;
        }
        
        .org-logo-large {
            width: 72px; height: 72px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .verified-badge { margin-left: 5px; display: inline-flex; vertical-align: middle; }
        
        /* --- CONTENT SECTION --- */
        .content-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 24px;
            margin-bottom: 20px;
        }
        .section-title { font-weight: 700; font-size: 1.1rem; margin-bottom: 12px; }
        .description-text { line-height: 1.7; color: #333; white-space: pre-line; }

        /* --- SIDEBAR WIDGETS --- */
        .widget-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-row { display: flex; align-items: center; margin-bottom: 15px; }
        .info-icon {
            width: 40px; height: 40px;
            border-radius: 50%;
            background: #f0f2f5;
            display: flex; align-items: center; justify-content: center;
            color: var(--primary-color);
            margin-right: 15px;
        }
        .info-label { font-size: 0.85rem; color: var(--text-muted); display: block; }
        .info-val { font-weight: 600; font-size: 0.95rem; }

        /* --- MOBILE BOTTOM ACTION BAR --- */
        .mobile-action-bar {
            position: fixed;
            bottom: 0; left: 0; width: 100%;
            background: #fff;
            padding: 12px 20px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
            display: none; /* Hidden on desktop */
            z-index: 1040;
            display: flex;
            gap: 10px;
        }
        
        .btn-apply {
            background-color: var(--primary-color);
            color: #fff;
            font-weight: 600;
            border: none;
            padding: 12px;
            border-radius: 8px;
            flex-grow: 1;
            transition: 0.2s;
        }
        .btn-apply:hover { background-color: #1565c0; color: #fff; }

        .btn-save {
            background-color: #f0f2f5;
            color: var(--text-dark);
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 991px) {
            .main-container { margin-top: 70px; }
            .desktop-sidebar { display: none; }
            .mobile-action-bar { display: flex; } /* Show sticky footer */
            .navbar-nav { display: none; } /* Hide menu items in simple header */
        }
        
        @media (min-width: 992px) {
            .mobile-action-bar { display: none !important; } /* Hide sticky footer on desktop */
        }
    </style>
</head>
<body>

<!-- TOP NAVBAR -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container d-flex align-items-center justify-content-between">
        <a class="navbar-brand d-flex align-items-center" href="<?php echo $back_link; ?>">
            <i class="fas fa-arrow-left me-3 text-dark d-lg-none" style="font-size: 1.1rem;"></i> ChaloJoin
        </a>
        
        <ul class="navbar-nav ms-auto d-none d-lg-flex">
            <li class="nav-item"><a href="<?php echo $back_link; ?>" class="btn btn-outline-secondary rounded-pill btn-sm">Back</a></li>
        </ul>
    </div>
</nav>

<!-- MAIN CONTENT -->
<div class="container main-container">
    <div class="row">
        
        <!-- LEFT COLUMN: Job Details -->
        <div class="col-lg-8">
            
            <?php if($message): ?>
                <div class="alert alert-success border-0 shadow-sm rounded-3"><i class="fas fa-check-circle me-2"></i> <?php echo $message; ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="alert alert-danger border-0 shadow-sm rounded-3"><i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Header Card -->
            <div class="header-card d-flex align-items-center">
                <img src="<?php echo $org_avatar; ?>" class="org-logo-large flex-shrink-0">
                <div class="ms-3">
                    <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($post['title']); ?></h3>
                    <div class="d-flex align-items-center text-muted">
                        <span><?php echo htmlspecialchars($post['organizer_name']); ?></span>
                        <?php if($post['is_verified'] == 1) echo $blue_tick; ?>
                        <span class="mx-2">•</span>
                        <span><?php echo htmlspecialchars($post['category_name']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Mobile Only Info Widget (Visible only on small screens) -->
            <div class="widget-card d-lg-none">
                <div class="row">
                    <div class="col-6 mb-3">
                        <small class="text-muted d-block">Location</small>
                        <strong><?php echo htmlspecialchars($post['location']); ?></strong>
                    </div>
                    <div class="col-6 mb-3">
                        <small class="text-muted d-block">Type</small>
                        <strong><?php echo ucfirst($post['type']); ?></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Deadline</small>
                        <strong class="text-danger"><?php echo date('d M Y', strtotime($post['deadline'])); ?></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Mode</small>
                        <strong><?php echo ucfirst($post['mode']); ?></strong>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="content-card">
                <h5 class="section-title">About this opportunity</h5>
                <p class="description-text"><?php echo htmlspecialchars($post['description']); ?></p>
            </div>

            <!-- Application Form (Desktop Inline) -->
            <?php if(!$has_applied && $user_role == 'student' && !$message): ?>
            <div class="content-card d-none d-lg-block">
                <h5 class="section-title">Apply Now</h5>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Cover Letter / Note</label>
                        <textarea name="cover_letter" class="form-control bg-light border-0" rows="4" placeholder="Tell us why you're a good fit..." required></textarea>
                    </div>
                    <button type="submit" name="apply_now" class="btn btn-primary fw-bold px-4 py-2">Submit Application</button>
                </form>
            </div>
            <?php endif; ?>

        </div>

        <!-- RIGHT COLUMN: Sidebar (Desktop Only) -->
        <div class="col-lg-4 desktop-sidebar">
            
            <!-- Quick Stats Widget -->
            <div class="widget-card">
                <h6 class="fw-bold mb-3">Job Overview</h6>
                
                <div class="info-row">
                    <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div>
                        <span class="info-label">Location</span>
                        <span class="info-val"><?php echo htmlspecialchars($post['location']); ?></span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon"><i class="fas fa-laptop-house"></i></div>
                    <div>
                        <span class="info-label">Mode</span>
                        <span class="info-val"><?php echo ucfirst($post['mode']); ?></span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon"><i class="far fa-calendar-alt"></i></div>
                    <div>
                        <span class="info-label">Deadline</span>
                        <span class="info-val text-danger"><?php echo date('d M Y', strtotime($post['deadline'])); ?></span>
                    </div>
                </div>

                <hr>

                <!-- Action Buttons (Desktop) -->
                <?php if($user_role == 'student'): ?>
                    <?php if($has_applied): ?>
                        <button class="btn btn-success w-100 fw-bold mb-2" disabled><i class="fas fa-check"></i> Applied</button>
                        <a href="student/my_applications.php" class="btn btn-outline-secondary w-100">View Status</a>
                    <?php else: ?>
                         <!-- On desktop, the form is in the main column, so we might just put a save button here or scroll link -->
                         <a href="student/save_action.php?id=<?php echo $post['id']; ?>" class="btn btn-outline-primary w-100 fw-bold">
                            <i class="far fa-bookmark me-2"></i> Save for later
                         </a>
                    <?php endif; ?>
                <?php elseif($user_role == 'organizer'): ?>
                    <div class="alert alert-info small">You posted this opportunity (or are viewing as organizer).</div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary w-100">Login to Apply</a>
                <?php endif; ?>

            </div>

            <!-- Organizer Bio Widget -->
            <div class="widget-card">
                <h6 class="fw-bold mb-3">About the Organizer</h6>
                <div class="d-flex align-items-center mb-3">
                    <img src="<?php echo $org_avatar; ?>" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;" class="me-2">
                    <div>
                        <div class="fw-bold"><?php echo htmlspecialchars($post['organizer_name']); ?></div>
                        <small class="text-muted"><?php echo htmlspecialchars($post['organizer_email']); ?></small>
                    </div>
                </div>
                <p class="small text-muted mb-0">
                    <?php echo $post['org_bio'] ? htmlspecialchars($post['org_bio']) : "No bio available."; ?>
                </p>
            </div>

        </div>

    </div>
</div>

<!-- MOBILE BOTTOM ACTION BAR (Sticky Footer) -->
<?php if($user_role == 'student' && !$has_applied): ?>
<div class="mobile-action-bar">
    <a href="student/save_action.php?id=<?php echo $post['id']; ?>" class="btn-save">
        <i class="far fa-bookmark"></i>
    </a>
    <!-- Triggers a modal for application on mobile -->
    <button type="button" class="btn-apply" data-bs-toggle="modal" data-bs-target="#applyModal">
        Apply Now
    </button>
</div>

<!-- Mobile Application Modal -->
<div class="modal fade" id="applyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Apply for <?php echo htmlspecialchars($post['title']); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Cover Letter</label>
                        <textarea name="cover_letter" class="form-control bg-light border-0" rows="5" placeholder="Why are you a good fit?" required></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="apply_now" class="btn btn-primary btn-lg fw-bold">Submit Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if($user_role == 'student' && $has_applied): ?>
<div class="mobile-action-bar justify-content-center">
    <button class="btn btn-success w-100 fw-bold" disabled><i class="fas fa-check me-2"></i> Application Sent</button>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>