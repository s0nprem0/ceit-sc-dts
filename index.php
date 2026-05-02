<?php

session_start();
require_once 'config.php';

// Get the path from the URL
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$script_name = dirname($_SERVER['SCRIPT_NAME']);
if (strpos($request_uri, $script_name) === 0) {
    $request_uri = substr($request_uri, strlen($script_name));
}
$request_uri = trim($request_uri, '/');

// Default to 'login' if path is empty
$page = $request_uri ?: 'dashboard';

function isAuthenticated()
{
    return isset($_SESSION['student_number']);
}

switch ($page) {
    case 'dashboard':
        require 'controllers/dashboard_h.php';
        require 'views/dashboard_v.php';
        break;
    default:
        http_response_code(404);
        echo '<h1>404 Not Found</h1>';
        exit;
}