-- CEIT-SC Office Duty Tracker Database Schema (Updated)
CREATE DATABASE IF NOT EXISTS ceit_sc_duty_tracker;
USE ceit_sc_duty_tracker;

-- Users table to store officer information
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    student_number VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    committee VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    course_year_section VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    contact_number VARCHAR(20),
    address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_student_number (student_number),
    INDEX idx_committee (committee),
    INDEX idx_position (position)
);

-- Duty logs table to store all duty records (simplified without break features)
CREATE TABLE IF NOT EXISTS duty_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    student_number VARCHAR(20) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    committee VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    duty_date DATE NOT NULL,
    time_in TIME NOT NULL,
    time_out TIME NULL,
    total_hours DECIMAL(5,2) DEFAULT 0.00,
    status ENUM('Ongoing', 'Completed') DEFAULT 'Ongoing',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_number) REFERENCES users(student_number) ON DELETE CASCADE,
    INDEX idx_student_number (student_number),
    INDEX idx_duty_date (duty_date),
    INDEX idx_status (status),
    INDEX idx_committee (committee)
);

