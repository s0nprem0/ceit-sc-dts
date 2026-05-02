<?php
require_once __DIR__ . '/../core/Database.php';

class DutyService
{
	private $conn;

	public function __construct()
	{
		$this->conn = Database::getInstance()->getConnection();
	}


	// Function to log in for duty - automatically starts duty tracking
	function loginDuty($student_number, $password = null)
	{
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

	// Log out from duty
	public function logOutDuty($log_id)
	{
		global $conn;

		// Get duty log information
		$stmt = $conn->prepare("SELECT time_in FROM duty_logs WHERE log_id = ?");
		$stmt->bind_param("i", $log_id);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows === 0) {
			return array('success' => false, 'message' => 'Duty log not found');
		}

		$duty_log = $result->fetch_assoc();
		$time_in = $duty_log['time_in'];
		$time_out_str = Utils::getCurrentDateTime();
		$total_hours = Utils::calculateTotalHours($time_in, $time_out_str);

		// Update duty log with time_out and total_hours
		$stmt = $conn->prepare("UPDATE duty_logs SET time_out = ?, total_hours = ?, status = 'Completed' WHERE log_id = ?");
		$stmt->bind_param("sssi", $time_out_str, $total_hours, $log_id);

		if ($stmt->execute()) {
			return array('success' => true, 'message' => 'Successfully logged out from duty');
		} else {
			return array('success' => false, 'message' => 'Failed to log out from duty: ' . $conn->error);
		}
	}

	public function getAllDutyLogs()
	{
		global $conn;

		$sql = "SELECT log_id, student_number, full_name, committee, position, duty_date, time_in, time_out, total_hours, status FROM duty_logs ORDER BY duty_date DESC, time_in DESC";
		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
			$duty_logs = array();
			while ($row = $result->fetch_assoc()) {
				$duty_logs[] = $row;
			}
			return array('success' => true, 'duty_logs' => $duty_logs);
		} else {
			return array('success' => false, 'message' => 'No duty logs found');
		}
	}

	public function getDutyReceipt($log_id)
	{
		global $conn;

		$stmt = $conn->prepare("SELECT log_id, student_number, full_name, committee, position, duty_date, time_in, time_out, total_hours FROM duty_logs WHERE log_id = ?");
		$stmt->bind_param("i", $log_id);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows === 0) {
			return array('success' => false, 'message' => 'Duty log not found');
		}

		$duty_log = $result->fetch_assoc();
		return array('success' => true, 'duty_log' => $duty_log);
	}

	// Add other duty-related methods here (getAllDutyLogs, getDutyReceipt, etc.)
}
