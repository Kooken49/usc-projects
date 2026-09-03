-- Create Database
CREATE DATABASE IF NOT EXISTS dashboard_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

-- Use the database
USE dashboard_db;

-- Admin Table
CREATE TABLE Admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(100) NOT NULL
);

-- Teacher Table
CREATE TABLE Teacher (
    TeacherID INT AUTO_INCREMENT PRIMARY KEY,
    TeacherName VARCHAR(100) NOT NULL,
    TeacherEmail VARCHAR(100) NOT NULL,
    TeacherNum VARCHAR(20),
    Password VARCHAR(100) NOT NULL
);

-- Student Table
CREATE TABLE Student (
    StudentID INT AUTO_INCREMENT PRIMARY KEY,
    Password VARCHAR(100) NOT NULL,
    StudentName VARCHAR(100) NOT NULL,
    StudentEmail VARCHAR(100) NOT NULL,
    StudentNum VARCHAR(20)
);

-- Schedule Slot Table
CREATE TABLE ScheduleSlot (
    ScheduleID INT AUTO_INCREMENT PRIMARY KEY,
    TeacherID INT,
    StudentID INT,
    TimeStart TIME NOT NULL,
    TimeEnd TIME NOT NULL,
    Date DATE NOT NULL,
    FOREIGN KEY (TeacherID) REFERENCES Teacher(TeacherID),
    FOREIGN KEY (StudentID) REFERENCES Student(StudentID)
);

-- Notification Table (Stores notification content)
CREATE TABLE Notification (
    NotificationID INT AUTO_INCREMENT PRIMARY KEY,
    SenderType ENUM('Admin', 'System') NOT NULL,
    SenderID INT, -- Can be NULL if System
    Title VARCHAR(255) NOT NULL,
    Message TEXT NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Notification Recipient Table (Links notifications to students or teachers)
CREATE TABLE NotificationRecipient (
    RecipientID INT AUTO_INCREMENT PRIMARY KEY,
    NotificationID INT NOT NULL,
    RecipientType ENUM('Student', 'Teacher', 'Group') NOT NULL,
    StudentID INT, -- Nullable, filled if RecipientType = 'Student'
    TeacherID INT, -- Nullable, filled if RecipientType = 'Teacher'
    GroupTarget ENUM('AllStudents', 'AllTeachers') DEFAULT NULL, -- If RecipientType = 'Group'
    IsRead BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (NotificationID) REFERENCES Notification(NotificationID),
    FOREIGN KEY (StudentID) REFERENCES Student(StudentID),
    FOREIGN KEY (TeacherID) REFERENCES Teacher(TeacherID)
);
