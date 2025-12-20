<?php
require_once 'config/db.php';
session_start();

// 1. Fetch Stats for the Counter Section
$opp_count = $pdo->query("SELECT COUNT(*) FROM opportunities WHERE status='approved'")->fetchColumn();
$student_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$org_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role='organizer'")->fetchColumn();

// 2. Fetch Recent Opportunities (Sneak Peek)
// Fetch 'is_verified' for blue tick
$sql = "SELECT o.*, u.full_name as organizer_name, u.profile_image, u.is_verified 
        FROM opportunities o 
        JOIN users u ON o.organizer_id = u.id 
        WHERE o.status = 'approved' AND o.deadline >= CURRENT_DATE() 
        ORDER BY o.created_at DESC LIMIT 6";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$recent_jobs = $stmt->fetchAll();

// --- BLUE TICK SVG ---
$blue_tick = '<span class="verified-badge"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22.5 12.0001C22.5 13.3857 21.9427 14.6543 21.0425 15.5866C20.8252 15.8118 20.7308 16.1266 20.7853 16.4335C21.0118 17.708 20.6549 19.0345 19.6644 20.0249C18.674 21.0154 17.3474 21.3723 16.0729 21.1458C15.766 21.0913 15.4513 21.1857 15.226 21.4031C14.2938 22.3032 13.0252 22.8606 11.6395 22.8606C10.2539 22.8606 8.98528 22.3032 8.05307 21.4031C7.82782 21.1857 7.51307 21.0913 7.20619 21.1458C5.93171 21.3723 4.60515 21.0154 3.61469 20.0249C2.62423 19.0345 2.26732 17.708 2.49383 16.4335C2.54833 16.1266 2.45395 15.8118 2.23658 15.5866C1.33642 14.6543 0.779053 13.3857 0.779053 12.0001C0.779053 10.6144 1.33642 9.34582 2.23658 8.41357C2.45395 8.18833 2.54833 7.87358 2.49383 7.56667C2.26732 6.29215 2.62423 4.96564 3.61469 3.97518C4.60515 2.98472 5.93171 2.62781 7.20619 2.85433C7.51307 2.90883 7.82782 2.81445 8.05307 2.59708C8.98528 1.69692 10.2539 1.13953 11.6395 1.13953C13.0252 1.13953 14.2938 1.69692 15.226 2.59708C15.4513 2.81445 15.766 2.90883 16.0729 2.85433C17.3474 2.62781 18.674 2.98472 19.6644 3.97518C20.6549 4.96564 21.0118 6.29215 20.7853 7.56667C20.7308 7.87358 20.8252 8.18833 21.0425 8.41357C21.9427 9.34582 22.5 10.6144 22.5 12.0001Z" fill="#0095F6"/><path d="M10.0957 16.5925L6.3421 12.839C6.0142 12.5111 6.0142 11.9794 6.3421 11.6515L7.20573 10.7879C7.53363 10.4599 8.06528 10.4599 8.39318 10.7879L10.6894 13.0841L15.9333 7.84024C16.2612 7.51234 16.7928 7.51234 17.1207 7.84024L17.9844 8.70387C18.3123 9.03177 18.3123 9.56342 17.9844 9.89132L11.2831 16.5925C10.9552 16.9204 10.4236 16.9204 10.0957 16.5925Z" fill="white"/></svg></span>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ChaloJoin - Find Opportunities</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Three.js for 3D Background -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <!-- Vanilla Tilt for 3D Card Effect -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.0/vanilla-tilt.min.js"></script>
    
    <style>
        :root {
            /* Updated Palette: Modern Tech Blue */
            --primary-color: #2563eb; /* Vibrant Blue */
            --primary-hover: #1d4ed8;
            --accent-color: #06b6d4;  /* Cyan for gradients */
            --bg-color: #f8fafc;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --card-bg: #ffffff;
        }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-color);
            color: var(--text-dark);
            overflow-x: hidden;
        }
        
        /* Glassmorphism Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.03);
            padding: 16px 0;
            transition: all 0.3s ease;
        }
        .navbar-brand {
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 1.6rem;
            letter-spacing: -0.5px;
        }
        .nav-link { 
            font-weight: 600; 
            color: var(--text-dark) !important; 
            margin: 0 12px; 
            font-size: 0.95rem;
            opacity: 0.8;
            transition: opacity 0.2s;
        }
        .nav-link:hover { opacity: 1; color: var(--primary-color) !important; }

        /* Animated Hero Section */
        .hero-section {
            /* Animated Gradient Background */
            background: linear-gradient(-45deg, #1e3a8a, #2563eb, #3b82f6, #06b6d4);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            color: white;
            padding: 140px 0 160px;
            position: relative;
            overflow: hidden;
            border-bottom-left-radius: 60px;
            border-bottom-right-radius: 60px;
            z-index: 1;
        }
        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* 3D Canvas */
        #hero-3d-canvas {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1;
            opacity: 0.5; /* Particles blend with gradient */
        }

        /* Hero Animations */
        .hero-content { position: relative; z-index: 2; }
        
        .fade-in-up {
            animation: fadeInUp 1s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }

        @keyframes fadeInUp {
            to { opacity: 1; transform: translateY(0); }
        }

        .hero-title { 
            font-weight: 800; 
            letter-spacing: -1.5px; 
            margin-bottom: 24px; 
            text-shadow: 0 10px 30px rgba(0,0,0,0.15);
            font-size: 3.5rem;
        }
        .hero-text { 
            opacity: 0.9; 
            font-size: 1.25rem; 
            font-weight: 400; 
            margin-bottom: 48px; 
            line-height: 1.6;
        }
        
        .hero-btn {
            padding: 16px 48px;
            font-weight: 700;
            border-radius: 50px;
            transition: all 0.3s ease;
            font-size: 1.1rem;
            position: relative;
            overflow: hidden;
        }
        .btn-white { 
            background: white; 
            color: var(--primary-color); 
            border: 2px solid white;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .btn-white:hover { 
            background: transparent; 
            color: white; 
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        
        .btn-glass {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(5px);
            border: 2px solid rgba(255,255,255,0.5);
            color: white;
        }
        .btn-glass:hover {
            background: rgba(255,255,255,0.2);
            border-color: white;
            color: white;
            transform: translateY(-3px);
        }

        /* Glassmorphism Floating Stats */
        .stats-container { margin-top: -80px; position: relative; z-index: 10; padding-bottom: 60px; }
        .stat-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            padding: 40px 30px;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.08);
            text-align: center;
            transform-style: preserve-3d;
            transform: perspective(1000px);
        }
        /* Make stats pop */
        .stat-card-inner { transform: translateZ(30px); }

        .stat-icon {
            width: 70px; height: 70px;
            border-radius: 20px;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: var(--primary-color);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.75rem;
            margin: 0 auto 20px;
            box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.1);
        }
        .stat-number { font-weight: 900; font-size: 2.5rem; color: var(--text-dark); line-height: 1; margin-bottom: 8px; letter-spacing: -1px; }
        .stat-label { color: var(--text-muted); font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }

        /* Modern Opportunity Cards */
        .section-header h2 { font-weight: 800; color: var(--text-dark); letter-spacing: -1px; }
        .section-header h6 { color: var(--primary-color); letter-spacing: 2px; font-weight: 700; }

        .opp-card {
            background: white;
            border: 1px solid rgba(0,0,0,0.03);
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.04);
            height: 100%;
            transform-style: preserve-3d;
            transform: perspective(1000px);
            overflow: hidden; /* For smooth corners */
        }
        
        .card-body { padding: 30px; transform: translateZ(20px); }
        
        .org-header { display: flex; align-items: center; margin-bottom: 20px; }
        .org-avatar { 
            width: 50px; height: 50px; 
            border-radius: 12px; /* Squircle */
            object-fit: cover; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.05); 
            margin-right: 15px; 
        }
        .org-name { font-weight: 700; color: var(--text-dark); font-size: 1rem; }
        .verified-badge { margin-left: 5px; display: inline-flex; vertical-align: middle; }
        
        .card-title { font-weight: 800; font-size: 1.25rem; margin-bottom: 15px; color: var(--text-dark); line-height: 1.4; }
        
        .badge-tag { 
            background: #eff6ff; 
            color: var(--primary-color); 
            padding: 6px 14px; 
            border-radius: 30px; 
            font-size: 0.8rem; 
            font-weight: 600; 
            margin-right: 8px; 
            border: 1px solid rgba(37, 99, 235, 0.1);
        }

        .card-footer-custom {
            padding-top: 25px;
            margin-top: 15px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .location-text { color: var(--text-muted); font-weight: 500; font-size: 0.9rem; }

        /* Footer */
        footer { 
            background-color: white; 
            color: var(--text-dark); 
            padding: 80px 0 40px; 
            border-top: 1px solid #f1f5f9; 
        }
        .social-link {
            width: 48px; height: 48px;
            border-radius: 14px;
            background: #f8fafc;
            color: var(--text-muted);
            display: inline-flex; align-items: center; justify-content: center;
            margin: 0 8px;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 1.2rem;
        }
        .social-link:hover { 
            background: var(--primary-color); 
            color: white; 
            transform: translateY(-5px); 
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
        }

        @media (max-width: 768px) {
            .hero-section { padding: 100px 0 120px; border-radius: 0 0 40px 40px; }
            .hero-title { font-size: 2.5rem; }
            .stats-container { margin-top: -60px; }
            .stat-card { margin-bottom: 20px; padding: 30px 20px; }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#">ChaloJoin</a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon" style="filter: grayscale(100%);"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item me-3 d-none d-lg-block">
                        <span class="text-muted fw-bold small">Hello, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    </li>
                    <li class="nav-item mt-3 mt-lg-0">
                        <?php 
                            $dashboard_link = ($_SESSION['role'] == 'admin') ? 'admin/dashboard.php' : 
                                             (($_SESSION['role'] == 'organizer') ? 'organizer/dashboard.php' : 'student/dashboard.php');
                        ?>
                        <a href="<?php echo $dashboard_link; ?>" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Go to Dashboard</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Log In</a></li>
                    <li class="nav-item ms-2 mt-2 mt-lg-0">
                        <a href="register.php" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Get Started</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section with 3D Background -->
<section class="hero-section text-center">
    <!-- 3D Canvas Container -->
    <div id="hero-3d-canvas"></div>

    <div class="container hero-content">
        <div class="row justify-content-center">
            <div class="col-lg-9 col-md-11">
                <div class="fade-in-up delay-1">
                    <span class="badge bg-white bg-opacity-25 border border-white border-opacity-50 text-white rounded-pill px-4 py-2 mb-4 fw-bold shadow-sm text-uppercase" style="backdrop-filter:blur(5px); letter-spacing:1.5px; font-size:0.7rem;">
                        ✨ Launch Your Career Today
                    </span>
                </div>
                <h1 class="hero-title display-4 fade-in-up delay-2">Bridging Talent with <br> Limitless Opportunity</h1>
                <p class="hero-text mx-auto fade-in-up delay-3" style="max-width: 600px;">
                    Discover internships, hackathons, and exclusive events curated for students. 
                    Organizers, find the best talent to fuel your vision.
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap fade-in-up delay-3">
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <a href="register.php" class="btn btn-white hero-btn">Join as Student</a>
                        <a href="register.php" class="btn btn-glass hero-btn">Post Opportunity</a>
                    <?php else: ?>
                        <a href="<?php echo $dashboard_link; ?>" class="btn btn-white hero-btn">Browse Feed</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Floating Stats (3D Tilt Enabled) -->
<section class="container stats-container">
    <div class="row g-4 justify-content-center">
        <div class="col-md-4">
            <!-- Added 'data-tilt' for 3D effect -->
            <div class="stat-card" data-tilt data-tilt-glare data-tilt-max-glare="0.2" data-tilt-scale="1.02">
                <div class="stat-card-inner">
                    <div class="stat-icon"><i class="fas fa-briefcase"></i></div>
                    <div class="stat-number"><?php echo $opp_count; ?>+</div>
                    <div class="stat-label">Active Jobs</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" data-tilt data-tilt-glare data-tilt-max-glare="0.2" data-tilt-scale="1.02">
                <div class="stat-card-inner">
                    <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                    <div class="stat-number"><?php echo $student_count; ?>+</div>
                    <div class="stat-label">Students</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" data-tilt data-tilt-glare data-tilt-max-glare="0.2" data-tilt-scale="1.02">
                <div class="stat-card-inner">
                    <div class="stat-icon"><i class="fas fa-building"></i></div>
                    <div class="stat-number"><?php echo $org_count; ?>+</div>
                    <div class="stat-label">Companies</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Recent Opportunities (3D Tilt Enabled) -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5 section-header">
            <h6 class="text-uppercase">Fresh on the board</h6>
            <h2 class="display-6">Explore What's New</h2>
        </div>

        <div class="row g-4">
            <?php foreach($recent_jobs as $job): ?>
            <div class="col-lg-4 col-md-6">
                <!-- Added 'data-tilt' for 3D effect -->
                <div class="card opp-card" data-tilt data-tilt-max="3" data-tilt-speed="400" data-tilt-glare data-tilt-max-glare="0.05">
                    <div class="card-body d-flex flex-column h-100">
                        <div class="org-header">
                            <img src="uploads/avatars/<?php echo $job['profile_image'] ?: 'default_avatar.png'; ?>" class="org-avatar">
                            <div>
                                <div class="org-name">
                                    <?php echo htmlspecialchars($job['organizer_name']); ?>
                                    <?php if($job['is_verified'] == 1) echo $blue_tick; ?>
                                </div>
                                <small class="text-muted" style="font-size:0.8rem; font-weight:500;"><?php echo date('M d, Y', strtotime($job['created_at'])); ?></small>
                            </div>
                        </div>
                        
                        <h5 class="card-title text-truncate"><?php echo htmlspecialchars($job['title']); ?></h5>
                        
                        <div class="mb-3">
                            <span class="badge-tag"><?php echo ucfirst($job['type']); ?></span>
                            <span class="badge-tag"><?php echo ucfirst($job['mode']); ?></span>
                        </div>
                        
                        <p class="text-muted small mb-4 flex-grow-1" style="line-height:1.6;">
                            <?php echo substr(strip_tags($job['description']), 0, 90) . '...'; ?>
                        </p>

                        <div class="card-footer-custom">
                            <span class="location-text"><i class="fas fa-map-marker-alt text-danger me-2"></i><?php echo $job['location']; ?></span>
                            <a href="view_opportunity.php?id=<?php echo $job['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-bold">Details</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-5">
            <a href="register.php" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow-lg">View All Opportunities</a>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="text-center">
    <div class="container">
        <h3 class="fw-bold mb-3" style="color:var(--primary-color);">ChaloJoin</h3>
        <p class="text-muted mb-4" style="max-width:400px; margin:0 auto;">Empowering the next generation of talent to connect, learn, and grow together.</p>
        <div class="mb-5">
            <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
            <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
        </div>
        <div class="small text-muted border-top pt-4" style="border-color: #e2e8f0 !important;">
            &copy; 2025 ChaloJoin. All rights reserved.
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- 3D Background Script (Three.js) -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. Three.js Hero Background Setup ---
        const container = document.getElementById('hero-3d-canvas');
        
        if(typeof THREE !== 'undefined' && container) {
            const scene = new THREE.Scene();
            // Fog to blend particles into the distance (Color matches new blue gradient end)
            scene.fog = new THREE.FogExp2(0x3b82f6, 0.0015);

            const camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
            camera.position.z = 500;

            const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
            renderer.setSize(container.clientWidth, container.clientHeight);
            renderer.setPixelRatio(window.devicePixelRatio);
            container.appendChild(renderer.domElement);

            // Create Particles
            const geometry = new THREE.BufferGeometry();
            const particlesCount = 450;
            const posArray = new Float32Array(particlesCount * 3);

            for(let i = 0; i < particlesCount * 3; i++) {
                // Spread particles in a wide area
                posArray[i] = (Math.random() - 0.5) * 1600; 
            }

            geometry.setAttribute('position', new THREE.BufferAttribute(posArray, 3));

            // Material for particles
            const material = new THREE.PointsMaterial({
                size: 3,
                color: 0xffffff,
                transparent: true,
                opacity: 0.7,
            });

            // Create Mesh
            const particlesMesh = new THREE.Points(geometry, material);
            scene.add(particlesMesh);

            // Mouse Interaction Vars
            let mouseX = 0;
            let mouseY = 0;

            document.addEventListener('mousemove', (event) => {
                mouseX = event.clientX - window.innerWidth / 2;
                mouseY = event.clientY - window.innerHeight / 2;
            });

            // Animation Loop
            const animate = () => {
                requestAnimationFrame(animate);

                // Rotate the entire particle system slowly
                particlesMesh.rotation.y += 0.0008;
                particlesMesh.rotation.x += 0.0003;

                // Subtle parallax effect based on mouse
                particlesMesh.rotation.x += mouseY * 0.00002;
                particlesMesh.rotation.y += mouseX * 0.00002;

                renderer.render(scene, camera);
            };

            animate();

            // Handle Resize
            window.addEventListener('resize', () => {
                camera.aspect = container.clientWidth / container.clientHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(container.clientWidth, container.clientHeight);
            });
        }
    });
</script>

</body>
</html>