<?php
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

$users = get_all_users();

// Apply committee filter if selected
$filter_committee = $_GET['filter_committee'] ?? '';

if (!empty($filter_committee)) {
    $users = array_filter($users, function ($user) use ($filter_committee) {
        return isset($user['committee']) && $user['committee'] === $filter_committee;
    });
}


// Handle messages from redirects
$message = $_GET['message'] ?? '';
$message_type = $_GET['type'] ?? '';
$filter_committee = $_GET['filter_committee'] ?? '';


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - CEIT-SC Office Duty Tracker</title>
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
        
        .btn {
            border: none;
            border-radius: 15px;
            padding: 8px 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            font-size: 0.8rem;
        }
        
        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 15px rgba(191, 96, 19, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(191, 96, 19, 0.4);
            background: var(--gradient-primary);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4);
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            color: #212529;
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
        }
        
        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 193, 7, 0.4);
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            color: #212529;
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
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
    <div class="container-fluid mt-4 mx-5">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="CEIT-SCLogo(White).png" alt="CEIT-SC Logo" class="logo">
            <span>Manage Users</span>
        </a>
        <div class="navbar-nav ms-auto d-flex align-items-center">
            <a class="nav-link" href="create_users.php" style="margin-right: 5px;">
                    <i class="fas fa-user-plus"></i> Create Users
                </a>
            <form method="POST" action="clear_officers.php" 
                onsubmit="return confirm('This will delete ALL officers and their duty records. Continue?');" 
                style="display: inline;">
                <button type="submit" class="nav-link" style="margin-right: 5px;"
                        style="padding: 0; border: none; background: none; cursor: pointer;">
                    <i class="fas fa-user-slash"></i> Clear All Accounts
                </button>
            </form>
            <a class="nav-link" href="admin_dashboard.php" style="margin-right: 5px;">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <a class="nav-link" href="admin_logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</nav>


    <div class="container-fluid mt-4 px-5">
        <!-- Alert Messages -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
    <h5 class="mb-3 mb-md-0">
        <i class="fas fa-users me-2"></i>
        All Users (<?php echo count($users); ?>)
    </h5>
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-8">
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
                <option value="Others" <?php echo $filter_committee === 'Others' ? 'selected' : ''; ?>>Others</option>
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <div class="btn-group w-100" role="group">
                <button type="submit" class="btn btn-light">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
            </div>
        </div>
    </form>
</div>

            <div class="card-body">
                <?php if (empty($users)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No Users Found</h5>
                        <p class="text-muted mb-0">No users are registered in the system yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-user me-1"></i> Name</th>
                                    <th><i class="fas fa-id-card me-1"></i> Student #</th>
                                    <th><i class="fas fa-users me-1"></i> Committee</th>
                                    <th><i class="fas fa-crown me-1"></i> Position</th>
                                    <th><i class="fas fa-graduation-cap me-1"></i> Course</th>
                                    <th><i class="fas fa-calendar-alt me-1"></i> Age</th>
                                    <th><i class="fas fa-phone me-1"></i> Contact</th>
                                    <th><i class="fas fa-cogs me-1"></i> Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $committee_colors = [
                                    'Internal Affairs' => '#000000',
                                    'External Affairs' => '#FF0000',
                                    'Records and Documentation' => '#FFFF00',
                                    'Finance and Budget Management' => '#008000',
                                    'Audit Planning and Risk Assessment' => '#9C9ECF',
                                    'Operations and Implementation' => '#FFA500',
                                    'Public Relations' => '#FFC0CB',
                                    'Business Affairs and Procurement' => '#40E0D0',
                                    'Social and Environmental Awareness' => '#A52A2A',
                                    'Sports, Culture, and the Arts' => '#800080',
                                    'Student Rights and Welfare' => '#0000FF',
                                    'Council Officer' => '#BF6013'
                                ];
                                ?>
                                <?php foreach ($users as $user): 
                                    $committee_name = $user['committee'] ?? '';
                                    $committee_color = $committee_colors[$committee_name] ?? '#AEB6BF';
                                ?>
                                    <tr class="fade-in" style="border-left: 4px solid <?php echo $committee_color; ?>;">
                                        <td>
                                            <a href="profile.php?student_number=<?php echo urlencode($user['student_number']); ?>" class="text-decoration-none fw-bold text-primary">
                                                <?php echo htmlspecialchars($user['full_name']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($user['student_number']); ?>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: linear-gradient(135deg, <?php echo $committee_color; ?> 0%, <?php echo $committee_color; ?>CC 100%); color: white;">
                                                <?php echo htmlspecialchars($user['committee']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['position']); ?></td>
                                        <td><?php echo htmlspecialchars($user['course_year_section']); ?></td>
                                        <td><?php echo $user['age']; ?></td>
                                        <td><?php echo htmlspecialchars($user['contact_number'] ?? 'N/A'); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" 
                                                   class="btn btn-warning btn-sm btn-action">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <button type="button" class="btn btn-danger btn-sm btn-action" 
                                                        onclick="confirmDelete(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['full_name'], ENT_QUOTES); ?>')">
                                                    <i class="fas fa-trash"></i> Delete
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                        Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the user <strong id="deleteUserName"></strong>?</p>
                    <p class="text-danger">
                        <i class="fas fa-warning me-1"></i>
                        This action cannot be undone and will also delete all duty records associated with this user.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <form method="POST" action="delete_user.php" style="display: inline;">
                        <input type="hidden" name="user_id" id="deleteUserId">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i> Delete User
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(userId, userName) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('deleteUserName').textContent = userName;
            
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
    </script>
</body>
</html>
