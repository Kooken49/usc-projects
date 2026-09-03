<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "dashboard_db");
$schedules = $conn->query("SELECT ScheduleSlot.*, TeacherName, StudentName 
                           FROM ScheduleSlot
                           LEFT JOIN Teacher ON ScheduleSlot.TeacherID = Teacher.TeacherID
                           LEFT JOIN Student ON ScheduleSlot.StudentID = Student.StudentID");

$events = [];
while ($row = $schedules->fetch_assoc()) {
    $events[] = [
        'date' => $row['Date'],
        'start' => $row['TimeStart'],
        'end' => $row['TimeEnd'],
        'teacher' => $row['TeacherName'],
        'student' => $row['StudentName']
    ];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; vertical-align: top; }
        th { background-color: #f0f0f0; }
        td div { margin-top: 4px; }
    </style>
</head>
<body>
    <h2>Dashboard</h2>
    <nav>
        <a href="user_manage.php">User Management</a> |
        <a href="schedule_manage.php">Schedules</a> |
        <a href="admin_notifications.php">Notifications</a> |
        <a href="credit_manage.php">Credit Management</a> |
        <a href="logout.php">Logout</a>
    </nav>

    <h2> Class Schedule Calendar</h2>
    <div id="calendar"></div>

    <script>
    const events = <?= json_encode($events) ?>;

    const calendar = document.getElementById('calendar');
    const today = new Date();
    const year = today.getFullYear();
    const month = today.getMonth();

    const monthNames = ["January", "February", "March", "April", "May", "June",
                        "July", "August", "September", "October", "November", "December"];

    function generateCalendar(year, month) {
        const date = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0).getDate();

        let html = `<h3>${monthNames[month]} ${year}</h3><table><tr>`;
        const days = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
        for (let d of days) html += `<th>${d}</th>`;
        html += `</tr><tr>`;

        for (let i = 0; i < date.getDay(); i++) html += "<td></td>";

        for (let day = 1; day <= lastDay; day++) {
            const fullDate = `${year}-${String(month+1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const dayEvents = events.filter(e => e.date === fullDate);

            html += `<td><strong>${day}</strong>`;
            for (let e of dayEvents) {
                html += `<div style="font-size: 0.75em;">
                            ${e.start}–${e.end}<br>
                            ${e.teacher}<br>
                            ${e.student}
                         </div>`;
            }
            html += `</td>`;

            if ((date.getDay() + day) % 7 === 0) html += "</tr><tr>";
        }

        html += "</tr></table>";
        calendar.innerHTML = html;
    }

    generateCalendar(year, month);
    </script>
</body>
</html>
