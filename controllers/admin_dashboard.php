<?php
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Handle create account action (API Endpoint)
if (($_POST['action'] ?? '') === 'create_account') {
    header('Content-Type: application/json');

    $full_name = trim($_POST['full_name'] ?? '');
    $student_number = trim($_POST['student_number'] ?? '');
    $committee = trim($_POST['committee'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $course_year_section = trim($_POST['course_year_section'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $contact_number = trim($_POST['contact_number'] ?? '');
    $address = trim($_POST['address'] ?? '');

    $result = create_user($full_name, $student_number, $committee, $position, $course_year_section, $age, $contact_number, $address);
    echo json_encode($result);
    exit();
}

// Handle CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $filter_committee = $_GET['filter_committee'] ?? '';
    $filter_date = $_GET['filter_date'] ?? '';
    $filter_month = $_GET['filter_month'] ?? '';
    export_duty_logs_csv($filter_committee, $filter_date, $filter_month);
    exit();
}

// Get filter parameters
$filter_committee = $_GET['filter_committee'] ?? '';
$filter_date = $_GET['filter_date'] ?? '';
$filter_month = $_GET['filter_month'] ?? '';
$filter_week = $_GET['filter_week'] ?? '';

// Get data
$duty_logs = get_all_duty_logs($filter_committee, $filter_date, $filter_month, $filter_week);
$officer_stats = get_officer_stats();
$committee_stats = get_committee_stats();

// Normalize data to avoid warnings if functions return unexpected types
if (!is_array($duty_logs)) { $duty_logs = []; }
if (!is_array($officer_stats)) { $officer_stats = []; }
if (!is_array($committee_stats)) { $committee_stats = []; }

// Committee color and icon mapping (Moved to controller to keep View cleaner)
$committee_colors = [
    'Internal Affairs' => ['color' => '#000000', 'icon' => 'fas fa-home'],
    'External Affairs' => ['color' => '#FF0000', 'icon' => 'fas fa-globe'],
    'Records and Documentation' => ['color' => '#FFC300', 'icon' => 'fas fa-file-alt'],
    'Finance and Budget Management' => ['color' => '#008000', 'icon' => 'fas fa-dollar-sign'],
    'Audit Planning and Risk Assessment' => ['color' => '#7D7FB8', 'icon' => 'fas fa-search'],
    'Operations and Implementation' => ['color' => '#FFA500', 'icon' => 'fas fa-cogs'],
    'Public Relations' => ['color' => '#FF1493', 'icon' => 'fas fa-bullhorn'],
    'Business Affairs and Procurement' => ['color' => '#40E0D0', 'icon' => 'fas fa-briefcase'],
    'Social and Environmental Awareness' => ['color' => '#A52A2A', 'icon' => 'fas fa-leaf'],
    'Sports, Culture, and the Arts' => ['color' => '#800080', 'icon' => 'fas fa-palette'],
    'Student Rights and Welfare' => ['color' => '#0000FF', 'icon' => 'fas fa-shield-alt'],
    'Council Officer' => ['color' => '#BF6013', 'icon' => 'fas fa-crown']
];

// Load the View
require_once 'views/admin_dashboard_view.php';