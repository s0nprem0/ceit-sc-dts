<?php
// services/DutyLogService.php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../helpers/Utils.php';

class DutyLogService {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getAllDutyLogs($filter_committee = '', $filter_date = '', $filter_month = '', $filter_week = '') {
        $sql = "SELECT log_id, student_number, full_name, committee, position, duty_date, time_in, time_out, total_hours, status FROM duty_logs WHERE 1=1";
        $params = array();
        $types = "";

        if (!empty($filter_committee)) {
            if ($filter_committee === 'Council Officer') {
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
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->conn->query($sql);
        }

        $duty_logs = array();
        while ($row = $result->fetch_assoc()) {
            if ($row['status'] === 'Ongoing') {
                $time_in = new DateTime($row['duty_date'] . ' ' . $row['time_in']);
                $current_time = new DateTime();
                $interval = $time_in->diff($current_time);
                $total_minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i + ($interval->s / 60);
                $row['total_hours'] = max(0, $total_minutes / 60);
            } else {
                $row['total_hours'] = (float)$row['total_hours'];
            }
            $duty_logs[] = $row;
        }
        return $duty_logs;
    }

    public function getDutyReceipt($log_id) {
        $stmt = $this->conn->prepare("
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

    public function exportDutyLogsCsv($filter_committee = '', $filter_date = '', $filter_month = '') {
        $duty_logs = $this->getAllDutyLogs($filter_committee, $filter_date, $filter_month);
        $filename = 'duty_logs_' . date('Y-m-d_H-i-s') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $output = fopen('php://output', 'w');
        fputcsv($output, array('Name', 'Committee', 'Position', 'Student Number', 'Date', 'Time In', 'Time Out', 'Total Hours', 'Status'));
        foreach ($duty_logs as $log) {
            fputcsv($output, array(
                $log['full_name'],
                $log['committee'],
                $log['position'],
                $log['student_number'],
                $log['duty_date'],
                $log['time_in'],
                $log['time_out'] ?? 'N/A',
                Utils::formatHoursMinutes($log['total_hours']),
                $log['status']
            ));
        }
        fclose($output);
        exit();
    }

    public function computeCommitteeDutyHours($committee, $filter_type = 'all', $filter_value = '') {
        if ($committee === 'Council Officer') {
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
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->conn->query($sql);
        }
        $row = $result->fetch_assoc();
        return $row['total_hours'] ?? 0;
    }
}
