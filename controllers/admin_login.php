<?php

require_once 'config.php';
require_once __DIR__ . '/../helpers/Utils.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';

$message = '';
$message_type = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = Utils::sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $result = AuthHelper::authenticateAdmin($username, $password);
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'error';

    if ($result['success']) {
        header('Location: admin_dashboard.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CEIT-SC Office Duty Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --ceit-orange: #BF6013;
            --ceit-light-orange: #F4A441;
            --ceit-dark: #1C1C1C;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1C1C1C 0%, #2E2E2E 50%, var(--ceit-orange) 100%);
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1; /* pushes footer to the bottom */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }

        .login-header {
            background: linear-gradient(135deg, var(--ceit-dark) 0%, #2E2E2E 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .logo {
            width: 60px;
            height: 60px;
            margin-bottom: 15px;
        }

        .login-body {
            padding: 30px;
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--ceit-orange);
            box-shadow: 0 0 0 0.2rem rgba(191, 96, 19, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--ceit-orange) 0%, var(--ceit-light-orange) 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(191, 96, 19, 0.4);
        }

        .alert {
            border-radius: 10px;
            border: none;
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
    <main>
        <div class="login-card">
            <div class="login-header">
                <img src="CEIT-SCLogo(White).png" alt="CEIT-SC Logo" class="logo">
                <h4>Admin Login</h4>
                <p class="mb-0">CEIT-SC Office Duty Tracker</p>
            </div>

            <div class="login-body">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> mb-4" role="alert">
                        <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-user"></i> Username
                        </label>
                        <input type="text" class="form-control" name="username" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <input type="password" class="form-control" name="password" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>

                <div class="text-center">
                    <a href="index.php" class="text-muted">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </main>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
