<?php
require_once 'config.php';

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'register':
                $full_name = sanitize_input($_POST['full_name']);
                $student_number = sanitize_input($_POST['student_number']);
                $committee = sanitize_input($_POST['committee']);
                $position = sanitize_input($_POST['position']);
                $course_year_section = sanitize_input($_POST['course_year_section']);
                $age = (int)$_POST['age'];
                $contact_number = sanitize_input($_POST['contact_number']);
                $address = sanitize_input($_POST['address']);

                $result = register_user($full_name, $student_number, $committee, $position, $course_year_section, $age, $contact_number, $address);
                $message = $result['message'];
                $message_type = $result['success'] ? 'success' : 'error';
                break;

            case 'log_in':
                $student_number = sanitize_input($_POST['student_number']);
                $password = isset($_POST['password']) ? sanitize_input($_POST['password']) : null;
                $result = log_in_duty($student_number, $password);
                $message = $result['message'];
                $message_type = $result['success'] ? 'success' : 'error';
                break;

            case 'log_out':
                $log_id = (int)$_POST['log_id'];
                $result = log_out_duty($log_id);
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
$duty_logs = get_duty_logs();
$officer_stats = get_officer_stats();
$committee_stats = get_committee_stats();
?>