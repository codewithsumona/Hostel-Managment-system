-- Hostel Meal Management System (XAMPP / MySQL)
-- Import this file in phpMyAdmin.

CREATE DATABASE IF NOT EXISTS hostel_meal_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE hostel_meal_db;

-- Users for login
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role ENUM('admin','student') NOT NULL,
  username VARCHAR(80) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  student_ref INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Students
CREATE TABLE IF NOT EXISTS students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  room_no VARCHAR(20) NULL,
  phone VARCHAR(30) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Link users.student_ref -> students.id
ALTER TABLE users
  ADD CONSTRAINT fk_users_student
  FOREIGN KEY (student_ref) REFERENCES students(id)
  ON DELETE SET NULL;

-- Meal OFF requests (date range, per meal)
CREATE TABLE IF NOT EXISTS meal_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_ref INT NOT NULL,
  from_date DATE NOT NULL,
  to_date DATE NOT NULL,
  off_breakfast TINYINT(1) NOT NULL DEFAULT 0,
  off_lunch TINYINT(1) NOT NULL DEFAULT 0,
  off_dinner TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('approved','pending') NOT NULL DEFAULT 'approved',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mealreq_student FOREIGN KEY (student_ref) REFERENCES students(id) ON DELETE CASCADE,
  INDEX idx_mealreq_range (student_ref, from_date, to_date)
) ENGINE=InnoDB;

-- Monthly meal rate (per meal unit)
CREATE TABLE IF NOT EXISTS meal_rates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  month INT NOT NULL,
  year INT NOT NULL,
  rate_per_meal_unit DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_month_year (month, year)
) ENGINE=InnoDB;

-- Daily bazar/expense
CREATE TABLE IF NOT EXISTS bazar_expense (
  id INT AUTO_INCREMENT PRIMARY KEY,
  expense_date DATE NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  note VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_bazar_date (expense_date)
) ENGINE=InnoDB;

-- Seed admin user (username: admin, password: admin123)
INSERT INTO users (role, username, password_hash)
VALUES ('admin', 'admin', '$2y$10$fmg.8grXTf01gRj5INTOauDLL1qJH2Up6NLSItGLjobLHEjTJJq4.')
ON DUPLICATE KEY UPDATE username = username;

-- (Optional) Seed a demo student + login
INSERT INTO students (student_id, name, room_no, phone)
VALUES ('S-1001', 'Demo Student', '101', '01XXXXXXXXX')
ON DUPLICATE KEY UPDATE student_id = student_id;

-- Create a student login for demo (username: S-1001, password: 12345)
INSERT INTO users (role, username, password_hash, student_ref)
SELECT 'student', 'S-1001', '$2y$10$Q2HAOM7mK.RNSUpSdvWaZu3tuWClIylM1YIZFZcOgf/HaGL4qqBuy', s.id
FROM students s WHERE s.student_id='S-1001'
ON DUPLICATE KEY UPDATE username = username;
