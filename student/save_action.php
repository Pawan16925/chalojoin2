<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// 1. Security Check
checkRole('student');

if (isset($_GET['id'])) {
    $opp_id = intval($_GET['id']);
    $student_id = $_SESSION['user_id'];

    // 2. Check if already saved
    $check = $pdo->prepare("SELECT id FROM saved_opportunities WHERE student_id = ? AND opportunity_id = ?");
    $check->execute([$student_id, $opp_id]);
    
    if ($check->rowCount() > 0) {
        // 3a. It exists -> DELETE (Unsave)
        $del = $pdo->prepare("DELETE FROM saved_opportunities WHERE student_id = ? AND opportunity_id = ?");
        $del->execute([$student_id, $opp_id]);
    } else {
        // 3b. It doesn't exist -> INSERT (Save)
        $ins = $pdo->prepare("INSERT INTO saved_opportunities (student_id, opportunity_id) VALUES (?, ?)");
        $ins->execute([$student_id, $opp_id]);
    }
}

// 4. Redirect user back to the previous page (Dashboard or Saved Page)
if (isset($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    header("Location: dashboard.php");
}
exit();
?>