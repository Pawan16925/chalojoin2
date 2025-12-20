<?php
session_start();

// Function to redirect users based on role
function redirectBasedOnRole($role) {
    switch ($role) {
        case 'admin':
            header("Location: ./admin/dashboard.php");
            break;
        case 'organizer':
            header("Location: ./organizer/dashboard.php");
            break;
        case 'student':
            header("Location: ./student/dashboard.php");
            break;
        default:
            header("Location: ../login.php");
    }
    exit();
}

// Function to check if user is logged in and has correct role
function checkRole($required_role) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== $required_role) {
        // Redirect to login if unauthorized
        header("Location: ../login.php?error=unauthorized");
        exit();
    }
}
?>