-- Admin Table
CREATE TABLE Admin (
    UserID INT PRIMARY KEY,
    Password VARCHAR(100) NOT NULL
);

-- Teacher Table
CREATE TABLE Teacher (
    TeacherID INT PRIMARY KEY,
    TeacherName VARCHAR(100) NOT NULL,
    TeacherEmail VARCHAR(100) NOT NULL,
    TeacherNum VARCHAR(20),
    Password VARCHAR(100) NOT NULL
);

-- Student Table
CREATE TABLE Student (
    StudentID INT PRIMARY KEY,
    Password VARCHAR(100) NOT NULL,
    StudentName VARCHAR(100) NOT NULL,
    StudentEmail VARCHAR(100) NOT NULL,
    StudentNum VARCHAR(20)
);

-- Schedule Slot Table
CREATE TABLE ScheduleSlot (
    ScheduleID INT PRIMARY KEY,
    TeacherID INT,
    StudentID INT,
    TimeStart TIME NOT NULL,
    TimeEnd TIME NOT NULL,
    Date DATE NOT NULL,
    FOREIGN KEY (TeacherID) REFERENCES Teacher(TeacherID),
    FOREIGN KEY (StudentID) REFERENCES Student(StudentID)
);
