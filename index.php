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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEIT-SC Office Duty Tracker</title>
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
            --gradient-dark: linear-gradient(135deg, #1C1C1C 0%, #2E2E2E 100%);
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
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
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
            transition: all 0.3s ease;
        }
        
        .navbar-brand:hover {
            text-shadow: 0 4px 20px rgba(0,0,0,0.4);
        }
        
        .logo {
            width: 50px;
            height: 50px;
            margin-right: 15px;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
            transition: all 0.3s ease;
        }
        
        .logo:hover {
            transform: scale(1.05);
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
            box-shadow: var(--shadow-medium);
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
        
        .card-body {
            padding: 2rem;
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
        }
        
        .form-control {
            border: 2px solid rgba(191, 96, 19, 0.1);
            border-radius: 15px;
            padding: 12px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }
        
        .form-control:focus {
            border-color: var(--ceit-primary);
            box-shadow: 0 0 0 0.25rem rgba(191, 96, 19, 0.15);
            background: white;
        }
        
        .form-select {
            border: 2px solid rgba(191, 96, 19, 0.1);
            border-radius: 15px;
            padding: 12px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }
        
        .form-select:focus {
            border-color: var(--ceit-primary);
            box-shadow: 0 0 0 0.25rem rgba(191, 96, 19, 0.15);
            background: white;
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
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(191, 96, 19, 0.4);
            background: var(--gradient-primary);
        }
        
        .btn-success {
            background: var(--gradient-success);
            color: white;
            box-shadow: 0 8px 25px rgba(17, 153, 142, 0.3);
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(191, 96, 19, 0.4);
            background: var(--gradient-success);
        }
        
        .btn-danger {
            background: var(--gradient-danger);
            color: white;
            box-shadow: 0 8px 25px rgba(28, 28, 28, 0.3);
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(28, 28, 28, 0.4);
            background: var(--gradient-danger);
        }
        
        .btn-light {
            background: rgba(255, 255, 255, 0.9);
            color: var(--ceit-dark);
            backdrop-filter: blur(10px);
        }
        
        .btn-light:hover {
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
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
            transform: scale(1.02);
        }
        
        .status-ongoing {
            background: var(--gradient-success);
            color: white;
        }
        
        .status-completed {
            background: var(--gradient-dark);
            color: white;
        }
        
        .table {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-soft);
            background: white;
        }
        
        .table thead th {
            background: var(--gradient-dark);
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
        }
        
        .table tbody td {
            padding: 1rem;
            border-color: rgba(0,0,0,0.05);
            vertical-align: middle;
        }
        
        .alert {
            border: none;
            border-radius: 15px;
            padding: 1.2rem 1.5rem;
            font-weight: 500;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
            background: linear-gradient(
                135deg,
                #b02a37 0%,
                #dc3545 25%,
                #ff4d5e 50%,
                #dc3545 75%,
                #a71d2a 100%
            );
            background-size: 200% 200%;
            color: white;
            border-left: 4px solid #7a1c24;
            animation: metallic-shine 6s ease infinite;
        }

        .alert::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, #ff4d5e, #a71d2a);
        }

        @keyframes metallic-shine {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        
        .alert-success {
            background: var(--ceit-primary);
            color: white;
            border-left: 4px solid var(--ceit-primary);
        }
        
        .alert-danger {
            background: red;
            color: white;
            border-left: 4px solid red;
        }
        
        .alert .close-btn {
            position: absolute;
            top: 15px;
            right: 20px;
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            opacity: 0.8;
            transition: opacity 0.3s ease;
        }
        
        .alert .close-btn:hover {
            opacity: 1;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            opacity: 0.5;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 10px;
        }
        
        .real-time-timer {
            font-family: 'Courier New', monospace;
            font-size: 1.1rem;
            font-weight: bold;
            color: #28a745;
            background: rgba(40, 167, 69, 0.1);
            padding: 5px 10px;
            border-radius: 8px;
            border: 1px solid rgba(40, 167, 69, 0.3);
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
        <div class="container-fluid px-5">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="CEIT-SCLogo(White).png" alt="CEIT-SC Logo" class="logo">
                <span>CEIT-SC Office Duty Tracker</span>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="admin_login.php">
                    <i class="fas fa-user-shield"></i> Admin Portal
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-5 py-4">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> fade show" role="alert">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                <?php echo htmlspecialchars($message); ?>
                <button style="margin-top: 3px" type="button" class="close-btn" data-bs-dismiss="alert" aria-label="Close">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-4x mb-3"></i>
                        <div class="stats-number"><?php echo $officer_stats['total_officers']; ?></div>
                        <h5 class="mb-0">Total Officers</h5>
                        <small class="opacity-75">Registered in System</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="fas fa-clock fa-4x mb-3"></i>
                        <div class="stats-number"><?php echo $officer_stats['active_duties']; ?></div>
                        <h5 class="mb-0">Active Duties</h5>
                        <small class="opacity-75">Currently On Duty</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-3 mb-4" style="margin-top: -20px;">

                <!-- Log In for Duty Card -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Log In for Duty
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="loginForm">
                            <input type="hidden" name="action" value="log_in">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-id-card me-1"></i> Student Number *
                                </label>
                                <input type="text" class="form-control" name="student_number" required 
                                    placeholder="Enter your student number">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-key me-1"></i> Password (if required)
                                </label>
                                <input type="password" class="form-control" name="password" 
                                    placeholder="Enter password">
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-clock me-2"></i>
                                Start Duty
                            </button>
                        </form>
                    </div>
                </div>


                <!-- Guidelines Card -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            How to Use the Duty Tracker
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-check-circle me-2" style="color: black;"></i>
                                <strong>For Users:</strong> Log in using your <strong>student number</strong>. If you hold the AMBUSH position, your chairperson will enter your password on your behalf.
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle me-2" style="color: black;"></i>
                                <strong>Starting Duty:</strong> Press <strong>"Start Duty"</strong>. Duty hours will automatically start tracking upon login.
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle me-2" style="color: black;"></i>
                                <strong>Ending Duty:</strong> Press <strong>"End Duty"</strong> when your session is complete.
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-info-circle me-2" style="color: black;"></i>
                                <strong>Notes:</strong>
                                <ul class="mb-0 ms-4">
                                    <li>Duty hours are tracked automatically in real-time.</li>
                                    <li>The system calculates your total hours automatically.</li>
                                    <li>Only your chairperson can enter your password.</li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-9">
                <!-- Real-time Duty Tracker -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Real-time Duty Tracker
                        </h5>
                        <button class="btn btn-light btn-sm" onclick="location.reload()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if (empty($duty_logs)): ?>
                            <div class="empty-state">
                                <i class="fas fa-clipboard-list"></i>
                                <h5 class="text-muted">No Active Duties Today</h5>
                                <p class="text-muted mb-0">Officers will appear here when they log in for duty.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive" style="max-height: none; overflow-y: visible;">
                                <table class="table table-hover text-center">
    <thead>
        <tr>
            <th><i class="fas fa-user me-1"></i> OFFICER</th>
            <th><i class="fas fa-users me-1"></i> COMMITTEE</th>
            <th><i class="fas fa-crown me-1"></i> POSITION</th>
            <th><i class="fas fa-clock me-1"></i> TIME IN</th>
            <th><i class="fas fa-clock me-1"></i> TIME OUT</th>
            <th><i class="fas fa-stopwatch me-1"></i> HOURS</th>
            <th><i class="fas fa-info-circle me-1"></i> STATUS</th>
            <th><i class="fas fa-cogs me-1"></i> ACTIONS</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($duty_logs as $log): 
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
            $committee_info = $committee_colors[$log['committee']] ?? ['color' => '#AEB6BF', 'icon' => 'fas fa-users'];
        ?>
            <tr style="border-left: 4px solid <?php echo $committee_info['color']; ?>;">
                <td>
                    <div class="d-flex align-items-center justify-content-center">
                        <div class="avatar-circle me-3">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <div class="fw-semibold"><?php echo htmlspecialchars($log['full_name']); ?></div>
                            <small class="text-muted"><?php echo htmlspecialchars($log['student_number']); ?></small>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge" style="background: linear-gradient(135deg, <?php echo $committee_info['color']; ?> 0%, <?php echo $committee_info['color']; ?>CC 100%); color: white;">
                        <i class="<?php echo $committee_info['icon']; ?> me-1"></i>
                        <?php echo htmlspecialchars($log['committee']); ?>
                    </span>
                </td>
                <td>
                    <span class="badge bg-primary"><?php echo htmlspecialchars($log['position']); ?></span>
                </td>
                <td>
                    <i class="fas fa-clock me-1 text-success"></i>
                    <?php echo date('g:i A', strtotime($log['time_in'])); ?>
                </td>
                <td>
                    <?php if ($log['time_out']): ?>
                        <i class="fas fa-clock me-1 text-danger"></i>
                        <?php echo date('g:i A', strtotime($log['time_out'])); ?>
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($log['status'] === 'Ongoing'): ?>
                        <span class="real-time-timer" data-start-time="<?php echo $log['duty_date'] . ' ' . $log['time_in']; ?>">
                            <?php echo format_hours_minutes_seconds($log['total_hours']); ?>
                        </span>
                    <?php else: ?>
                        <span class="fw-semibold"><?php echo format_hours_minutes($log['total_hours']); ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="status-badge status-<?php echo strtolower($log['status']); ?>">
                        <?php echo $log['status']; ?>
                    </span>
                </td>
                <td>
                    <?php if ($log['status'] === 'Ongoing'): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="log_out">
                            <input type="hidden" name="log_id" value="<?php echo $log['log_id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" 
                                    onclick="return confirm('Are you sure you want to end your duty?')">
                                <i class="fas fa-stop me-1"></i> End Duty
                            </button>
                        </form>
                    <?php else: ?>
                        <span class="text-muted">Completed</span>
                    <?php endif; ?>
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
        // Real-time timer update
        function updateTimers() {
            const timers = document.querySelectorAll('.real-time-timer');
            timers.forEach(timer => {
                const startTime = new Date(timer.getAttribute('data-start-time'));
                const now = new Date();
                const diff = now - startTime;
                
                const hours = Math.floor(diff / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                
                timer.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            });
        }

        // Update timers every second
        setInterval(updateTimers, 1000);
        
        // Initial update
        updateTimers();

        // Auto-refresh page every 30 seconds to get new data
        // setInterval(() => {
        //    location.reload();
        //}, 30000);

        // Form focus effects
        document.querySelectorAll('.form-control, .form-select').forEach(element => {
            element.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            element.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });
    </script>
</body>
</html>

