<?php
require_once 'config.php';

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
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
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CEIT-SC Officer Account Creation</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Inter', sans-serif;
        background: linear-gradient(135deg, #1C1C1C 0%, #2E2E2E 50%, #BF6013 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 30px 15px;
    }
    .card {
        background: rgba(255,255,255,0.97);
        backdrop-filter: blur(20px);
        border: none;
        border-radius: 25px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(0,0,0,0.2);
    }
    h3 {
        font-weight: 700;
        color: #1C1C1C;
    }
    .gradient-icon {
        background: linear-gradient(135deg, #BF6013, #F4A441);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .form-buttons {
        display: flex;
        justify-content: center; /* centers horizontally */
        gap: 15px; /* spacing between buttons */
        margin-top: 20px;
    }
    .form-control, .form-select {
        border-radius: 15px;
        padding: 12px 18px;
        background: rgba(255,255,255,0.9);
        border: 1px solid #ddd;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: #BF6013;
        box-shadow: 0 0 8px rgba(191,96,19,0.3);
    }
    .btn {
        border-radius: 15px;
        padding: 12px 30px;
        font-weight: 600;
        transition: transform 0.2s, box-shadow 0.2s;
        border: none;
    }
    .btn-primary {
        background: linear-gradient(135deg, #BF6013 0%, #F4A441 100%);
        color: #fff;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 14px rgba(191,96,19,0.4);
    }
    .btn-secondary {
        background: linear-gradient(135deg, #6c757d, #a1a5a8);
        color: #fff;
    }
    .btn-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    }
    .btn-custom {
        min-width: 220px; /* same size */
        text-align: center;
    }

    .alert {
        border: none;
        border-radius: 15px;
        padding: 1.2rem 1.5rem;
        font-weight: 500;
        font-size: 0.95rem;
    }
    .form-label {
        font-weight: 600;
        margin-bottom: 6px;
        color: #333;
    }
    .text-center-btn {
        text-align: center;
        margin-top: 15px;
    }
</style>
</head>
<body>

<div class="container">
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> fade show" role="alert">
            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="card p-5">
                <h3 class="mb-4 text-center">
                    <i class="fas fa-user-plus me-2 gradient-icon"></i>
                    Create Officer Account
                </h3>
                <form method="POST">
                    <input type="hidden" name="action" value="register">

                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Student Number *</label>
                        <input type="text" class="form-control" name="student_number" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Committee *</label>
                        <select class="form-select" name="committee" required>
                            <option value="">Select Committee</option>
                            <option value="Internal Affairs">Internal Affairs</option>
                            <option value="External Affairs">External Affairs</option>
                            <option value="Records and Documentation">Records and Documentation</option>
                            <option value="Finance and Budget Management">Finance and Budget Management</option>
                            <option value="Audit Planning and Risk Assessment">Audit Planning and Risk Assessment</option>
                            <option value="Operations and Implementation">Operations and Implementation</option>
                            <option value="Public Relations">Public Relations</option>
                            <option value="Business Affairs and Procurement">Business Affairs and Procurement</option>
                            <option value="Social and Environmental Awareness">Social and Environmental Awareness</option>
                            <option value="Sports, Culture, and the Arts">Sports, Culture, and the Arts</option>
                            <option value="Student Rights and Welfare">Student Rights and Welfare</option>
                            <option value="Council Officer">Council Officer</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Position *</label>
                        <select class="form-select" name="position" required>
                            <option value="">Select Position</option>
                            <option value="Adviser">Adviser</option>
                            <option value="President">President</option>
                            <option value="Chairperson">Chairperson</option>
                            <option value="Vice Chairperson">Vice Chairperson</option>
                            <option value="Secretary General">Secretary General</option>
                            <option value="Assistant Secretary General">Assistant Secretary General</option>
                            <option value="AMBUSH">AMBUSH</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Course, Year, and Section *</label>
                        <input type="text" class="form-control" name="course_year_section" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Age *</label>
                        <input type="number" class="form-control" name="age" min="16" max="50" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-control" name="contact_number">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" rows="3"></textarea>
                    </div>

                    <div class="form-buttons text-center">
                        <button type="submit" class="btn btn-primary btn-custom me-2">
                            <i class="fas fa-user-plus me-2"></i> Create Account
                        </button>

                        <a href="admin_dashboard.php" class="btn btn-secondary btn-custom">
                            <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                        </a>
                    </div>


                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>