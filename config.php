<?php
// CEIT-SC Office Duty Tracker - Database Configuration (Updated)
$servername = "localhost";
$username = "root";
$password = "";  // Default XAMPP MySQL password is empty
$dbname = "ceit_sc_duty_tracker";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    // Database created successfully or already exists
} else {
    echo "Error creating database: " . $conn->error;
}

// Close connection and reconnect to the database
$conn->close();

// Reconnect to the specific database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

date_default_timezone_set("Asia/Manila");

// Start session
session_start();

// Helper function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to check if user exists
function user_exists($student_number) {
    global $conn;
    $stmt = $conn->prepare("SELECT student_number FROM users WHERE student_number = ?");
    $stmt->bind_param("s", $student_number);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Function to register a new user
function register_user($full_name, $student_number, $committee, $position, $course_year_section, $age, $contact_number, $address) {
    global $conn;

    // Check if user already exists
    if (user_exists($student_number)) {
        return array('success' => false, 'message' => 'Student number already exists');
    }

    $stmt = $conn->prepare("INSERT INTO users (full_name, student_number, committee, position, course_year_section, age, contact_number, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssiss", $full_name, $student_number, $committee, $position, $course_year_section, $age, $contact_number, $address);

    if ($stmt->execute()) {
        return array('success' => true, 'message' => 'User registered successfully');
    } else {
        return array('success' => false, 'message' => 'Error registering user: ' . $conn->error);
    }
}

// Function to log in for duty - automatically starts duty tracking
function log_in_duty($student_number, $password = null) {
    global $conn;

    // Get user information
    $stmt = $conn->prepare("SELECT * FROM users WHERE student_number = ?");
    $stmt->bind_param("s", $student_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return array('success' => false, 'message' => 'User not found');
    }

    $user = $result->fetch_assoc();
    $position = $user['position'];

    // Define positions that require a password
    $password_required_positions = ['AMBUSH', 'Vice Chairperson', 'Secretary General', 'Assistant Secretary General'];
    $default_password = '081825_ceitsc';

    // Check if password is required for the user's position
    if (in_array($position, $password_required_positions)) {
        if (empty($password) || $password !== $default_password) {
            return array('success' => false, 'message' => 'Password required for this position or incorrect password');
        }
    }

    // Check if user already has an active duty today
    $stmt = $conn->prepare("SELECT log_id FROM duty_logs WHERE student_number = ? AND status = 'Ongoing' AND duty_date = CURDATE()");
    $stmt->bind_param("s", $student_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return array('success' => false, 'message' => 'You already have an active duty today');
    }

    // Create new duty log - automatically starts duty tracking
    $stmt = $conn->prepare("INSERT INTO duty_logs (student_number, full_name, committee, position, duty_date, time_in, total_hours, status) VALUES (?, ?, ?, ?, CURDATE(), NOW(), 0, 'Ongoing')");
    $stmt->bind_param("ssss", $student_number, $user['full_name'], $user['committee'], $user['position']);

    if ($stmt->execute()) {
        return array('success' => true, 'message' => 'Successfully logged in for duty - tracking started automatically', 'log_id' => $conn->insert_id);
    } else {
        return array('success' => false, 'message' => 'Failed to log in for duty: ' . $conn->error);
    }
}

// Function to log out from duty - calculates total hours (Time In - Time Out)
function log_out_duty($log_id) {
    global $conn;

    // Get duty log information
    $stmt = $conn->prepare("SELECT * FROM duty_logs WHERE log_id = ? AND status = 'Ongoing'");
    $stmt->bind_param("i", $log_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return array('success' => false, 'message' => 'Duty log not found or already completed');
    }

    $duty_log = $result->fetch_assoc();

    // Calculate total duty hours (Time In - Time Out)
    $time_in = new DateTime($duty_log['duty_date'] . ' ' . $duty_log['time_in']);
    $time_out = new DateTime();

    // Calculate the difference in minutes
    $interval = $time_in->diff($time_out);
    $total_minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i + ($interval->s / 60);

    // Convert to hours (minimum 0)
    $total_hours = max(0, $total_minutes / 60);

    // Update duty log with time out and completion
    $stmt = $conn->prepare("UPDATE duty_logs SET time_out = ?, total_hours = ?, status = 'Completed' WHERE log_id = ?");
    $time_out_str = $time_out->format('H:i:s');
    $stmt->bind_param("sdi", $time_out_str, $total_hours, $log_id);

    if ($stmt->execute()) {
        return array('success' => true, 'message' => 'Successfully logged out from duty', 'total_hours' => $total_hours, 'log_id' => $log_id, 'redirect_to_receipt' => true);
    } else {
        return array('success' => false, 'message' => 'Failed to log out from duty: ' . $conn->error);
    }
}

// Function to get duty logs with real-time tracking
function get_duty_logs() {
    global $conn;

    $sql = "SELECT log_id, student_number, full_name, committee, position, duty_date, time_in, time_out, total_hours, status
            FROM duty_logs
            WHERE duty_date = CURDATE()
            ORDER BY time_in DESC";

    $result = $conn->query($sql);
    $duty_logs = array();

    while ($row = $result->fetch_assoc()) {
        if ($row['status'] === 'Ongoing') {
            // Calculate real-time hours for ongoing duties
            $time_in = new DateTime($row['duty_date'] . ' ' . $row['time_in']);
            $current_time = new DateTime();

            // Calculate the difference using DateInterval
            $interval = $time_in->diff($current_time);
            $total_minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i + ($interval->s / 60);
            $row['total_hours'] = max(0, $total_minutes / 60);
        } else {
            // For completed duties, use the stored total_hours value
            $row['total_hours'] = (float)$row['total_hours'];
        }

        $duty_logs[] = $row;
    }

    return $duty_logs;
}

// Function to get officer statistics
function get_officer_stats() {
    global $conn;

    // Get total number of officers
    $result = $conn->query("SELECT COUNT(*) as total_officers FROM users");
    $total_officers = $result->fetch_assoc()['total_officers'];

    // Get number of active duties today
    $result = $conn->query("SELECT COUNT(*) as active_duties FROM duty_logs WHERE duty_date = CURDATE() AND status = 'Ongoing'");
    $active_duties = $result->fetch_assoc()['active_duties'];

    return array('total_officers' => $total_officers, 'active_duties' => $active_duties);
}

// Function to get committee statistics
function get_committee_stats() {
    global $conn;

    // Get regular committee stats (excluding Council Officers for now)
    $sql = "SELECT committee, COUNT(*) as count
            FROM users
            WHERE committee != 'Council Officer'
            GROUP BY committee
            ORDER BY committee ASC";

    $result = $conn->query($sql);
    $committee_stats = array();

    while ($row = $result->fetch_assoc()) {
        $committee_stats[] = $row;
    }

    // Add Council Officers count (Adviser, President, Chairperson regardless of committee)
    $council_sql = "SELECT COUNT(*) as count
                    FROM users
                    WHERE position IN ('Adviser', 'President', 'Chairperson')";

    $council_result = $conn->query($council_sql);
    $council_row = $council_result->fetch_assoc();

    if ($council_row['count'] > 0) {
        $committee_stats[] = array(
            'committee' => 'Council Officer',
            'count' => $council_row['count']
        );
    }

    return $committee_stats;
}

// Function to get user profile
function get_user_profile($student_number) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM users WHERE student_number = ?");
    $stmt->bind_param("s", $student_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return array('success' => false, 'message' => 'User not found');
    }

    $user = $result->fetch_assoc();
    return array('success' => true, 'user' => $user);
}

