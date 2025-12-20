<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// 1. Security Check
checkRole('student');

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['full_name'];

$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;

$results = [];
$title_text = "All Opportunities";

// --- BLUE TICK SVG ---
$blue_tick = '<span class="verified-badge"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22.5 12.0001C22.5 13.3857 21.9427 14.6543 21.0425 15.5866C20.8252 15.8118 20.7308 16.1266 20.7853 16.4335C21.0118 17.708 20.6549 19.0345 19.6644 20.0249C18.674 21.0154 17.3474 21.3723 16.0729 21.1458C15.766 21.0913 15.4513 21.1857 15.226 21.4031C14.2938 22.3032 13.0252 22.8606 11.6395 22.8606C10.2539 22.8606 8.98528 22.3032 8.05307 21.4031C7.82782 21.1857 7.51307 21.0913 7.20619 21.1458C5.93171 21.3723 4.60515 21.0154 3.61469 20.0249C2.62423 19.0345 2.26732 17.708 2.49383 16.4335C2.54833 16.1266 2.45395 15.8118 2.23658 15.5866C1.33642 14.6543 0.779053 13.3857 0.779053 12.0001C0.779053 10.6144 1.33642 9.34582 2.23658 8.41357C2.45395 8.18833 2.54833 7.87358 2.49383 7.56667C2.26732 6.29215 2.62423 4.96564 3.61469 3.97518C4.60515 2.98472 5.93171 2.62781 7.20619 2.85433C7.51307 2.90883 7.82782 2.81445 8.05307 2.59708C8.98528 1.69692 10.2539 1.13953 11.6395 1.13953C13.0252 1.13953 14.2938 1.69692 15.226 2.59708C15.4513 2.81445 15.766 2.90883 16.0729 2.85433C17.3474 2.62781 18.674 2.98472 19.6644 3.97518C20.6549 4.96564 21.0118 6.29215 20.7853 7.56667C20.7308 7.87358 20.8252 8.18833 21.0425 8.41357C21.9427 9.34582 22.5 10.6144 22.5 12.0001Z" fill="#0095F6"/><path d="M10.0957 16.5925L6.3421 12.839C6.0142 12.5111 6.0142 11.9794 6.3421 11.6515L7.20573 10.7879C7.53363 10.4599 8.06528 10.4599 8.39318 10.7879L10.6894 13.0841L15.9333 7.84024C16.2612 7.51234 16.7928 7.51234 17.1207 7.84024L17.9844 8.70387C18.3123 9.03177 18.3123 9.56342 17.9844 9.89132L11.2831 16.5925C10.9552 16.9204 10.4236 16.9204 10.0957 16.5925Z" fill="white"/></svg></span>';

// 2. Build the Query (Updated to fetch is_verified)
if ($keyword) {
    // Search by Keyword
    $title_text = "Search results for: \"" . htmlspecialchars($keyword) . "\"";
    $sql = "SELECT o.*, c.name AS category_name, u.full_name AS organizer_name, u.profile_image, u.is_verified 
            FROM opportunities o 
            JOIN categories c ON o.category_id = c.id 
            JOIN users u ON o.organizer_id = u.id 
            WHERE o.status = 'approved' 
            AND o.deadline >= CURRENT_DATE()
            AND (o.title LIKE ? OR o.description LIKE ? OR o.location LIKE ?)";
    
    $stmt = $pdo->prepare($sql);
    $search_term = "%$keyword%";
    $stmt->execute([$search_term, $search_term, $search_term]);
    $results = $stmt->fetchAll();

} elseif ($category_id) {
    // Search by Category
    $cat_stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $cat_stmt->execute([$category_id]);
    $cat_name = $cat_stmt->fetchColumn();
    
    if($cat_name) {
        $title_text = "Category: " . htmlspecialchars($cat_name);
        $sql = "SELECT o.*, c.name AS category_name, u.full_name AS organizer_name, u.profile_image, u.is_verified 
                FROM opportunities o 
                JOIN categories c ON o.category_id = c.id 
                JOIN users u ON o.organizer_id = u.id 
                WHERE o.status = 'approved' 
                AND o.deadline >= CURRENT_DATE()
                AND o.category_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$category_id]);
        $results = $stmt->fetchAll();
    } else {
        $title_text = "Category not found";
    }

} else {
    // No search term? Show recent 20
    $title_text = "Recent Opportunities";
    $sql = "SELECT o.*, c.name AS category_name, u.full_name AS organizer_name, u.profile_image, u.is_verified 
            FROM opportunities o 
            JOIN categories c ON o.category_id = c.id 
            JOIN users u ON o.organizer_id = u.id 
            WHERE o.status = 'approved' AND o.deadline >= CURRENT_DATE()
            ORDER BY o.created_at DESC LIMIT 20";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Search - ChaloJoin</title>
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
            padding-bottom: 80px; /* Space for bottom nav */
        }

        /* --- NAVBAR --- */
        .navbar {
            background: #fff;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            height: 60px;
        }
        .navbar-brand { font-weight: 800; color: var(--primary-color) !important; font-size: 1.4rem; }

        /* --- SEARCH CARD --- */
        .search-container {
            margin-top: 80px;
        }
        
        .search-input-lg {
            border-radius: 50px;
            padding: 12px 20px;
            border: 1px solid #ddd;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            font-size: 1rem;
            transition: all 0.2s;
        }
        .search-input-lg:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 4px 10px rgba(24, 119, 242, 0.15);
        }

        /* --- RESULT CARDS --- */
        .opp-card {
            background: #fff;
            border-radius: 12px;
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            height: 100%;
            transition: transform 0.2s;
        }
        .opp-card:active { transform: scale(0.98); }
        
        .card-body { padding: 16px; }
        
        .organizer-img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid #f0f0f0; }
        .verified-badge { margin-left: 4px; display: inline-flex; vertical-align: middle; }
        
        .org-name { font-size: 0.9rem; font-weight: 600; color: #333; }
        .card-title { font-weight: 700; font-size: 1.05rem; margin-top: 10px; margin-bottom: 8px; }
        
        .badge-light-custom {
            background: #f0f2f5;
            color: #444;
            font-weight: 500;
            border: 1px solid #e4e6eb;
        }

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
            .bottom-nav { display: flex; }
            .navbar .navbar-nav { display: none; }
            .search-container { margin-top: 70px; }
        }
    </style>
