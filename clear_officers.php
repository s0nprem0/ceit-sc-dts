<?php
require_once 'config.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Only allow POST for destructive action
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_users.php');
    exit();
}

// Perform deletion inside a transaction
$conn->begin_transaction();
try {
    // Delete duty logs first (FK cascade will also handle, but explicit is safe)
    if (!$conn->query("DELETE FROM duty_logs")) {
        throw new Exception($conn->error);
    }

    // Delete all users
    if (!$conn->query("DELETE FROM users")) {
        throw new Exception($conn->error);
    }

    $conn->commit();
    header('Location: manage_users.php?type=success&message=' . urlencode('All officers and duty records cleared.'));
    exit();
} catch (Exception $e) {
    $conn->rollback();
    header('Location: manage_users.php?type=danger&message=' . urlencode('Failed to clear officers: ' . $e->getMessage()));
    exit();
}

?>

