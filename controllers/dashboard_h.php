<?php
require_once __DIR__ . '/../services/UserService.php';
require_once __DIR__ . '/../services/DutyService.php';
require_once __DIR__ . '/../helpers/Utils.php';

$userService = new UserService();
$dutyService = new DutyService();
$utils = new Utils();

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'register':
                $full_name = $utils->sanitizeInput($_POST['full_name']);
                $student_number = $utils->sanitizeInput($_POST['student_number']);
                $committee = $utils->sanitizeInput($_POST['committee']);
                $position = $utils->sanitizeInput($_POST['position']);
                $course_year_section = $utils->sanitizeInput($_POST['course_year_section']);
                $age = (int)$_POST['age'];
                $contact_number = $utils->sanitizeInput($_POST['contact_number']);
                $address = $utils->sanitizeInput($_POST['address']);

                $result = $userService->register_user($full_name, $student_number, $committee, $position, $course_year_section, $age, $contact_number, $address);
                $message = $result['message'];
                $message_type = $result['success'] ? 'success' : 'error';
                break;

            case 'log_in':
                $student_number = $utils->sanitizeInput($_POST['student_number']);
                $password = isset($_POST['password']) ? $utils->sanitizeInput($_POST['password']) : null;
                $result = $dutyService->logInDuty($student_number, $password);
                $message = $result['message'];
                $message_type = $result['success'] ? 'success' : 'error';
                break;

            case 'log_out':
                $log_id = (int)$_POST['log_id'];
                $result = $dutyService->logOutDuty($log_id);
                $message = $result['message'];
                $message_type = $result['success'] ? 'success' : 'error';

                // Redirect to receipt page if logout was successful
                if ($result['success'] && isset($result['redirect_to_receipt'])) {
                    header('Location: duty_receipt.php?log_id=' . $result['log_id']);
                    exit();
                }
                break;
        }
    }
}

// Get current data
$duty_logs = method_exists($dutyService, 'getDutyLogs') ? $dutyService->getDutyLogs() : [];
$officer_stats = method_exists($userService, 'getOfficerStats') ? $userService->getOfficerStats() : [];
$committee_stats = method_exists($userService, 'getCommitteeStats') ? $userService->getCommitteeStats() : [];
?>