</head>
<body>

<!-- TOP NAVBAR -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container d-flex align-items-center justify-content-between">
        <a class="navbar-brand" href="dashboard.php">ChaloJoin</a>
        
        <!-- Desktop User Info -->
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
<div class="container search-container mb-5">
    
    <!-- Large Search Bar -->
    <div class="row justify-content-center mb-4">
        <div class="col-lg-8 col-md-10">
            <form action="search.php" method="GET" class="d-flex">
                <input class="form-control search-input-lg me-2" type="search" name="q" placeholder="Search internships, events..." value="<?php echo htmlspecialchars($keyword); ?>">
                <button class="btn btn-primary rounded-pill px-4 fw-bold" type="submit">Search</button>
            </form>
        </div>
    </div>

    <!-- Results Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0 text-dark"><?php echo $title_text; ?></h5>
        <span class="badge bg-light text-dark border"><?php echo count($results); ?> found</span>
    </div>

    <!-- Results Grid -->
    <?php if(count($results) > 0): ?>
        <div class="row">
            <?php foreach($results as $opp): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card opp-card">
                        <div class="card-body">
                            <!-- Header -->
                            <div class="d-flex align-items-center mb-2">
                                <img src="../uploads/avatars/<?php echo $opp['profile_image'] ?: 'default_avatar.png'; ?>" class="organizer-img me-2">
                                <div>
                                    <div class="org-name text-truncate" style="max-width: 180px;">
                                        <?php echo htmlspecialchars($opp['organizer_name']); ?>
                                        <?php if($opp['is_verified'] == 1) echo $blue_tick; ?>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.75rem;"><?php echo date('M d', strtotime($opp['created_at'])); ?></small>
                                </div>
                            </div>
                            
                            <!-- Title -->
                            <h5 class="card-title text-truncate"><?php echo htmlspecialchars($opp['title']); ?></h5>
                            
                            <!-- Tags -->
                            <div class="mb-3">
                                <span class="badge badge-light-custom rounded-pill me-1"><?php echo htmlspecialchars($opp['category_name']); ?></span>
                                <span class="badge badge-light-custom rounded-pill"><?php echo ucfirst($opp['type']); ?></span>
                            </div>

                            <!-- Meta -->
                            <p class="card-text text-muted small mb-3">
                                <i class="fas fa-map-marker-alt me-1 text-danger"></i> <?php echo htmlspecialchars($opp['location']); ?>
                                <span class="mx-1">•</span> 
                                <span class="text-dark fw-medium"><?php echo ucfirst($opp['mode']); ?></span>
                            </p>
                            
                            <div class="d-grid">
                                <a href="../view_opportunity.php?id=<?php echo $opp['id']; ?>" class="btn btn-outline-primary rounded-pill btn-sm fw-bold">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- Empty State -->
        <div class="text-center py-5">
            <img src="https://cdni.iconscout.com/illustration/premium/thumb/search-result-not-found-2130361-1800925.png" width="200" alt="No results">
            <h5 class="mt-3 fw-bold">No results found</h5>
            <p class="text-muted">Try using different keywords or categories.</p>
            <a href="dashboard.php" class="btn btn-primary rounded-pill px-4">Go to Feed</a>
        </div>
    <?php endif; ?>

</div>

<!-- BOTTOM NAV (Mobile) -->
<div class="bottom-nav">
    <a href="dashboard.php" class="bottom-nav-item">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <a href="my_applications.php" class="bottom-nav-item">
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