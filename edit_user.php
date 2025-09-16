<?php
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $full_name = sanitize_input($_POST['full_name']);
    $student_number = sanitize_input($_POST['student_number']);
    $committee = sanitize_input($_POST['committee']);
    $position = sanitize_input($_POST['position']);
    $course_year_section = sanitize_input($_POST['course_year_section']);
    $age = intval($_POST['age']);
    $contact_number = sanitize_input($_POST['contact_number']);
    $address = sanitize_input($_POST['address']);
    
    $result = update_user($user_id, $full_name, $student_number, $committee, $position, $course_year_section, $age, $contact_number, $address);
    
    if ($result['success']) {
        $message = $result['message'];
        $message_type = 'success';
        // Redirect to admin dashboard after successful update
        header('Location: admin_dashboard.php?message=' . urlencode($message) . '&type=success');
        exit();
    } else {
        $message = $result['message'];
        $message_type = 'danger';
    }
}

// Get user information
$user_id = intval($_GET['id'] ?? 0);
if ($user_id <= 0) {
    header('Location: admin_dashboard.php');
    exit();
}

$user_result = get_user_by_id($user_id);
if (!$user_result['success']) {
    header('Location: admin_dashboard.php?message=' . urlencode($user_result['message']) . '&type=danger');
    exit();
}

$user = $user_result['user'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - CEIT-SC Office Duty Tracker</title>
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
        
        .btn-secondary {
            background: var(--gradient-secondary);
            color: white;
            box-shadow: 0 8px 25px rgba(28, 28, 28, 0.3);
        }
        
        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(28, 28, 28, 0.4);
            background: var(--gradient-secondary);
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
                <span>Edit User</span>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="admin_dashboard.php">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <a class="nav-link" href="admin_logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Alert Messages -->
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Edit User Form -->
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-edit me-2"></i>
                            Edit User Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="full_name" class="form-label">
                                        <i class="fas fa-user me-1"></i> Full Name
                                    </label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="student_number" class="form-label">
                                        <i class="fas fa-id-card me-1"></i> Student Number
                                    </label>
                                    <input type="text" class="form-control" id="student_number" name="student_number" 
                                           value="<?php echo htmlspecialchars($user['student_number']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="committee" class="form-label">
                                        <i class="fas fa-users me-1"></i> Committee
                                    </label>
                                    <select class="form-select" id="committee" name="committee" required>
                                        <option value="">Select Committee</option>
                                        <option value="Internal Affairs" <?php echo $user['committee'] === 'Internal Affairs' ? 'selected' : ''; ?>>Internal Affairs</option>
                                        <option value="External Affairs" <?php echo $user['committee'] === 'External Affairs' ? 'selected' : ''; ?>>External Affairs</option>
                                        <option value="Records and Documentation" <?php echo $user['committee'] === 'Records and Documentation' ? 'selected' : ''; ?>>Records and Documentation</option>
                                        <option value="Finance and Budget Management" <?php echo $user['committee'] === 'Finance and Budget Management' ? 'selected' : ''; ?>>Finance and Budget Management</option>
                                        <option value="Audit Planning and Risk Assessment" <?php echo $user['committee'] === 'Audit Planning and Risk Assessment' ? 'selected' : ''; ?>>Audit Planning and Risk Assessment</option>
                                        <option value="Operations and Implementation" <?php echo $user['committee'] === 'Operations and Implementation' ? 'selected' : ''; ?>>Operations and Implementation</option>
                                        <option value="Public Relations" <?php echo $user['committee'] === 'Public Relations' ? 'selected' : ''; ?>>Public Relations</option>
                                        <option value="Business Affairs and Procurement" <?php echo $user['committee'] === 'Business Affairs and Procurement' ? 'selected' : ''; ?>>Business Affairs and Procurement</option>
                                        <option value="Social and Environmental Awareness" <?php echo $user['committee'] === 'Social and Environmental Awareness' ? 'selected' : ''; ?>>Social and Environmental Awareness</option>
                                        <option value="Sports, Culture, and the Arts" <?php echo $user['committee'] === 'Sports, Culture, and the Arts' ? 'selected' : ''; ?>>Sports, Culture, and the Arts</option>
                                        <option value="Student Rights and Welfare" <?php echo $user['committee'] === 'Student Rights and Welfare' ? 'selected' : ''; ?>>Student Rights and Welfare</option>
                                        <option value="Council Officer" <?php echo $user['committee'] === 'Council Officer' ? 'selected' : ''; ?>>Council Officer</option>
                                        <option value="Others" <?php echo $user['committee'] === 'Others' ? 'selected' : ''; ?>>Others</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="position" class="form-label">
                                        <i class="fas fa-crown me-1"></i> Position
                                    </label>
                                    <input type="text" class="form-control" id="position" name="position" 
                                           value="<?php echo htmlspecialchars($user['position']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="course_year_section" class="form-label">
                                        <i class="fas fa-graduation-cap me-1"></i> Course, Year & Section
                                    </label>
                                    <input type="text" class="form-control" id="course_year_section" name="course_year_section" 
                                           value="<?php echo htmlspecialchars($user['course_year_section']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="age" class="form-label">
                                        <i class="fas fa-calendar-alt me-1"></i> Age
                                    </label>
                                    <input type="number" class="form-control" id="age" name="age" min="16" max="100" 
                                           value="<?php echo $user['age']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="contact_number" class="form-label">
                                        <i class="fas fa-phone me-1"></i> Contact Number
                                    </label>
                                    <input type="text" class="form-control" id="contact_number" name="contact_number" 
                                           value="<?php echo htmlspecialchars($user['contact_number']); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="address" class="form-label">
                                        <i class="fas fa-map-marker-alt me-1"></i> Address
                                    </label>
                                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="admin_dashboard.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Update User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

