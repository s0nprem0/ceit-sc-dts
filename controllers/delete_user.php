<?php
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    
    $result = delete_user($user_id);
    
    if ($result['success']) {
        header('Location: admin_dashboard.php?message=' . urlencode($result['message']) . '&type=success');
    } else {
        header('Location: admin_dashboard.php?message=' . urlencode($result['message']) . '&type=danger');
    }
    exit();
}

// If not a POST request, redirect to admin dashboard
header('Location: admin_dashboard.php');
exit();
?>

