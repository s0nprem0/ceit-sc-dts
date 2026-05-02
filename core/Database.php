<?php
class Database {
	private static $instance = null;
	private $conn;

	private $host = 'localhost';
	private $db_name = 'ceit_sc_duty_tracker';
	private $username = 'root';
	private $password = '';

	private function __construct() {
		$this->connect();
	}

	private function connect() {
		$this->conn = new mysqli($this->host, $this->username, $this->password);
		if ($this->conn->connect_error) {
			die("Connection failed: " . $this->conn->connect_error);
		}
		// Create Database if it doesn't exist
		$this->conn->query("CREATE DATABASE IF NOT EXISTS $this->db_name");
		$this->conn->select_db($this->db_name);
		$this->conn->set_charset("utf8mb4");
	}

	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new Database();
		}
		return self::$instance;
	}

	public function getConnection() {
		return $this->conn;
	}
}