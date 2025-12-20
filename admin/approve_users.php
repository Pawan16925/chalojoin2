<?php
// admin/approve_users.php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Check role
checkRole('admin');

if (isset($_GET['id']) && isset($_GET['action'])) {
    $user_id = intval($_GET['id']);
    $action = $_GET['action'];

    // Determine new status
    if ($action === 'approve') {
        $new_status = 'approved';
    } elseif ($action === 'reject') {
        $new_status = 'rejected';
    } else {
        // Invalid action
        header("Location: dashboard.php");
        exit();
    }

    // Update Database
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    
    if ($stmt->execute([$new_status, $user_id])) {
        // Optional: You could add a $_SESSION['message'] here to show a success alert on the dashboard
        header("Location: dashboard.php");
    } else {
        echo "Error updating record.";
    }
} else {
    header("Location: dashboard.php");
}
?>