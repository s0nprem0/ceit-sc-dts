<?php
require_once __DIR__ . '/../services/DutyService.php';
require_once __DIR__ . '/../services/UserService.php';

class DutyController
{
	private $dutyService;
	private $userService;

	public function __construct()
	{
		$this->dutyService = new DutyService();
		$this->userService = new UserService();
	}

	public function register($full_name, $student_number, $committee, $position, $course_year_section, $age, $contact_number, $address)
	{
		return $this->userService->registerUser($full_name, $student_number, $committee, $position, $course_year_section, $age, $contact_number, $address);
	}

	public function log_in($student_number, $password = null)
	{
		return $this->dutyService->loginDuty($student_number, $password);
	}

	public function log_out($log_id)
	{
		return $this->dutyService->logOutDuty($log_id);
	}

	public function get_duty_logs()
	{
		return $this->dutyService->getDutyLogs();
	}

	public function get_officer_stats()
	{
		return $this->userService->getOfficerStats();
	}

	public function get_committee_stats()
	{
		return $this->userService->getCommitteeStats();
	}
}
