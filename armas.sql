-- ARMAS Database Schema
-- Run this in phpMyAdmin to create the ARMAS database

CREATE DATABASE IF NOT EXISTS armas_db;
USE armas_db;

-- USERS TABLE
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('ofw', 'agency', 'admin', 'superadmin') NOT NULL,
  status ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
  login_attempts INT DEFAULT 0,
  locked_until DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- OFW PROFILES TABLE
CREATE TABLE IF NOT EXISTS ofws (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  first_name VARCHAR(100) NOT NULL,
  middle_name VARCHAR(100),
  suffix VARCHAR(20),
  agency_id INT NOT NULL,
  address TEXT,
  contact_number VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- AGENCIES TABLE
CREATE TABLE IF NOT EXISTS agencies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  license_number VARCHAR(100),
  address TEXT,
  contact_number VARCHAR(20),
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_by_admin_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- CASES TABLE
CREATE TABLE IF NOT EXISTS cases (
  id INT AUTO_INCREMENT PRIMARY KEY,
  case_number VARCHAR(30) UNIQUE,
  ofw_id INT NOT NULL,
  agency_id INT NOT NULL,
  type VARCHAR(100),
  status ENUM('pending','in_process','resolved','closed') DEFAULT 'pending',
  description TEXT,
  location_abroad VARCHAR(255),
  employer_name VARCHAR(255),
  date_of_departure DATE,
  emergency_contact_name VARCHAR(255),
  emergency_contact_number VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (ofw_id) REFERENCES ofws(id) ON DELETE CASCADE,
  FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE CASCADE
);

-- CASE UPDATES / TIMELINE TABLE
CREATE TABLE IF NOT EXISTS case_updates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  case_id INT NOT NULL,
  note TEXT NOT NULL,
  updated_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
  FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- OTP CODES TABLE
CREATE TABLE IF NOT EXISTS otp_codes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  code_hash VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  used TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- AUDIT LOGS TABLE
CREATE TABLE IF NOT EXISTS audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  actor_id INT NOT NULL,
  action VARCHAR(255) NOT NULL,
  target_type VARCHAR(100),
  target_id INT,
  ip_address VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (actor_id) REFERENCES users(id)
);

-- NOTIFICATIONS TABLE
CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message TEXT NOT NULL,
  type VARCHAR(100),
  read_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample data for testing (optional)
-- Admin account: email: admin@armas.gov.ph, password: Admin123!
-- INSERT INTO users (email, password_hash, role, status) VALUES ('admin@armas.gov.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin', 'active');
