<?php
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Get filter parameters
$filter_type = $_GET['filter_type'] ?? 'all';
$filter_value = $_GET['filter_value'] ?? '';

// Get committee statistics with hours
$committee_stats = get_committee_stats();
$committee_hours = [];

foreach ($committee_stats as $committee) {
    $committee_name = $committee['committee'];
    $total_hours = compute_committee_duty_hours($committee_name, $filter_type, $filter_value);
    $committee_hours[] = [
        'committee' => $committee_name,
        'officer_count' => $committee['count'],
        'total_hours' => $total_hours
    ];
}

// Sort by total hours descending
usort($committee_hours, function($a, $b) {
    return $b['total_hours'] <=> $a['total_hours'];
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Committee Hours - CEIT-SC Office Duty Tracker</title>
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
            --shadow-soft: 0 4px 15px rgba(0,0,0,0.1);
            --shadow-medium: 0 6px 20px rgba(0,0,0,0.15);
            --shadow-strong: 0 8px 25px rgba(0,0,0,0.2);
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
        
        .card-header {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 1.5rem;
            border-radius: 25px 25px 0 0 !important;
            position: relative;
            overflow: hidden;
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
        
        .filter-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .committee-hours-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: var(--shadow-soft);
            transition: all 0.3s ease;
        }
        
        .committee-hours-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
        }
        
        .hours-display {
            font-size: 2rem;
            font-weight: 800;
            color: var(--ceit-primary);
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid mt-4 mx-5">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="CEIT-SCLogo(White).png" alt="CEIT-SC Logo" class="logo">
                <span>Committee Hours Report</span>
            </a>
            <div class="navbar-nav ms-auto" style="margin-right: 5px;">
                <a class="nav-link" href="admin_dashboard.php">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <a class="nav-link" href="admin_logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4 px-5">
        <!-- Filters -->
        <div class="filter-card fade-in">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label text-white fw-semibold">
                        <i class="fas fa-filter me-1"></i> Filter Type
                    </label>
                    <select class="form-select" name="filter_type">
                        <option value="all" <?php echo $filter_type === 'all' ? 'selected' : ''; ?>>All Time</option>
                        <option value="daily" <?php echo $filter_type === 'daily' ? 'selected' : ''; ?>>Daily</option>
                        <option value="weekly" <?php echo $filter_type === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                        <option value="monthly" <?php echo $filter_type === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-white fw-semibold">
                        <i class="fas fa-calendar me-1"></i> Filter Value
                    </label>
                    <?php if ($filter_type === 'daily'): ?>
                        <input type="date" class="form-control" name="filter_value" value="<?php echo htmlspecialchars($filter_value); ?>">
                    <?php elseif ($filter_type === 'weekly'): ?>
                        <input type="week" class="form-control" name="filter_value" value="<?php echo htmlspecialchars($filter_value); ?>">
                    <?php elseif ($filter_type === 'monthly'): ?>
                        <input type="month" class="form-control" name="filter_value" value="<?php echo htmlspecialchars($filter_value); ?>">
                    <?php else: ?>
                        <input type="text" class="form-control" name="filter_value" value="<?php echo htmlspecialchars($filter_value); ?>" placeholder="Not applicable for 'All Time'" readonly>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="btn-group w-100" role="group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Generate Report
                        </button>
                        <a href="?" class="btn btn-light">
                            <i class="fas fa-times me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Committee Hours Report -->
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Committee Duty Hours Report
                    <?php if ($filter_type !== 'all'): ?>
                        - <?php echo ucfirst($filter_type); ?>
                        <?php if (!empty($filter_value)): ?>
                            (<?php echo htmlspecialchars($filter_value); ?>)
                        <?php endif; ?>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($committee_hours)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-chart-bar fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No Data Available</h5>
                        <p class="text-muted mb-0">No committee data found for the selected filters.</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php 
                        // Committee color mapping
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
                        
                        foreach ($committee_hours as $committee): 
                            $committee_info = $committee_colors[$committee['committee']] ?? ['color' => '#AEB6BF', 'icon' => 'fas fa-users'];
                        ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="committee-hours-card" style="border-left: 5px solid <?php echo $committee_info['color']; ?>;">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">
                                                <i class="<?php echo $committee_info['icon']; ?> me-2" style="color: <?php echo $committee_info['color']; ?>;"></i>
                                                <?php echo htmlspecialchars($committee['committee']); ?>
                                            </h6>
                                            <small class="text-muted"><?php echo $committee['officer_count']; ?> officers</small>
                                        </div>
                                        <div class="text-end">
                                            <div class="hours-display"><?php echo number_format($committee['total_hours'], 1); ?></div>
                                            <small class="text-muted">hours</small>
                                        </div>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar" 
                                             style="background: linear-gradient(90deg, <?php echo $committee_info['color']; ?>, <?php echo $committee_info['color']; ?>CC); width: <?php echo $committee_hours[0]['total_hours'] > 0 ? ($committee['total_hours'] / $committee_hours[0]['total_hours']) * 100 : 0; ?>%;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Summary Table -->
                    <div class="table-responsive mt-4">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-users me-1"></i> Committee</th>
                                    <th><i class="fas fa-user me-1"></i> Officers</th>
                                    <th><i class="fas fa-clock me-1"></i> Total Hours</th>
                                    <th><i class="fas fa-calculator me-1"></i> Avg Hours/Officer</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($committee_hours as $committee): 
                                    $committee_info = $committee_colors[$committee['committee']] ?? ['color' => '#AEB6BF', 'icon' => 'fas fa-users'];
                                    $avg_hours = $committee['officer_count'] > 0 ? $committee['total_hours'] / $committee['officer_count'] : 0;
                                ?>
                                    <tr style="border-left: 4px solid <?php echo $committee_info['color']; ?>;">
                                        <td>
                                            <i class="<?php echo $committee_info['icon']; ?> me-2" style="color: <?php echo $committee_info['color']; ?>;"></i>
                                            <strong><?php echo htmlspecialchars($committee['committee']); ?></strong>
                                        </td>
                                        <td><?php echo $committee['officer_count']; ?></td>
                                        <td><strong><?php echo number_format($committee['total_hours'], 2); ?> hrs</strong></td>
                                        <td><?php echo number_format($avg_hours, 2); ?> hrs</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update filter value input based on filter type
        document.querySelector('select[name="filter_type"]').addEventListener('change', function() {
            const filterValue = document.querySelector('input[name="filter_value"]');
            const filterType = this.value;
            
            switch(filterType) {
                case 'daily':
                    filterValue.type = 'date';
                    filterValue.placeholder = 'Select date';
                    filterValue.removeAttribute('readonly');
                    break;
                case 'weekly':
                    filterValue.type = 'week';
                    filterValue.placeholder = 'Select week';
                    filterValue.removeAttribute('readonly');
                    break;
                case 'monthly':
                    filterValue.type = 'month';
                    filterValue.placeholder = 'Select month';
                    filterValue.removeAttribute('readonly');
                    break;
                default:
                    filterValue.type = 'text';
                    filterValue.placeholder = 'Not applicable for "All Time"';
                    filterValue.setAttribute('readonly', 'readonly');
                    filterValue.value = '';
                    break;
            }
        });
    </script>
</body>
</html>

