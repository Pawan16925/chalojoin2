<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// 1. Security Check
checkRole('admin');

// 2. Helper: Time Ago Function
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year', 'm' => 'month', 'w' => 'week',
        'd' => 'day', 'h' => 'hour', 'i' => 'minute', 's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

// 3. Fetch Real-time Stats
$total_students = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$total_organizers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='organizer'")->fetchColumn();
$pending_users = $pdo->query("SELECT COUNT(*) FROM users WHERE status='pending'")->fetchColumn();
$pending_posts = $pdo->query("SELECT COUNT(*) FROM opportunities WHERE status='pending'")->fetchColumn();

// 4. Fetch Pending Users with sorting
$stmt = $pdo->query("SELECT * FROM users WHERE status='pending' ORDER BY created_at DESC LIMIT 10");
$pending_list = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Admin Dashboard - ChaloJoin</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Vanilla Tilt -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.0/vanilla-tilt.min.js"></script>
    
    <style>
        :root {
            /* Premium Tech Theme */
            --primary-color: #2563eb; 
            --secondary-color: #3b82f6;
            --accent-color: #0ea5e9;
            --bg-color: #f8fafc;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
        }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-color);
            color: var(--text-dark);
            padding-bottom: 90px; /* Space for mobile nav */
        }

        /* Glassmorphism Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            height: 70px;
            z-index: 1020;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        }
        .navbar-brand {
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
        }

        /* Header Section */
        .dashboard-header { margin-top: 100px; margin-bottom: 30px; }
        .welcome-title { font-size: 1.8rem; font-weight: 800; color: var(--text-dark); margin-bottom: 5px; }
        .welcome-subtitle { color: var(--text-muted); font-size: 0.95rem; font-weight: 500; }

        /* 3D Tilt Stats Cards */
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.03), 0 4px 6px -2px rgba(0, 0, 0, 0.01);
            border: 1px solid rgba(255, 255, 255, 0.5);
            height: 100%;
            transition: transform 0.2s;
            transform-style: preserve-3d;
            transform: perspective(1000px);
        }
        
        .stat-icon-wrapper {
            width: 50px; height: 50px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
            transform: translateZ(20px);
        }
        .icon-blue { background: #eff6ff; color: var(--primary-color); }
        .icon-green { background: #ecfdf5; color: var(--success-color); }
        .icon-orange { background: #fffbeb; color: var(--warning-color); }
        .icon-purple { background: #f5f3ff; color: #8b5cf6; }

        .stat-value { font-size: 2.2rem; font-weight: 800; color: var(--text-dark); line-height: 1; margin-bottom: 5px; transform: translateZ(30px); }
        .stat-label { font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; transform: translateZ(20px); }

        /* Section Titles */
        .section-header {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;
        }
        .section-title { font-size: 1.1rem; font-weight: 700; color: var(--text-dark); }

        /* User Approval List */
        .approval-card {
            background: white;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            display: flex; align-items: center; justify-content: space-between;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }
        .approval-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            border-color: #e2e8f0;
        }

        .user-avatar {
            width: 48px; height: 48px;
            border-radius: 12px;
            background: #f1f5f9;
            color: var(--text-muted);
            font-weight: 700; font-size: 1.2rem;
            display: flex; align-items: center; justify-content: center;
            margin-right: 15px;
        }
        
        .user-meta h6 { font-weight: 700; margin: 0; font-size: 0.95rem; color: var(--text-dark); }
        .user-meta span { font-size: 0.8rem; color: var(--text-muted); font-weight: 500; }
        .badge-role { 
            font-size: 0.7rem; padding: 2px 8px; border-radius: 6px; 
            background: #eff6ff; color: var(--primary-color); font-weight: 600; margin-left: 5px; text-transform: capitalize;
        }

        .action-group { display: flex; gap: 8px; }
        .btn-icon {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            border: none; cursor: pointer; transition: 0.2s;
        }
        .btn-approve { background: #ecfdf5; color: var(--success-color); }
        .btn-approve:hover { background: var(--success-color); color: white; }
        .btn-reject { background: #fef2f2; color: var(--danger-color); }
        .btn-reject:hover { background: var(--danger-color); color: white; }

        /* Mobile Bottom Nav */
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 70px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(0,0,0,0.05);
            display: none; justify-content: space-around; align-items: center; z-index: 1030;
            padding-bottom: 5px;
        }
        .nav-item-mobile {
            text-align: center; color: var(--text-muted); text-decoration: none;
            font-size: 0.7rem; font-weight: 500; width: 25%; transition: 0.2s;
        }
        .nav-item-mobile i { font-size: 1.3rem; display: block; margin-bottom: 4px; }
        .nav-item-mobile.active { color: var(--primary-color); }

        /* Quick Actions (Desktop Sidebar) */
        .quick-actions { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .qa-link {
            display: flex; align-items: center; padding: 12px;
            color: var(--text-dark); text-decoration: none; font-weight: 600;
            border-radius: 12px; transition: 0.2s; margin-bottom: 8px;
        }
        .qa-link:hover { background: #f8fafc; color: var(--primary-color); transform: translateX(5px); }
        .qa-icon { width: 30px; text-align: center; margin-right: 10px; color: var(--text-muted); }
        .qa-link:hover .qa-icon { color: var(--primary-color); }

        /* Animations */
        .fade-in-up { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; transform: translateY(20px); }
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        
        @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 991px) {
            .bottom-nav { display: flex; }
            .desktop-sidebar { display: none; }
            .navbar-nav { display: none; }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-layer-group me-2"></i>Admin Panel
        </a>
        <div class="d-none d-lg-block">
            <span class="text-muted small me-3"><?php echo date('l, F j, Y'); ?></span>
            <a href="../logout.php" class="btn btn-sm btn-danger rounded-pill px-4 fw-bold shadow-sm">Logout</a>
        </div>
    </div>
</nav>

<!-- Header -->
<div class="container dashboard-header">
    <div class="row">
        <div class="col-12">
            <h1 class="welcome-title fade-in-up">Overview</h1>
            <p class="welcome-subtitle fade-in-up delay-1">Here's what's happening on ChaloJoin today.</p>
        </div>
    </div>
</div>

<div class="container">
    <div class="row g-4">
        
        <!-- Left Column: Stats & Content -->
        <div class="col-lg-8">
            
            <!-- Stats Grid -->
            <div class="row g-3 mb-5">
                <div class="col-6 col-md-6 fade-in-up delay-1">
                    <div class="stat-card" data-tilt data-tilt-glare data-tilt-max-glare="0.2" data-tilt-scale="1.02">
                        <div class="stat-icon-wrapper icon-blue"><i class="fas fa-user-graduate"></i></div>
                        <div class="stat-value"><?php echo $total_students; ?></div>
                        <div class="stat-label">Students</div>
                    </div>
                </div>
                <div class="col-6 col-md-6 fade-in-up delay-2">
                    <div class="stat-card" data-tilt data-tilt-glare data-tilt-max-glare="0.2" data-tilt-scale="1.02">
                        <div class="stat-icon-wrapper icon-green"><i class="fas fa-building"></i></div>
                        <div class="stat-value"><?php echo $total_organizers; ?></div>
                        <div class="stat-label">Organizers</div>
                    </div>
                </div>
                <!-- Pending Stats -->
                <div class="col-6 col-md-6 fade-in-up delay-3">
                    <div class="stat-card" data-tilt data-tilt-glare data-tilt-max-glare="0.2" data-tilt-scale="1.02">
                        <div class="stat-icon-wrapper icon-orange"><i class="fas fa-user-clock"></i></div>
                        <div class="stat-value text-warning"><?php echo $pending_users; ?></div>
                        <div class="stat-label">Pending Users</div>
                    </div>
                </div>
                <div class="col-6 col-md-6 fade-in-up delay-3">
                    <div class="stat-card" data-tilt data-tilt-glare data-tilt-max-glare="0.2" data-tilt-scale="1.02">
                        <div class="stat-icon-wrapper icon-purple"><i class="fas fa-file-signature"></i></div>
                        <div class="stat-value text-primary"><?php echo $pending_posts; ?></div>
                        <div class="stat-label">Pending Posts</div>
                    </div>
                </div>
            </div>

            <!-- User Approvals List -->
            <div class="fade-in-up delay-3">
                <div class="section-header">
                    <div class="section-title">
                        Pending Approvals <span class="badge bg-warning text-dark rounded-pill ms-2"><?php echo $pending_users; ?></span>
                    </div>
                    <?php if($pending_users > 5): ?>
                        <a href="verify_users.php" class="text-decoration-none small fw-bold text-primary">View All <i class="fas fa-arrow-right ms-1"></i></a>
                    <?php endif; ?>
                </div>

                <?php if(count($pending_list) > 0): ?>
                    <?php foreach($pending_list as $user): ?>
                    <div class="approval-card">
                        <div class="d-flex align-items-center">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                            <div class="user-meta">
                                <h6>
                                    <?php echo htmlspecialchars($user['full_name']); ?>
                                    <span class="badge-role"><?php echo ucfirst($user['role']); ?></span>
                                </h6>
                                <span><?php echo time_elapsed_string($user['created_at']); ?></span>
                            </div>
                        </div>
                        <div class="action-group">
                            <a href="approve_users.php?id=<?php echo $user['id']; ?>&action=approve" class="btn-icon btn-approve" title="Approve">
                                <i class="fas fa-check"></i>
                            </a>
                            <a href="approve_users.php?id=<?php echo $user['id']; ?>&action=reject" class="btn-icon btn-reject" title="Reject" onclick="return confirm('Reject this user?');">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5 bg-white rounded-4 border border-light shadow-sm">
                        <img src="https://cdni.iconscout.com/illustration/premium/thumb/task-complete-4659392-3868884.png" width="120" alt="All Done" style="opacity:0.8;">
                        <h6 class="fw-bold text-dark mt-3">All Caught Up!</h6>
                        <p class="text-muted small mb-0">No pending users waiting for approval.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Right Column: Quick Actions (Desktop Only) -->
        <div class="col-lg-4 desktop-sidebar d-none d-lg-block fade-in-up delay-2">
            <div class="quick-actions">
                <h6 class="fw-bold text-uppercase text-muted small mb-3 ls-1">Quick Management</h6>
                
                <a href="verify_posts.php" class="qa-link">
                    <span class="qa-icon"><i class="fas fa-briefcase"></i></span>
                    Verify Opportunities
                    <?php if($pending_posts > 0): ?>
                        <span class="badge bg-danger rounded-pill ms-auto"><?php echo $pending_posts; ?></span>
                    <?php endif; ?>
                </a>
                
                <a href="verify_users.php" class="qa-link">
                    <span class="qa-icon"><i class="fas fa-users-cog"></i></span>
                    Manage Organizers
                </a>
                
                <hr class="text-muted opacity-25 my-3">
                
                <a href="../index.php" target="_blank" class="qa-link">
                    <span class="qa-icon"><i class="fas fa-external-link-alt"></i></span>
                    View Live Site
                </a>
            </div>
        </div>

    </div>
</div>

<!-- Mobile Bottom Navigation -->
<div class="bottom-nav">
    <a href="dashboard.php" class="nav-item-mobile active">
        <i class="fas fa-th-large"></i>
        <span>Home</span>
    </a>
    <a href="verify_posts.php" class="nav-item-mobile">
        <i class="fas fa-briefcase"></i>
        <span>Posts</span>
        <?php if($pending_posts > 0): ?>
            <span class="position-absolute top-0 start-50 translate-middle p-1 bg-danger border border-light rounded-circle" style="width:8px; height:8px; margin-left:8px; margin-top:10px;"></span>
        <?php endif; ?>
    </a>
    <a href="verify_users.php" class="nav-item-mobile">
        <i class="fas fa-users"></i>
        <span>Users</span>
    </a>
    <a href="../logout.php" class="nav-item-mobile text-danger">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
    </a>
</div>

</body>
</html>