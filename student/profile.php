<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// 1. Security Check
checkRole('student');

$student_id = $_SESSION['user_id'];
$message = '';
$error = '';

// 2. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $skills = trim($_POST['skills']);
    $bio = trim($_POST['bio']);
    $linkedin = trim($_POST['linkedin_url']);
    $portfolio = trim($_POST['portfolio_url']);

    // Handle Profile Image Upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_img_name = "user_" . $student_id . "." . $ext;
            $destination = "../uploads/avatars/" . $new_img_name;
            if(move_uploaded_file($_FILES['profile_image']['tmp_name'], $destination)) {
                $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                $stmt->execute([$new_img_name, $student_id]);
            }
        } else {
            $error = "Invalid image format. Only JPG, PNG, GIF allowed.";
        }
    }

    // Handle Resume Upload
    if (isset($_FILES['resume_file']) && $_FILES['resume_file']['error'] == 0) {
        $allowed_docs = ['pdf', 'doc', 'docx'];
        $filename = $_FILES['resume_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed_docs)) {
            $new_resume_name = "resume_" . $student_id . "_" . time() . "." . $ext;
            $destination = "../uploads/resumes/" . $new_resume_name;
            if(move_uploaded_file($_FILES['resume_file']['tmp_name'], $destination)) {
                $stmt = $pdo->prepare("UPDATE users SET resume_file = ? WHERE id = ?");
                $stmt->execute([$new_resume_name, $student_id]);
            }
        } else {
            $error = "Invalid resume format. Only PDF, DOC, DOCX allowed.";
        }
    }

    // Update Text Fields
    if (!$error) {
        $sql = "UPDATE users SET full_name=?, phone=?, skills=?, bio=?, linkedin_url=?, portfolio_url=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$full_name, $phone, $skills, $bio, $linkedin, $portfolio, $student_id])) {
            $message = "Profile updated successfully!";
            $_SESSION['full_name'] = $full_name;
        } else {
            $error = "Database update failed.";
        }
    }
}

// 3. Fetch Current User Data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$student_id]);
$user = $stmt->fetch();

// Profile Image Logic
$my_avatar = $user['profile_image'] ? "../uploads/avatars/" . $user['profile_image'] : "https://via.placeholder.com/150";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Edit Profile - ChaloJoin</title>
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
            --card-bg: #ffffff;
            --text-dark: #050505;
            --text-muted: #65676b;
        }

        body {
            background-color: var(--bg-color);
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            padding-bottom: 80px; /* Space for bottom nav */
        }

        /* --- NAVBAR (Desktop) --- */
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

        /* --- MOBILE BOTTOM NAV --- */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 60px;
            background: #fff;
            border-top: 1px solid #eee;
            display: none; /* Hidden on Desktop */
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
        .bottom-nav-item i {
            font-size: 1.2rem;
            display: block;
            margin-bottom: 2px;
        }
        .bottom-nav-item.active { color: var(--primary-color); }

        /* --- LAYOUT --- */
        .main-container { margin-top: 80px; }
        
        /* --- SIDEBAR --- */
        .profile-card-mini .avatar {
            width: 64px; height: 64px; border-radius: 50%; object-fit: cover;
        }

        /* --- EDIT FORM STYLES --- */
        .edit-profile-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            background: #fff;
        }
        
        .avatar-upload-container {
            position: relative;
            display: inline-block;
        }
        .avatar-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #f0f2f5;
        }
        .camera-icon-btn {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2px solid white;
        }
        
        .form-control {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
        }
        .form-control:focus {
            background-color: #fff;
            box-shadow: none;
            border-color: var(--primary-color);
        }
        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #333;
            margin-bottom: 6px;
        }

        @media (max-width: 991px) {
            .sidebar-left { display: none; }
            .bottom-nav { display: flex; }
            .navbar .navbar-nav { display: none; }
            .main-container { margin-top: 70px; }
        }
    </style>
</head>
<body>

<!-- 1. TOP NAVBAR -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container d-flex align-items-center justify-content-between">
        <a class="navbar-brand" href="dashboard.php">ChaloJoin</a>
        
        <!-- Desktop User Menu -->
        <ul class="navbar-nav ms-auto d-none d-lg-flex align-items-center">
            <li class="nav-item">
                <span class="fw-bold text-dark me-3"><?php echo htmlspecialchars($user['full_name']); ?></span>
            </li>
            <li class="nav-item">
                <a href="../logout.php" class="btn btn-sm btn-outline-danger rounded-pill">Logout</a>
            </li>
        </ul>
    </div>
