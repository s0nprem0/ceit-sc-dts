<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEIT-SC Office Duty Tracker</title>
    <link href="/assets/css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid px-5">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="assets/ceit-sc_blk.svg" width="64px" alt="CEIT-SC Logo" class="logo">
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
                        <div class="stats-number"><?php echo isset($officer_stats['total_officers']) ? $officer_stats['total_officers'] : 0; ?></div>
                        <h5 class="mb-0">Total Officers</h5>
                        <small class="opacity-75">Registered in System</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="fas fa-clock fa-4x mb-3"></i>
                        <div class="stats-number"><?php echo isset($officer_stats['active_duties']) ? $officer_stats['active_duties'] : 0; ?></div>
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
    <script src="assets/js/dashboard.js"></script>
</body>

</html>