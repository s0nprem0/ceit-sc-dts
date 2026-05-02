<?php

class Utils {
	public static function sanitizeInput($input) {
		return htmlspecialchars(trim($input));
	}

	// Function to format hours into "X hours Y minutes"
	public static function formatHours($total_hours) {
		$hours = floor($total_hours);
		$minutes = round(($total_hours - $hours) * 60);
		return "{$hours} hours {$minutes} minutes";
	}

	// Function to format hours into "HH:MM"
	public static function formatHoursMinutes($total_hours) {
		$hours = floor($total_hours);
		$minutes = round(($total_hours - $hours) * 60);
		return sprintf("%02d:%02d", $hours, $minutes);
	}

	// Function to format hours into "HH:MM:SS"
	public static function formatHoursMinutesSeconds($total_hours) {
		$total_seconds = round($total_hours * 3600);
		$hours = floor($total_seconds / 3600);
		$minutes = floor(($total_seconds % 3600) / 60);
		$seconds = $total_seconds % 60;
		return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
	}

	// Function to calculate total hours from time_in and time_out
	public static function calculateTotalHours($time_in, $time_out) {
		$time_in = new DateTime($time_in);
		$time_out = new DateTime($time_out);
		$interval = $time_in->diff($time_out);
		return $interval->h + ($interval->i / 60);
	}

	// Function to get the current date and time in a specific format
	public static function getCurrentDateTime() {
		return date('Y-m-d H:i:s');
	}
}