<?php
require_once __DIR__ . '/../core/Database.php';

class UserService
{
	private $conn;

	public function __construct()
	{
		$this->conn = Database::getInstance()->getConnection();
	}

	public function userExists($student_number)
	{
		$stmt = $this->conn->prepare("SELECT * FROM users WHERE student_number = ?");
		$stmt->bind_param("s", $student_number);
		$stmt->execute();
		$result = $stmt->get_result();
		return $result->num_rows > 0;
	}

	public function registerUser($full_name, $student_number, $committee, $position, $course_year_section, $age, $contact_number, $address)
	{
		if ($this->userExists($student_number)) {
			return array('success' => false, 'message' => 'Student number already registered');
		}

		$stmt = $this->conn->prepare("INSERT INTO users (full_name, student_number, committee, position, course_year_section, age, contact_number, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("sssssiis", $full_name, $student_number, $committee, $position, $course_year_section, $age, $contact_number, $address);

		if ($stmt->execute()) {
			return array('success' => true, 'message' => 'User registered successfully');
		} else {
			return array('success' => false, 'message' => 'Error registering user: ' . $stmt->error);
		}
	}

	public function updateUser($user_id, $full_name, $student_number, $committee, $position, $course_year_section, $age, $contact_number, $address)
	{
		// Check if the new student number already exists for a different user
		$stmt = $this->conn->prepare("SELECT user_id FROM users WHERE student_number = ? AND user_id != ?");
		$stmt->bind_param("si", $student_number, $user_id);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows > 0) {
			return array('success' => false, 'message' => 'Student number already exists for another user');
		}

		// Update user information
		$stmt = $this->conn->prepare("UPDATE users SET full_name = ?, student_number = ?, committee = ?, position = ?, course_year_section = ?, age = ?, contact_number = ?, address = ? WHERE user_id = ?");
		$stmt->bind_param("sssssissi", $full_name, $student_number, $committee, $position, $course_year_section, $age, $contact_number, $address, $user_id);

		if ($stmt->execute()) {
			return array('success' => true, 'message' => 'User information updated successfully');
		} else {
			return array('success' => false, 'message' => 'Error updating user information: ' . $this->conn->error);
		}
	}

	public function deleteUser($user_id)
	{
		// Get user information before deletion
		$stmt = $this->conn->prepare("SELECT full_name, student_number FROM users WHERE user_id = ?");
		$stmt->bind_param("i", $user_id);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows === 0) {
			return array('success' => false, 'message' => 'User not found');
		}

		$user = $result->fetch_assoc();

		// Delete user (this will also delete related duty logs due to CASCADE)
		$stmt = $this->conn->prepare("DELETE FROM users WHERE user_id = ?");
		$stmt->bind_param("i", $user_id);

		if ($stmt->execute()) {
			return array('success' => true, 'message' => 'User "' . $user['full_name'] . '" deleted successfully');
		} else {
			return array('success' => false, 'message' => 'Error deleting user: ' . $this->conn->error);
		}
	}

	public function getUserProfile($student_number)
	{
		$stmt = $this->conn->prepare("SELECT * FROM users WHERE student_number = ?");
		$stmt->bind_param("s", $student_number);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows === 0) {
			return array('success' => false, 'message' => 'User not found');
		}

		$user = $result->fetch_assoc();
		return array('success' => true, 'user' => $user);
	}

	public function getUserById($user_id)
	{
		$stmt = $this->conn->prepare("SELECT * FROM users WHERE user_id = ?");
		$stmt->bind_param("i", $user_id);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows === 0) {
			return array('success' => false, 'message' => 'User not found');
		}

		$user = $result->fetch_assoc();
		return array('success' => true, 'user' => $user);
	}

	public function getAllUsers()
	{
		$sql = "SELECT user_id, full_name, student_number, committee, position, course_year_section, age, contact_number, address FROM users ORDER BY full_name ASC";
		$result = $this->conn->query($sql);
		$users = array();

		while ($row = $result->fetch_assoc()) {
			$users[] = $row;
		}

		return $users;
	}

	public function getOfficerStats()
	{
		$sql = "SELECT position, COUNT(*) AS count FROM users GROUP BY position";
		$result = $this->conn->query($sql);
		$stats = array();

		while ($row = $result->fetch_assoc()) {
			$stats[] = $row;
		}

		return $stats;
	}
}
