<?php
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $log_id = $_POST['log_id'] ?? '';

    if (!empty($log_id) && ctype_digit($log_id)) {
        $stmt = $conn->prepare("DELETE FROM duty_logs WHERE log_id = ?");
        $stmt->bind_param("i", $log_id);

        if ($stmt->execute()) {
            $_SESSION['flash_message'] = "Duty log deleted successfully.";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_message'] = "Error deleting duty log: " . $stmt->error;
            $_SESSION['flash_type'] = "danger";
        }

        $stmt->close();
    } else {
        $_SESSION['flash_message'] = "Invalid log ID.";
        $_SESSION['flash_type'] = "warning";
    }

    header("Location: admin_dashboard.php");
    exit();
} else {
    header("Location: admin_dashboard.php");
    exit();
}
