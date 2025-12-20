<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// 1. Security Check
checkRole('student');

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['full_name'];

// 2. Fetch User Profile Image (for nav)
$stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
$stmt->execute([$student_id]);
$current_user_img = $stmt->fetchColumn();
$my_avatar = $current_user_img ? "../uploads/avatars/" . $current_user_img : "https://via.placeholder.com/150";

// 3. Fetch Applications
// Joining 'opportunities' and 'users' (organizer) to get details
// Also fetching 'is_verified' for the Blue Tick
$sql = "SELECT a.*, o.title, o.id as opportunity_id, u.full_name as organizer_name, u.profile_image as org_image, u.is_verified 
        FROM applications a 
        JOIN opportunities o ON a.opportunity_id = o.id 
        JOIN users u ON o.organizer_id = u.id 
        WHERE a.student_id = ? 
        ORDER BY a.applied_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$student_id]);
$my_apps = $stmt->fetchAll();

// --- BLUE TICK SVG ---
$blue_tick = '<span class="verified-badge"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22.5 12.0001C22.5 13.3857 21.9427 14.6543 21.0425 15.5866C20.8252 15.8118 20.7308 16.1266 20.7853 16.4335C21.0118 17.708 20.6549 19.0345 19.6644 20.0249C18.674 21.0154 17.3474 21.3723 16.0729 21.1458C15.766 21.0913 15.4513 21.1857 15.226 21.4031C14.2938 22.3032 13.0252 22.8606 11.6395 22.8606C10.2539 22.8606 8.98528 22.3032 8.05307 21.4031C7.82782 21.1857 7.51307 21.0913 7.20619 21.1458C5.93171 21.3723 4.60515 21.0154 3.61469 20.0249C2.62423 19.0345 2.26732 17.708 2.49383 16.4335C2.54833 16.1266 2.45395 15.8118 2.23658 15.5866C1.33642 14.6543 0.779053 13.3857 0.779053 12.0001C0.779053 10.6144 1.33642 9.34582 2.23658 8.41357C2.45395 8.18833 2.54833 7.87358 2.49383 7.56667C2.26732 6.29215 2.62423 4.96564 3.61469 3.97518C4.60515 2.98472 5.93171 2.62781 7.20619 2.85433C7.51307 2.90883 7.82782 2.81445 8.05307 2.59708C8.98528 1.69692 10.2539 1.13953 11.6395 1.13953C13.0252 1.13953 14.2938 1.69692 15.226 2.59708C15.4513 2.81445 15.766 2.90883 16.0729 2.85433C17.3474 2.62781 18.674 2.98472 19.6644 3.97518C20.6549 4.96564 21.0118 6.29215 20.7853 7.56667C20.7308 7.87358 20.8252 8.18833 21.0425 8.41357C21.9427 9.34582 22.5 10.6144 22.5 12.0001Z" fill="#0095F6"/><path d="M10.0957 16.5925L6.3421 12.839C6.0142 12.5111 6.0142 11.9794 6.3421 11.6515L7.20573 10.7879C7.53363 10.4599 8.06528 10.4599 8.39318 10.7879L10.6894 13.0841L15.9333 7.84024C16.2612 7.51234 16.7928 7.51234 17.1207 7.84024L17.9844 8.70387C18.3123 9.03177 18.3123 9.56342 17.9844 9.89132L11.2831 16.5925C10.9552 16.9204 10.4236 16.9204 10.0957 16.5925Z" fill="white"/></svg></span>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>My Applications - ChaloJoin</title>
    <!-- Bootstrap 5 -->
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
            padding-bottom: 70px;
        }

        /* --- NAVBAR --- */
        .navbar {
            background: #fff;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            height: 60px;
            z-index: 1020;
        }
        .navbar-brand {
            font-weight: 800;
            color: var(--primary-color) !important;
            font-size: 1.4rem;
        }

        /* --- LAYOUT --- */
        .main-container { margin-top: 80px; }
        
        /* --- APPLICATION CARD --- */
        .app-card {
            background: #fff;
            border-radius: 12px;
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 16px;
            padding: 16px;
            transition: transform 0.2s;
        }
        .app-card:active { transform: scale(0.99); }
        
        .org-logo {
            width: 50px; height: 50px;
            border-radius: 10px; /* Rounded square looks more corporate */
            object-fit: cover;
            border: 1px solid #eee;
        }

        .job-title { font-weight: 700; font-size: 1rem; margin-bottom: 2px; }
        .org-name { font-size: 0.9rem; color: var(--text-muted); }
        .verified-badge { margin-left: 4px; display: inline-flex; vertical-align: middle; }

        .meta-info {
            font-size: 0.8rem;
            color: #888;
            margin-top: 8px;
            display: flex;
            gap: 12px;
        }

        /* Status Badges */
        .status-badge {
            font-size: 0.75rem;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-selected { background: #e6f7e9; color: #2e7d32; } /* Green */
        .status-rejected { background: #fbe9e7; color: #c62828; } /* Red */
        .status-applied  { background: #fff8e1; color: #f57f17; } /* Orange/Yellow */

        .view-btn {
            background: #f0f2f5;
            color: var(--text-dark);
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
        }
        .view-btn:hover { background: #e4e6eb; color: var(--text-dark); }

        /* --- BOTTOM NAV --- */
        .bottom-nav {
            position: fixed;
            bottom: 0; left: 0; width: 100%;
            height: 60px;
            background: #fff;
            border-top: 1px solid #eee;
            display: none;
            justify-content: space-around;
            align-items: center;
            z-index: 1030;
        }
        .bottom-nav-item {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.75rem;
            text-decoration: none;
            width: 25%;
        }
        .bottom-nav-item i { font-size: 1.2rem; display: block; margin-bottom: 2px; }
        .bottom-nav-item.active { color: var(--primary-color); }

        @media (max-width: 991px) {
            .sidebar-left { display: none; }
            .bottom-nav { display: flex; }
            .navbar .navbar-nav { display: none; }
            .main-container { margin-top: 70px; }
        }
    </style>
</head>
<body>

<!-- TOP NAVBAR -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container d-flex align-items-center justify-content-between">
        <a class="navbar-brand" href="dashboard.php">ChaloJoin</a>
        
        <ul class="navbar-nav ms-auto d-none d-lg-flex align-items-center">
            <li class="nav-item">
                <span class="fw-bold text-dark me-3"><?php echo htmlspecialchars($student_name); ?></span>
            </li>
            <li class="nav-item">
                <a href="../logout.php" class="btn btn-sm btn-outline-danger rounded-pill">Logout</a>
            </li>
        </ul>
    </div>
</nav>

<!-- MAIN CONTENT -->
<div class="container main-container">
    <div class="row">
        
        <!-- LEFT SIDEBAR (Desktop Only) -->
        <div class="col-lg-3 sidebar-left">
            <div class="sticky-top" style="top: 80px;">
                <div class="list-group shadow-sm rounded-3 border-0">
                    <a href="dashboard.php" class="list-group-item list-group-item-action border-0"><i class="fas fa-home me-2"></i> Feed</a>
                    <a href="my_applications.php" class="list-group-item list-group-item-action border-0 active fw-bold"><i class="fas fa-briefcase me-2"></i> Applications</a>
                    <a href="saved_jobs.php" class="list-group-item list-group-item-action border-0"><i class="fas fa-bookmark me-2"></i> Saved</a>
                    <a href="profile.php" class="list-group-item list-group-item-action border-0"><i class="fas fa-user-cog me-2"></i> Settings</a>
                </div>
            </div>
        </div>

        <!-- CENTER CONTENT -->
        <div class="col-lg-8 col-md-12">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0">My Applications</h4>
                <span class="badge bg-light text-dark border"><?php echo count($my_apps); ?> Applied</span>
            </div>

            <?php if(count($my_apps) > 0): ?>
                <?php foreach($my_apps as $app): ?>
                <div class="app-card d-flex align-items-start">
                    
                    <!-- Company Logo -->
                    <img src="../uploads/avatars/<?php echo $app['org_image'] ?: 'default_avatar.png'; ?>" class="org-logo flex-shrink-0">

                    <div class="ms-3 flex-grow-1">
                        <!-- Header Line -->
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="job-title"><?php echo htmlspecialchars($app['title']); ?></h5>
                                <div class="org-name">
                                    <?php echo htmlspecialchars($app['organizer_name']); ?>
                                    <?php if($app['is_verified'] == 1) echo $blue_tick; ?>
                                </div>
                            </div>
                            
                            <!-- Status Badge -->
                            <?php 
                                $statusClass = 'status-applied';
                                $icon = '<i class="fas fa-clock me-1"></i>';
                                if($app['status'] == 'selected') { 
                                    $statusClass = 'status-selected'; 
                                    $icon = '<i class="fas fa-check-circle me-1"></i>';
                                }
                                elseif($app['status'] == 'rejected') { 
                                    $statusClass = 'status-rejected'; 
                                    $icon = '<i class="fas fa-times-circle me-1"></i>';
                                }
                            ?>
                            <span class="status-badge <?php echo $statusClass; ?>">
                                <?php echo $icon . ucfirst($app['status']); ?>
                            </span>
                        </div>

                        <!-- Meta Info -->
                        <div class="meta-info">
                            <span><i class="far fa-calendar-alt"></i> Applied: <?php echo date('M d, Y', strtotime($app['applied_at'])); ?></span>
                        </div>
                        
                        <!-- Action Footer -->
                        <div class="mt-3">
                            <a href="../view_opportunity.php?id=<?php echo $app['opportunity_id']; ?>" class="view-btn">
                                View Job Details
                            </a>
                        </div>
                    </div>

                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Empty State -->
                <div class="text-center py-5">
                    <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-box-4344458-3613886.png" width="150" alt="No apps">
                    <h5 class="mt-3 fw-bold">No applications yet</h5>
                    <p class="text-muted">Start applying to internships and events!</p>
                    <a href="dashboard.php" class="btn btn-primary rounded-pill px-4">Find Opportunities</a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- BOTTOM NAV -->
<div class="bottom-nav">
    <a href="dashboard.php" class="bottom-nav-item">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <a href="my_applications.php" class="bottom-nav-item active">
        <i class="fas fa-briefcase"></i>
        <span>Jobs</span>
    </a>
    <a href="saved_jobs.php" class="bottom-nav-item">
        <i class="fas fa-bookmark"></i>
        <span>Saved</span>
    </a>
    <a href="profile.php" class="bottom-nav-item">
        <i class="fas fa-user"></i>
        <span>Profile</span>
    </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>