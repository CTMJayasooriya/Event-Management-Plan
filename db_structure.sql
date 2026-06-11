-- Database Creation
CREATE DATABASE IF NOT EXISTS student_event_management;
USE student_event_management;

-- Users Table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Events Table
CREATE TABLE events (
    event_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    time TIME NOT NULL,
    venue VARCHAR(150) NOT NULL,
    organizer VARCHAR(100) NOT NULL,
    capacity INT DEFAULT 50,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Registrations Table
CREATE TABLE registrations (
    reg_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    student_id VARCHAR(50) NOT NULL,
    contact_number VARCHAR(15),
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('confirmed', 'cancelled') DEFAULT 'confirmed',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (user_id, event_id)
);

-- Insert Sample Admin User (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert Sample Events
INSERT INTO events (title, description, date, time, venue, organizer, capacity, created_by) VALUES
('Web Development Workshop', 'Learn HTML, CSS, JavaScript and modern web frameworks', '2025-11-20', '10:00:00', 'Computer Lab A', 'CS Department', 40, 1),
('Hackathon 2025', '24-hour coding competition with amazing prizes', '2025-11-25', '09:00:00', 'Main Auditorium', 'Tech Club', 100, 1),
('AI & Machine Learning Seminar', 'Industry experts discuss latest trends in AI', '2025-12-05', '14:00:00', 'Lecture Hall 2', 'AI Research Lab', 60, 1),
('Career Fair', 'Meet top employers and explore job opportunities', '2025-12-10', '11:00:00', 'Student Center', 'Career Services', 200, 1);

-- Insert Sample Student Users (password: student123)
INSERT INTO users (name, email, password, role) VALUES
('John Smith', 'john@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Sarah Johnson', 'sarah@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

-- Insert Sample Registrations
INSERT INTO registrations (user_id, event_id, student_id, contact_number) VALUES
(2, 1, 'STU2023001', '555-0101'),
(3, 1, 'STU2023002', '555-0102'),
(2, 2, 'STU2023001', '555-0101');