// Function to authenticate admin
function authenticate_admin($username, $password) {
    // Simple admin authentication (for demo purposes)
    if ($username === 'CEIT_SC2026' && $password === '081825_ceitsc') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        return array('success' => true, 'message' => 'Login successful');
    } else {
        return array('success' => false, 'message' => 'Invalid username or password');
    }
}

// Function to get duty logs with filtering for admin
function get_all_duty_logs($filter_committee = '', $filter_date = '', $filter_month = '', $filter_week = '') {
    global $conn;

    $sql = "SELECT log_id, student_number, full_name, committee, position, duty_date, time_in, time_out, total_hours, status
            FROM duty_logs WHERE 1=1";

    $params = array();
    $types = "";

    if (!empty($filter_committee)) {
        if ($filter_committee === 'Council Officer') {
            // For Council Officer filter, show users with Adviser, President, or Chairperson positions
            $sql .= " AND position IN ('Adviser', 'President', 'Chairperson')";
        } else {
            $sql .= " AND committee = ?";
            $params[] = $filter_committee;
            $types .= "s";
        }
    }

    if (!empty($filter_date)) {
        $sql .= " AND duty_date = ?";
        $params[] = $filter_date;
        $types .= "s";
    } elseif (!empty($filter_week)) {
        $sql .= " AND YEARWEEK(duty_date, 1) = YEARWEEK(?, 1)";
        $params[] = $filter_week;
        $types .= "s";
    } elseif (!empty($filter_month)) {
        $sql .= " AND DATE_FORMAT(duty_date, '%Y-%m') = ?";
        $params[] = $filter_month;
        $types .= "s";
    }

    $sql .= " ORDER BY duty_date DESC, time_in DESC";

    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }

    $duty_logs = array();

    while ($row = $result->fetch_assoc()) {
        if ($row['status'] === 'Ongoing') {
            // Calculate real-time hours for ongoing duties
            $time_in = new DateTime($row['duty_date'] . ' ' . $row['time_in']);
            $current_time = new DateTime();

            // Calculate the difference using DateInterval
            $interval = $time_in->diff($current_time);
            $total_minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i + ($interval->s / 60);
            $row['total_hours'] = max(0, $total_minutes / 60);
        } else {
            // For completed duties, use the stored total_hours value
            $row['total_hours'] = (float)$row['total_hours'];
        }

        $duty_logs[] = $row;
    }

    return $duty_logs;
}

