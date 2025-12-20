<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// 1. Security Check
checkRole('organizer');

$organizer_id = $_SESSION['user_id'];
$message = '';
$error = '';

// 2. Fetch Categories for the Dropdown
$cat_stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $cat_stmt->fetchAll();

// 3. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $category_id = $_POST['category_id'];
    $type = $_POST['type'];
    $location = trim($_POST['location']);
    $mode = $_POST['mode'];
    $deadline = $_POST['deadline'];
    $description = trim($_POST['description']);

    // Basic Validation
    if (empty($title) || empty($location) || empty($deadline) || empty($description)) {
        $error = "All fields are required.";
    } else {
        // Insert into Database
        $sql = "INSERT INTO opportunities (organizer_id, title, category_id, type, location, mode, deadline, description, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$organizer_id, $title, $category_id, $type, $location, $mode, $deadline, $description])) {
            $message = "Opportunity posted successfully! It is now pending Admin approval.";
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Opportunity - ChaloJoin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <span class="navbar-brand mb-0 h1">ChaloJoin <small class="text-warning">Organizer</small></span>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm">Back to Dashboard</a>
    </div>
</nav>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">Post New Opportunity</h4>
                </div>
                <div class="card-body">
                    
                    <?php if($message): ?>
                        <div class="alert alert-success"><?php echo $message; ?> <a href="dashboard.php">Go back</a></div>
                    <?php endif; ?>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Title</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Frontend Intern or Coding Hackathon" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Category</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <?php foreach($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Type</label>
                                <select name="type" class="form-select" required>
                                    <option value="internship">Internship</option>
                                    <option value="competition">Competition</option>
                                    <option value="scholarship">Scholarship</option>
                                    <option value="event">Event</option>
                                    <option value="webinar">Webinar</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Location</label>
                                <input type="text" name="location" class="form-control" placeholder="e.g. Mumbai or Remote" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Mode</label>
                                <select name="mode" class="form-select">
                                    <option value="online">Online / Remote</option>
                                    <option value="offline">Offline / In-Office</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Application Deadline</label>
                            <input type="date" name="deadline" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Description & Requirements</label>
                            <textarea name="description" class="form-control" rows="6" placeholder="Describe the role, eligibility criteria, perks, etc." required></textarea>
                            <div class="form-text">You can include details about stipend, duration, and certificates here.</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Submit for Approval</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>