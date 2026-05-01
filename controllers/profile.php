<?php
require_once 'config.php';

// Check if student number is provided
if (!isset($_GET['student_number'])) {
    header('Location: index.php');
    exit();
}

$student_number = sanitize_input($_GET['student_number']);
$profile_result = get_user_profile($student_number);

if (!$profile_result['success']) {
    $error_message = $profile_result['message'];
} else {
    $user = $profile_result['user'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - CEIT-SC Office Duty Tracker</title>
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
        
        .profile-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding: 1rem;
            background: rgba(191, 96, 19, 0.05);
            border-radius: 15px;
            border-left: 4px solid var(--ceit-primary);
        }
        
        .profile-label {
            font-weight: 600;
            color: var(--ceit-dark);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .profile-value {
            font-weight: 500;
            color: var(--ceit-primary);
            font-size: 1.1rem;
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
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(191, 96, 19, 0.4);
            background: var(--gradient-primary);
        }
        
        .btn-secondary {
            background: var(--gradient-secondary);
            color: white;
            box-shadow: 0 8px 25px rgba(28, 28, 28, 0.3);
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(28, 28, 28, 0.4);
            background: var(--gradient-secondary);
        }
        
        .alert {
            border: none;
            border-radius: 15px;
            padding: 1.2rem 1.5rem;
            font-weight: 500;
            box-shadow: var(--shadow-soft);
            position: relative;
            overflow: hidden;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, rgba(28, 28, 28, 0.1), rgba(46, 46, 46, 0.1));
            color: #1C1C1C;
            border-left: 4px solid #1C1C1C;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            margin: 0 auto 2rem;
            box-shadow: var(--shadow-medium);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="CEIT-SCLogo(White).png" alt="CEIT-SC Logo" class="logo">
                <span>CEIT-SC Office Duty Tracker</span>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a class="nav-link" href="admin_login.php">
                    <i class="fas fa-user-shield"></i> Admin Portal
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-5 py-4">
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
            <div class="text-center">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        <?php else: ?>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header text-center">
                            <h4 class="mb-0">
                                <i class="fas fa-user me-2"></i>User Profile
                            </h4>
                        </div>
                        <div class="card-body">
                            <!-- Profile Avatar -->
                            <div class="profile-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            
                            <!-- Profile Information -->
                            <div class="profile-info">
                                <span class="profile-label">Full Name</span>
                                <span class="profile-value"><?php echo htmlspecialchars($user['full_name']); ?></span>
                            </div>
                            
                            <div class="profile-info">
                                <span class="profile-label">Student Number</span>
                                <span class="profile-value"><?php echo htmlspecialchars($user['student_number']); ?></span>
                            </div>
                            
                            <div class="profile-info">
                                <span class="profile-label">Committee</span>
                                <span class="profile-value"><?php echo htmlspecialchars($user['committee']); ?></span>
                            </div>
                            
                            <div class="profile-info">
                                <span class="profile-label">Position</span>
                                <span class="profile-value"><?php echo htmlspecialchars($user['position']); ?></span>
                            </div>
                            
                            <div class="profile-info">
                                <span class="profile-label">Course, Year & Section</span>
                                <span class="profile-value"><?php echo htmlspecialchars($user['course_year_section']); ?></span>
                            </div>
                            
                            <div class="profile-info">
                                <span class="profile-label">Age</span>
                                <span class="profile-value"><?php echo htmlspecialchars($user['age']); ?> years old</span>
                            </div>
                            
                            <div class="profile-info">
                                <span class="profile-label">Contact Number</span>
                                <span class="profile-value"><?php echo htmlspecialchars($user['contact_number']); ?></span>
                            </div>
                            
                            <div class="profile-info">
                                <span class="profile-label">Address</span>
                                <span class="profile-value"><?php echo htmlspecialchars($user['address']); ?></span>
                            </div>
                            
                            <div class="profile-info">
                                <span class="profile-label">Member Since</span>
                                <span class="profile-value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="text-center mt-4">
                                <a href="index.php" class="btn btn-secondary me-3">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

