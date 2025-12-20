<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// 1. Security Check
checkRole('admin');

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    
    // 2. Check current status
    $stmt = $pdo->prepare("SELECT is_verified FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $current = $stmt->fetchColumn();

    // 3. Toggle Status (If 1, make 0. If 0, make 1)
    $new_status = ($current == 1) ? 0 : 1;

    // 4. Update Database
    $update = $pdo->prepare("UPDATE users SET is_verified = ? WHERE id = ?");
    if ($update->execute([$new_status, $user_id])) {
        // Redirect back to the user list
        header("Location: verify_users.php?msg=updated");
    }
}
?>