// Function to format hours and minutes
function format_hours_minutes($total_hours) {
    $hours = floor($total_hours);
    $minutes = round(($total_hours - $hours) * 60);

    if ($hours > 0 && $minutes > 0) {
        return $hours . ' hrs ' . $minutes . ' mins';
    } elseif ($hours > 0) {
        return $hours . ' hrs';
    } elseif ($minutes > 0) {
        return $minutes . ' mins';
    } else {
        return '0 mins';
    }
}

// Function to format hours, minutes, and seconds for real-time display
function format_hours_minutes_seconds($total_hours) {
    $total_seconds = $total_hours * 3600;
    $hours = floor($total_seconds / 3600);
    $minutes = floor(($total_seconds % 3600) / 60);
    $seconds = floor($total_seconds % 60);

    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
}

// Function to get duty receipt data
function get_duty_receipt($log_id) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT dl.*, u.*
        FROM duty_logs dl
        JOIN users u ON dl.student_number = u.student_number
        WHERE dl.log_id = ? AND dl.status = 'Completed'
    ");
    $stmt->bind_param("i", $log_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return array('success' => false, 'message' => 'Receipt not found or duty not completed');
    }

    $receipt_data = $result->fetch_assoc();
    return array('success' => true, 'data' => $receipt_data);
}

// Function to export duty logs to CSV (simple export)
function export_duty_logs_csv($filter_committee = '', $filter_date = '', $filter_month = '') {
    $duty_logs = get_all_duty_logs($filter_committee, $filter_date, $filter_month);

    $filename = 'duty_logs_' . date('Y-m-d_H-i-s') . '.csv';

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // CSV headers
    fputcsv($output, array('Name', 'Committee', 'Position', 'Student Number', 'Date', 'Time In', 'Time Out', 'Total Hours', 'Status'));

    // CSV data
    foreach ($duty_logs as $log) {
        fputcsv($output, array(
            $log['full_name'],
            $log['committee'],
            $log['position'],
            $log['student_number'],
            $log['duty_date'],
            $log['time_in'],
            $log['time_out'] ?? 'N/A',
            format_hours_minutes($log['total_hours']),
            $log['status']
        ));
    }

    fclose($output);
    exit();
}


