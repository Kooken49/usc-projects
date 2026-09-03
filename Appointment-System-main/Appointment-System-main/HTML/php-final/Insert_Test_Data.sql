-- Use the correct database
USE dashboard_db;
-- A1b2C3d4!
-- Insert test Admins
INSERT INTO Admin (AdminName, AdminEmail, password) VALUES
('Admin One', 'admin1@universityofsc.edu', '$2y$10$/FKBI3UFev4yuk1XVNJBm.Waq5baJpzVyKG14lwb48ix03vMV9qDW'),
('Admin Two', 'admin2@universityofsc.edu', '$2y$10$/FKBI3UFev4yuk1XVNJBm.Waq5baJpzVyKG14lwb48ix03vMV9qDW');

-- Insert test Teachers
INSERT INTO Teacher (TeacherName, TeacherEmail, TeacherNum, Password) VALUES
('Mr. Smith', 'smith@universityofsc.edu', '09171234567', '$2y$10$/FKBI3UFev4yuk1XVNJBm.Waq5baJpzVyKG14lwb48ix03vMV9qDW'),
('Ms. Johnson', 'johnson@universityofsc.edu', '09179876543', '$2y$10$/FKBI3UFev4yuk1XVNJBm.Waq5baJpzVyKG14lwb48ix03vMV9qDW');

-- Insert test Students
INSERT INTO Student (Password, StudentName, StudentEmail, StudentNum, Level) VALUES
('$2y$10$/FKBI3UFev4yuk1XVNJBm.Waq5baJpzVyKG14lwb48ix03vMV9qDW', 'Alice Reyes', 'alice@universityofsc.edu', '09981234567', 'Basic'),
('$2y$10$/FKBI3UFev4yuk1XVNJBm.Waq5baJpzVyKG14lwb48ix03vMV9qDW', 'Bob Santos', 'bob@universityofsc.edu', '09982345678', 'Advanced');
