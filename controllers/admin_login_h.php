<?php

require_once 'config.php';
require_once __DIR__ . '/../helpers/Utils.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';

$message = '';
$message_type = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = Utils::sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $result = AuthHelper::authenticateAdmin($username, $password);
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'error';

    if ($result['success']) {
        header('Location: admin_dashboard.php');
        exit();
    }
}
?>