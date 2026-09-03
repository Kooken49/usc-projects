-- Use the correct database
USE dashboard_db;

-- Insert test Admins
INSERT INTO Admin (username, password) VALUES
('admin1', 'pass123'),
('admin2', 'secure456');

-- Insert test Teachers
INSERT INTO Teacher (TeacherName, TeacherEmail, TeacherNum, Password) VALUES
('Mr. Smith', 'smith@exampleeeeeeeeeee.com', '09171234567', 'teachpass1'),
('Ms. Johnson', 'johnson@exampleeeeeeeeeee.com', '09179876543', 'teachpass2');

-- Insert test Students
INSERT INTO Student (Password, StudentName, StudentEmail, StudentNum) VALUES
('studpass1', 'Alice Reyes', 'alice@exampleeeeeeeeeee.com', '09981234567'),
('studpass2', 'Bob Santos', 'bob@exampleeeeeeeeeee.com', '09982345678');