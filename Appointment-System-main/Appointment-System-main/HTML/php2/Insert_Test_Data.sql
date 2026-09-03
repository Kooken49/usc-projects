-- Use the correct database
USE dashboard_db;

-- Insert test Admins
INSERT INTO Admin (AdminName, AdminEmail, password) VALUES
('Admin One', 'admin1@universityofsc.edu', 'pass123'),
('Admin Two', 'admin2@universityofsc.edu', 'secure456');

-- Insert test Teachers
INSERT INTO Teacher (TeacherName, TeacherEmail, TeacherNum, Password) VALUES
('Mr. Smith', 'smith@universityofsc.edu', '09171234567', 'teachpass1'),
('Ms. Johnson', 'johnson@universityofsc.edu', '09179876543', 'teachpass2');

-- Insert test Students
INSERT INTO Student (Password, StudentName, StudentEmail, StudentNum, Plan, Level) VALUES
('studpass1', 'Alice Reyes', 'alice@universityofsc.edu', '09981234567', 'OneTimeAWeek', 'Basic'),
('studpass2', 'Bob Santos', 'bob@universityofsc.edu', '09982345678', 'ThreeTimesAWeek', 'Advanced');