// Function to update user information
function update_user($user_id, $full_name, $student_number, $committee, $position, $course_year_section, $age, $contact_number, $address) {
    global $conn;

    // Check if the new student number already exists for a different user
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE student_number = ? AND user_id != ?");
    $stmt->bind_param("si", $student_number, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return array('success' => false, 'message' => 'Student number already exists for another user');
    }

    // Update user information
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, student_number = ?, committee = ?, position = ?, course_year_section = ?, age = ?, contact_number = ?, address = ? WHERE user_id = ?");
    $stmt->bind_param("sssssissi", $full_name, $student_number, $committee, $position, $course_year_section, $age, $contact_number, $address, $user_id);

    if ($stmt->execute()) {
        return array('success' => true, 'message' => 'User information updated successfully');
    } else {
        return array('success' => false, 'message' => 'Error updating user information: ' . $conn->error);
    }
}

// Function to delete user
function delete_user($user_id) {
    global $conn;

    // Get user information before deletion
    $stmt = $conn->prepare("SELECT full_name, student_number FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return array('success' => false, 'message' => 'User not found');
    }

    $user = $result->fetch_assoc();

    // Delete user (this will also delete related duty logs due to CASCADE)
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        return array('success' => true, 'message' => 'User "' . $user['full_name'] . '" deleted successfully');
    } else {
        return array('success' => false, 'message' => 'Error deleting user: ' . $conn->error);
    }
}

// Function to get user by ID
function get_user_by_id($user_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return array('success' => false, 'message' => 'User not found');
    }

    $user = $result->fetch_assoc();
    return array('success' => true, 'user' => $user);
}

// Function to get all users for admin management
function get_all_users() {
    global $conn;

    $sql = "SELECT * FROM users ORDER BY full_name ASC";
    $result = $conn->query($sql);

    $users = array();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    return $users;
}

/// Function to compute total duty hours for a committee with filtering
function compute_committee_duty_hours($committee, $filter_type = 'all', $filter_value = '') {
    global $conn;

    if ($committee === 'Council Officer') {
        // For Council Officers, sum hours for users with Adviser, President, or Chairperson positions
        $sql = "SELECT SUM(total_hours) as total_hours FROM duty_logs WHERE position IN ('Adviser', 'President', 'Chairperson') AND status = 'Completed'";
    } else {
        $sql = "SELECT SUM(total_hours) as total_hours FROM duty_logs WHERE committee = ? AND status = 'Completed'";
    }

    $params = array();
    $types = "";

    if ($committee !== 'Council Officer') {
        $params[] = $committee;
        $types .= "s";
    }

    switch ($filter_type) {
        case 'daily':
            if (!empty($filter_value)) {
                $sql .= " AND duty_date = ?";
                $params[] = $filter_value;
                $types .= "s";
            } else {
                $sql .= " AND duty_date = CURDATE()";
            }
            break;
        case 'weekly':
            if (!empty($filter_value)) {
                // Expecting filter_value in format 'YYYY-WW' (e.g., '2024-01')
                $sql .= " AND YEARWEEK(duty_date, 1) = YEARWEEK(?, 1)";
                $params[] = $filter_value;
                $types .= "s";
            } else {
                $sql .= " AND YEARWEEK(duty_date, 1) = YEARWEEK(CURDATE(), 1)";
            }
            break;
        case 'monthly':
            if (!empty($filter_value)) {
                $sql .= " AND DATE_FORMAT(duty_date, '%Y-%m') = ?";
                $params[] = $filter_value;
                $types .= "s";
            } else {
                $sql .= " AND DATE_FORMAT(duty_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')";
            }
            break;
    }

    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }

    $row = $result->fetch_assoc();
    return $row['total_hours'] ?? 0;
}


?>
