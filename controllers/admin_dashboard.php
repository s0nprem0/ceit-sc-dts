<?php
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Handle create account action
if ($_POST['action'] ?? '' === 'create_account') {
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CEIT-SC Office Duty Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --ceit-primary: #BF6013;
            --ceit-secondary: #F4A441;
            --ceit-accent: #F4A441;
            --ceit-dark: #1C1C1C;
            --ceit-light: #FFFFFF;
            --gradient-primary: linear-gradient(135deg, #BF6013 0%, #F4A441 100%);
            --gradient-secondary: linear-gradient(135deg, #1C1C1C 0%, #2E2E2E 100%);
            --gradient-success: linear-gradient(135deg, #BF6013 0%, #F4A441 100%);
            --gradient-warning: linear-gradient(135deg, #F4A441 0%, #BF6013 100%);
            --gradient-danger: linear-gradient(135deg, #1C1C1C 0%, #2E2E2E 100%);
            --gradient-info: linear-gradient(135deg, #1C1C1C 0%, #2E2E2E 100%);
            --shadow-soft: 0 4px 15px rgba(0,0,0,0.1);
            --shadow-medium: 0 6px 20px rgba(0,0,0,0.15);
            --shadow-strong: 0 8px 25px rgba(0,0,0,0.2);
            --ceit-orange: #BF6013;
            --ceit-light-orange: #F4A441;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1C1C1C 0%, #2E2E2E 50%, #BF6013 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        .navbar {
            background: rgba(28, 28, 28, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            color: white !important;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        .logo {
            width: 50px;
            height: 50px;
            margin-right: 15px;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
        }
        
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: none;
            border-radius: 25px;
            box-shadow: var(--shadow-soft);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
            position: relative;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-strong);
        }
        
        .card:hover::before {
            opacity: 1;
        }
        
        .card-header {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 1.5rem;
            border-radius: 25px 25px 0 0 !important;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            transition: all 0.6s ease;
            opacity: 0;
        }
        
        .card:hover .card-header::before {
            animation: shimmer 1.5s ease-in-out;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); opacity: 0; }
        }
        
        .stats-card {
            background: var(--gradient-secondary);
            color: white;
            border: none;
            position: relative;
            overflow: hidden;
        }
        
        .stats-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }
        
        .stats-number {
            font-size: 3rem;
            font-weight: 800;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
            animation: countUp 1s ease-out;
        }
        
        @keyframes countUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .form-control, .form-select {
            border: 2px solid rgba(191, 96, 19, 0.1);
            border-radius: 15px;
            padding: 12px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--ceit-primary);
            box-shadow: 0 0 0 0.25rem rgba(191, 96, 19, 0.15);
            background: white;
            transform: translateY(-2px);
        }
        
        .btn {
            border: none;
            border-radius: 15px;
            padding: 12px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 8px 25px rgba(191, 96, 19, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(191, 96, 19, 0.4);
            background: var(--gradient-primary);
        }
        
        .btn-success {
            background: var(--gradient-success);
            color: white;
            box-shadow: 0 8px 25px rgba(17, 153, 142, 0.3);
        }
        
        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(17, 153, 142, 0.4);
            background: var(--gradient-success);
        }
        
        .btn-warning {
            background: var(--gradient-warning);
            color: white;
            box-shadow: 0 8px 25px rgba(240, 147, 251, 0.3);
        }
        
        .btn-warning:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(240, 147, 251, 0.4);
            background: var(--gradient-warning);
        }
        
        .btn-danger {
            background: var(--gradient-warning);
            color: white;
            box-shadow: 0 8px 25px rgba(252, 70, 107, 0.3);
        }
        
        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(252, 70, 107, 0.4);
            background: var(--gradient-danger);
        }
        
        .btn-info {
            background: var(--gradient-info);
            color: white;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        
        .btn-info:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
            background: var(--gradient-info);
        }
        
        .btn-light {
            background: rgba(255, 255, 255, 0.9);
            color: var(--ceit-dark);
            backdrop-filter: blur(10px);
        }
        
        .btn-light:hover {
            background: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .status-badge {
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 0.85em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        
        .status-badge:hover {
            transform: scale(1.05);
        }
        
        .status-ongoing {
            background: var(--gradient-success);
            color: white;
        }
        
        .status-onbreak {
            background: var(--gradient-warning);
            color: white;
        }
        
        .status-completed {
            background: var(--gradient-secondary);
            color: white;
        }
        
        .table {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-soft);
            background: white;
        }
        
        .table thead th {
            background: var(--gradient-secondary);
            color: white;
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1.2rem 1rem;
        }
        
        .table tbody tr {
            transition: all 0.3s ease;
        }
        
        .table tbody tr:hover {
            background: linear-gradient(90deg, rgba(191, 96, 19, 0.05), rgba(244, 164, 65, 0.05));
            transform: scale(1.01);
        }
        
        .table tbody td {
            padding: 1rem;
            border-color: rgba(0,0,0,0.05);
            vertical-align: middle;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 10px;
        }
        
        .nav-link:hover {
            color: white !important;
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .slide-in-left {
            animation: slideInLeft 0.6s ease-out;
        }
        
        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .slide-in-right {
            animation: slideInRight 0.6s ease-out;
        }
        
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .filter-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .committee-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            margin: 0.25rem;
            background: var(--gradient-info);
            color: white;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .committee-badge:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem;
            }
            
            .stats-number {
                font-size: 2rem;
            }
            
            .btn {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
        }
          /* Footer Styles */
        footer.footer {
            color: #f1f1f1;
            font-size: 0.9rem;
            padding: 20px 10px;
            width: 100%;
            background: linear-gradient(135deg, #1C1C1C 0%, #2E2E2E 50%, #BF6013 100%);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px 15px 0 0;
            box-shadow: 0 -3px 10px rgba(0,0,0,0.2);
        }

        .footer-divider {
            width: 60%;
            margin: 0 auto 15px auto;
            border: 0;
            height: 1px;
            background: rgba(255,255,255,0.2);
        }

        .footer-title {
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.5px;
            color: var(--ceit-light-orange);
        }

        .footer-text {
            font-size: 0.9rem;
            color: #e2e2e2;
        }

        .footer-subtext {
            font-size: 0.8rem;
            color: #cfcfcf;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid mt-4 mx-5">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="CEIT-SCLogo(White).png" alt="CEIT-SC Logo" class="logo">
                <span>CEIT-SC Admin Dashboard</span>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="committee_hours.php" style="margin-right: 5px;">
                    <i class="fas fa-chart-bar"></i> Committee Hours
                </a>
                <a class="nav-link" href="manage_users.php" style="margin-right: 5px;">
                    <i class="fas fa-users-cog"></i> Manage Users
                </a>
                <a class="nav-link" href="index.php" style="margin-right: 5px;">
                    <i class="fas fa-home"></i> Main Dashboard
                </a>
                <a class="nav-link" href="admin_logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4 px-5">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card stats-card fade-in">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-4x mb-3"></i>
                        <div class="stats-number"><?php echo intval($officer_stats['total_officers'] ?? 0); ?></div>
                        <h5 class="mb-0">Total Officers</h5>
                        <small class="opacity-75">Registered in System</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stats-card fade-in" style="animation-delay: 0.2s;">
                    <div class="card-body text-center">
                        <i class="fas fa-clock fa-4x mb-3"></i>
                        <div class="stats-number"><?php echo intval($officer_stats['active_duties'] ?? 0); ?></div>
                        <h5 class="mb-0">Active Duties</h5>
                        <small class="opacity-75">Currently On Duty</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stats-card fade-in" style="animation-delay: 0.4s;">
                    <div class="card-body text-center">
                        <i class="fas fa-history fa-4x mb-3"></i>
                        <div class="stats-number"><?php echo count($duty_logs); ?></div>
                        <h5 class="mb-0">Total Records</h5>
                        <small class="opacity-75">Filtered Results</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Officers by Committee -->
        <div class="card mb-4 slide-in-left">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Officers by Committee
                </h5>
            </div>
            <div class="card-body">
                <?php 
                // Committee color and icon mapping
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
                
                foreach ($committee_stats as $committee): 
                    $committee_name = $committee['committee'];
                    $committee_info = $committee_colors[$committee_name] ?? ['color' => '#AEB6BF', 'icon' => 'fas fa-users'];
                ?>
                    <span class="committee-badge" style="background: linear-gradient(135deg, <?php echo $committee_info['color']; ?> 0%, <?php echo $committee_info['color']; ?>CC 100%);">
                        <i class="<?php echo $committee_info['icon']; ?> me-1"></i>
                        <?php echo htmlspecialchars($committee_name); ?>: 
                        <strong><?php echo $committee['count']; ?></strong>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-card slide-in-right">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label text-white fw-semibold">
                        <i class="fas fa-users me-1"></i> Committee
                    </label>
                    <select class="form-select" name="filter_committee">
                        <option value="">All Committees</option>
                        <option value="Internal Affairs" <?php echo $filter_committee === 'Internal Affairs' ? 'selected' : ''; ?>>Internal Affairs</option>
                        <option value="External Affairs" <?php echo $filter_committee === 'External Affairs' ? 'selected' : ''; ?>>External Affairs</option>
                        <option value="Records and Documentation" <?php echo $filter_committee === 'Records and Documentation' ? 'selected' : ''; ?>>Records and Documentation</option>
                        <option value="Finance and Budget Management" <?php echo $filter_committee === 'Finance and Budget Management' ? 'selected' : ''; ?>>Finance and Budget Management</option>
                        <option value="Audit Planning and Risk Assessment" <?php echo $filter_committee === 'Audit Planning and Risk Assessment' ? 'selected' : ''; ?>>Audit Planning and Risk Assessment</option>
                        <option value="Operations and Implementation" <?php echo $filter_committee === 'Operations and Implementation' ? 'selected' : ''; ?>>Operations and Implementation</option>
                        <option value="Public Relations" <?php echo $filter_committee === 'Public Relations' ? 'selected' : ''; ?>>Public Relations</option>
                        <option value="Business Affairs and Procurement" <?php echo $filter_committee === 'Business Affairs and Procurement' ? 'selected' : ''; ?>>Business Affairs and Procurement</option>
                        <option value="Social and Environmental Awareness" <?php echo $filter_committee === 'Social and Environmental Awareness' ? 'selected' : ''; ?>>Social and Environmental Awareness</option>
                        <option value="Sports, Culture, and the Arts" <?php echo $filter_committee === 'Sports, Culture, and the Arts' ? 'selected' : ''; ?>>Sports, Culture, and the Arts</option>
                        <option value="Student Rights and Welfare" <?php echo $filter_committee === 'Student Rights and Welfare' ? 'selected' : ''; ?>>Student Rights and Welfare</option>
                        <option value="Council Officer" <?php echo $filter_committee === 'Council Officer' ? 'selected' : ''; ?>>Council Officer</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-white fw-semibold">
                        <i class="fas fa-calendar me-1"></i> Specific Date
                    </label>
                    <input type="date" class="form-control" name="filter_date" value="<?php echo htmlspecialchars($filter_date); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-white fw-semibold">
                        <i class="fas fa-calendar-week me-1"></i> Week
                    </label>
                    <input type="week" class="form-control" name="filter_week" value="<?php echo htmlspecialchars($filter_week); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-white fw-semibold">
                        <i class="fas fa-calendar-alt me-1"></i> Month
                    </label>
                    <input type="month" class="form-control" name="filter_month" value="<?php echo htmlspecialchars($filter_month); ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="btn-group w-100" role="group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <a href="?" class="btn btn-light">
                            <i class="fas fa-times me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Duty History -->
        <div class="card slide-in-left" style="margin-top: -5px;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>
                    Duty History
                </h5>
                <div class="btn-group">
                    <a href="?export=csv<?php echo ($filter_committee!==''?'&filter_committee='.urlencode($filter_committee):'') . ($filter_date!==''?'&filter_date='.urlencode($filter_date):'') . ($filter_month!==''?'&filter_month='.urlencode($filter_month):''); ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-download me-1"></i> Export CSV
                    </a>
                    <button class="btn btn-light btn-sm" onclick="location.reload()">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($duty_logs)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No Records Found</h5>
                        <p class="text-muted mb-0">Try adjusting your filters or check back later.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover text-center">
    <thead>
        <tr>
            <th><i class="fas fa-user me-1"></i> Name</th>
            <th><i class="fas fa-users me-1"></i> Committee</th>
            <th><i class="fas fa-crown me-1"></i> Position</th>
            <th><i class="fas fa-id-card me-1"></i> Student #</th>
            <th><i class="fas fa-calendar me-1"></i> Date</th>
            <th><i class="fas fa-clock me-1"></i> Time In</th>
            <th><i class="fas fa-clock me-1"></i> Time Out</th>
            <th><i class="fas fa-hourglass-half me-1"></i> Total Hours</th>
            <th><i class="fas fa-info-circle me-1"></i> Status</th>
            <th><i class="fas fa-cogs me-1"></i> Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($duty_logs as $log): 
            // Get committee color for row styling
            $committee_colors = [
                'Internal Affairs' => '#000000',
                'External Affairs' => '#FF0000',
                'Records and Documentation' => '#FFC300',
                'Finance and Budget Management' => '#008000',
                'Audit Planning and Risk Assessment' => '#7D7FB8',
                'Operations and Implementation' => '#FFA500',
                'Public Relations' => '#FF1493',
                'Business Affairs and Procurement' => '#40E0D0',
                'Social and Environmental Awareness' => '#A52A2A',
                'Sports, Culture, and the Arts' => '#0000FF',
                'Student Rights and Welfare' => '#85C1E9',
                'Council Officer' => '#BF6013',








            ];
            $committee_color = $committee_colors[$log['committee']] ?? '#AEB6BF';
        ?>
            <tr class="fade-in" style="border-left: 4px solid <?php echo $committee_color; ?>;">
                <td>
                    <a href="profile.php?student_number=<?php echo urlencode($log['student_number']); ?>" 
                       class="text-decoration-none fw-bold text-primary">
                        <?php echo htmlspecialchars($log['full_name']); ?>
                    </a>
                </td>
                <td>
                    <span class="badge" style="background: linear-gradient(135deg, <?php echo $committee_color; ?> 0%, <?php echo $committee_color; ?>CC 100%); color: white;">
                        <?php echo htmlspecialchars($log['committee']); ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($log['position']); ?></td>
                <td><code><?php echo htmlspecialchars($log['student_number']); ?></code></td>
                <td>
                    <?php 
                        $dutyDateRaw = $log['duty_date'] ?? '';
                        $dutyDateTs = $dutyDateRaw ? strtotime($dutyDateRaw) : false;
                        echo $dutyDateTs ? date('M d, Y', $dutyDateTs) : '-';
                    ?>
                </td>
                <td>
                    <?php 
                        $timeInRaw = $log['time_in'] ?? '';
                        $timeInTs = $timeInRaw ? strtotime($timeInRaw) : false;
                    ?>
                    <strong><?php echo $timeInTs ? date('g:i A', $timeInTs) : '-'; ?></strong>
                </td>
                <td>
                    <?php 
                        $timeOutRaw = $log['time_out'] ?? '';
                        $hasTimeOut = !empty($timeOutRaw);
                    ?>
                    <?php if ($hasTimeOut && strtotime($timeOutRaw)): ?>
                        <strong><?php echo date('g:i A', strtotime($timeOutRaw)); ?></strong>
                    <?php else: ?>
                        <span class="text-muted">Ongoing</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="fw-bold text-primary">
                        <?php 
                            $rawTotalHours = $log['total_hours'] ?? 0;
                            $totalHours = is_numeric($rawTotalHours) ? (float)$rawTotalHours : 0.0;
                            $total_seconds = (int)round($totalHours * 3600);
                            $hours = floor($total_seconds / 3600);
                            $minutes = floor(($total_seconds % 3600) / 60);
                            $seconds = floor($total_seconds % 60);
                            echo sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                        ?>
                    </span>
                </td>
                <td>
                    <?php 
                        $statusRaw = (string)($log['status'] ?? '');
                        $statusClass = strtolower(preg_replace('/[^a-z0-9]/i', '', $statusRaw));
                    ?>
                    <span class="status-badge status-<?php echo $statusClass; ?>">
                        <?php echo htmlspecialchars($statusRaw); ?>
                    </span>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <?php if ($log['status'] === 'Completed'): ?>
                            <a href="duty_receipt.php?log_id=<?php echo $log['log_id']; ?>" 
                            class="btn btn-info btn-sm" target="_blank">
                                <i class="fas fa-receipt me-1"></i> Receipt
                            </a>
                        <?php else: ?>
                            <span class="text-muted btn btn-sm">
                                <i class="fas fa-clock me-1"></i> In Progress
                            </span>
                        <?php endif; ?>
                        
                        <!-- Delete Duty Log Button -->
                        <button type="button" class="btn btn-danger btn-sm" 
                                onclick="confirmDeleteLog(<?php echo $log['log_id']; ?>)" 
                                title="Delete Duty Log">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
   
    <!-- Delete Log Confirmation Modal -->
<div class="modal fade" id="deleteLogModal" tabindex="-1" aria-labelledby="deleteLogModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteLogModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                    Confirm Delete Log
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this duty log?</p>
                <p class="text-danger">
                    <i class="fas fa-warning me-1"></i>
                    This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <form method="POST" action="delete_log.php" style="display: inline;">
                    <input type="hidden" name="log_id" id="deleteLogId">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete Log
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

 <!-- Sticky Footer -->
    <footer class="footer">
        <div class="container text-center">
            <hr class="footer-divider">
            <p class="footer-title mb-1">
                CEIT-SC Office Duty Tracker
            </p>
            <p class="footer-text mb-1">
                Designed and Developed by <strong>Railey Andrei O. Acosta</strong>, COSRAW AMBUSH, AY 2025-2026
            </p>
            <p class="footer-subtext mb-0">
                This system is officially endorsed by the <strong>College of Engineering and Infomation Technology - Student Council</strong>.<br>
                Valid for the entire term of AY 2025-2026.  
                <span class="d-block mt-1">© 2025 CEIT-SC. All Rights Reserved.</span>
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        
        // Function to confirm log deletion
function confirmDeleteLog(logId) {
    document.getElementById('deleteLogId').value = logId;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteLogModal'));
    deleteModal.show();
}

        // Add intersection observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all cards
        document.querySelectorAll('.card').forEach(card => {
            observer.observe(card);
        });

          // Function to toggle create account modal
        function toggleCreateAccountModal() {
            const modal = new bootstrap.Modal(document.getElementById('createAccountModal'));
            modal.show();
        }

        // Handle create account form submission
        document.getElementById('createAccountForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'create_account');
            
            fetch('admin_dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    bootstrap.Modal.getInstance(document.getElementById('createAccountModal')).hide();
                    this.reset();
                    location.reload(); // Refresh to show new user
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while creating the account.');
            });
        });

        // Function to confirm delete
        function confirmDelete(userId, userName) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('deleteUserName').textContent = userName;
            
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
    </script>
</body>
</html>