</nav>

<!-- 2. MAIN CONTENT -->
<div class="container main-container">
    <div class="row">
        
        <!-- LEFT SIDEBAR (Desktop Only) -->
        <div class="col-lg-3 sidebar-left">
            <div class="sticky-top" style="top: 80px;">
                <div class="list-group shadow-sm rounded-3 border-0">
                    <a href="dashboard.php" class="list-group-item list-group-item-action border-0"><i class="fas fa-home me-2"></i> Feed</a>
                    <a href="my_applications.php" class="list-group-item list-group-item-action border-0"><i class="fas fa-briefcase me-2"></i> Applications</a>
                    <a href="saved_jobs.php" class="list-group-item list-group-item-action border-0"><i class="fas fa-bookmark me-2"></i> Saved</a>
                    <a href="profile.php" class="list-group-item list-group-item-action border-0 active fw-bold"><i class="fas fa-user-cog me-2"></i> Settings</a>
                </div>
            </div>
        </div>

        <!-- CENTER: EDIT PROFILE FORM -->
        <div class="col-lg-8 col-md-12">
            
            <?php if($message): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card edit-profile-card p-4">
                <h4 class="mb-4 fw-bold">Edit Profile</h4>
                
                <form action="" method="POST" enctype="multipart/form-data">
                    
                    <!-- Avatar Upload Section -->
                    <div class="text-center mb-5">
                        <div class="avatar-upload-container">
                            <img src="<?php echo $my_avatar; ?>" class="avatar-preview">
                            <label for="profile_image" class="camera-icon-btn">
                                <i class="fas fa-camera"></i>
                            </label>
                            <input type="file" id="profile_image" name="profile_image" hidden accept="image/*" onchange="document.querySelector('.avatar-preview').src = window.URL.createObjectURL(this.files[0])">
                        </div>
                        <p class="text-muted small mt-2">Tap icon to change photo</p>
                    </div>

                    <!-- Personal Info -->
                    <h6 class="text-primary text-uppercase small fw-bold mb-3">Personal Information</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control text-muted" value="<?php echo htmlspecialchars($user['email']); ?>" readonly disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bio / Headline</label>
                            <input type="text" name="bio" class="form-control" placeholder="Student at XYZ University" value="<?php echo htmlspecialchars($user['bio']); ?>">
                        </div>
                    </div>

                    <!-- Professional Info -->
                    <h6 class="text-primary text-uppercase small fw-bold mb-3">Professional Details</h6>
                    <div class="mb-3">
                        <label class="form-label">Skills (Comma Separated)</label>
                        <input type="text" name="skills" class="form-control" placeholder="Java, Python, Leadership" value="<?php echo htmlspecialchars($user['skills']); ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Resume / CV</label>
                        <input type="file" name="resume_file" class="form-control mb-2">
                        <?php if($user['resume_file']): ?>
                            <div class="p-2 bg-light rounded d-flex justify-content-between align-items-center">
                                <span class="small text-muted"><i class="fas fa-file-pdf me-2"></i> Current Resume</span>
                                <a href="../uploads/resumes/<?php echo $user['resume_file']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Social Links -->
                    <h6 class="text-primary text-uppercase small fw-bold mb-3">Social Links</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label"><i class="fab fa-linkedin text-primary me-1"></i> LinkedIn URL</label>
                            <input type="url" name="linkedin_url" class="form-control" placeholder="https://linkedin.com/in/..." value="<?php echo htmlspecialchars($user['linkedin_url']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-globe text-secondary me-1"></i> Portfolio URL</label>
                            <input type="url" name="portfolio_url" class="form-control" placeholder="https://mywebsite.com" value="<?php echo htmlspecialchars($user['portfolio_url']); ?>">
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold">Save Changes</button>
                    </div>

                </form>
            </div>
            
            <!-- Mobile Only Logout Button -->
            <div class="d-grid mt-3 d-lg-none">
                <a href="../logout.php" class="btn btn-outline-danger">Log Out</a>
            </div>

        </div>
    </div>
</div>

<!-- 3. MOBILE BOTTOM NAVIGATION -->
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
    <a href="profile.php" class="bottom-nav-item active">
        <i class="fas fa-user"></i>
        <span>Profile</span>
    </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>