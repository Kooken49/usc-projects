-- Create Database
CREATE DATABASE IF NOT EXISTS dashboard_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

-- Use the database
USE dashboard_db;

-- Admin Table
CREATE TABLE Admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    AdminName VARCHAR(100) NOT NULL,
    AdminEmail VARCHAR(100) NOT NULL,
    password VARCHAR(100) NOT NULL,
    IsActive BOOLEAN NOT NULL DEFAULT TRUE
);

-- Teacher Table
CREATE TABLE Teacher (
    TeacherID INT AUTO_INCREMENT PRIMARY KEY,
    Password VARCHAR(100) NOT NULL,
    TeacherName VARCHAR(100) NOT NULL,
    TeacherEmail VARCHAR(100) NOT NULL,
    TeacherNum VARCHAR(20),
    IsActive BOOLEAN NOT NULL DEFAULT TRUE
);

-- Student Table
CREATE TABLE Student (
    StudentID INT AUTO_INCREMENT PRIMARY KEY,
    Password VARCHAR(100) NOT NULL,
    StudentName VARCHAR(100) NOT NULL,
    StudentEmail VARCHAR(100) NOT NULL,
    StudentNum VARCHAR(20),
    Level ENUM('Basic', 'Advanced') DEFAULT 'Basic',
    IsActive BOOLEAN NOT NULL DEFAULT TRUE
);

-- Schedule Slot Table
CREATE TABLE ScheduleSlot (
    ScheduleID INT AUTO_INCREMENT PRIMARY KEY,
    TeacherID INT,
    StudentID INT,
    TimeStart TIME NOT NULL,
    TimeEnd TIME NOT NULL,
    Date DATE NOT NULL,
    FOREIGN KEY (TeacherID) REFERENCES Teacher(TeacherID) ON DELETE CASCADE,
    FOREIGN KEY (StudentID) REFERENCES Student(StudentID) ON DELETE CASCADE
);

-- Notification Table
CREATE TABLE Notification (
    NotificationID INT AUTO_INCREMENT PRIMARY KEY,
    SenderType ENUM('Admin', 'System') NOT NULL,
    Title VARCHAR(255) NOT NULL,
    Message TEXT NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Notification Recipient Table
CREATE TABLE NotificationRecipient (
    RecipientID INT AUTO_INCREMENT PRIMARY KEY,
    NotificationID INT NOT NULL,
    RecipientType ENUM('Student', 'Teacher', 'Group') NOT NULL,
    StudentID INT,
    TeacherID INT,
    GroupTarget ENUM('AllStudents', 'AllTeachers') DEFAULT NULL,
    IsRead BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (NotificationID) REFERENCES Notification(NotificationID) ON DELETE CASCADE,
    FOREIGN KEY (StudentID) REFERENCES Student(StudentID) ON DELETE CASCADE,
    FOREIGN KEY (TeacherID) REFERENCES Teacher(TeacherID) ON DELETE CASCADE
);

-- Booking Credits Table
CREATE TABLE BookingCredits ( 
    CreditID INT AUTO_INCREMENT PRIMARY KEY, 
    StudentID INT NOT NULL,
    CreditAmount INT NOT NULL DEFAULT 0, 
    Plan ENUM('OneTimeAWeek', 'TwoTimesAWeek', 'ThreeTimesAWeek', 'Everyday') DEFAULT 'OneTimeAWeek',
    IssuedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ExpiryDate DATE DEFAULT (CURRENT_DATE + INTERVAL 1 MONTH),
    FOREIGN KEY (StudentID) REFERENCES Student(StudentID) ON DELETE CASCADE
);

-- Student Booking Table
CREATE TABLE StudentBooking (
    BookingID INT AUTO_INCREMENT PRIMARY KEY,
    StudentID INT NOT NULL,
    PreferredDate DATE NOT NULL,
    PreferredTimeStart TIME NOT NULL,
    PreferredTimeEnd TIME NOT NULL,
    Status ENUM('Pending', 'Approved', 'Denied') DEFAULT 'Pending',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (StudentID) REFERENCES Student(StudentID) ON DELETE CASCADE
);

-- password reset Table
CREATE TABLE password_reset_tokens (
    TokenID INT AUTO_INCREMENT PRIMARY KEY,
    Email VARCHAR(100) NOT NULL,
    Token VARCHAR(20) NOT NULL,
    Role ENUM('Admin', 'Student', 'Teacher') NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Expiration DATETIME NOT NULL
);
