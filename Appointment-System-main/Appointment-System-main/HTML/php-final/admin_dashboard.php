<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "dashboard_db");

// Fetch all schedule info
$schedules = $conn->query("SELECT ScheduleSlot.*, TeacherName, StudentName 
                           FROM ScheduleSlot
                           LEFT JOIN Teacher ON ScheduleSlot.TeacherID = Teacher.TeacherID
                           LEFT JOIN Student ON ScheduleSlot.StudentID = Student.StudentID");

$dateEventMap = [];  // to store detailed info per date
$dateCounts = [];     // to store just counts for calendar

while ($row = $schedules->fetch_assoc()) {
    $date = $row['Date'];
    $title = "{$row['TeacherName']} & {$row['StudentName']} ({$row['TimeStart']} - {$row['TimeEnd']})";

    if (!isset($dateEventMap[$date])) {
        $dateEventMap[$date] = [];
        $dateCounts[$date] = 0;
    }

    $dateEventMap[$date][] = $title;
    $dateCounts[$date]++;
}

// Events to show on the calendar
$events = [];
foreach ($dateCounts as $date => $count) {
    $events[] = [
        'title' => "$count Schedule" . ($count > 1 ? 's' : ''),
        'start' => $date,
        'display' => 'auto'
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
  <style>
    .font-georgia { font-family: Georgia, serif; }
    html, body { height: 100%; }
  </style>
</head>
<body class="h-screen flex text-gray-800 font-georgia">
    <aside class="fixed top-0 left-0 h-full w-64 bg-[#f1f1f1] shadow-lg p-6 z-10">
    <a href="index.html" class="flex items-center space-x-2 mb-8">
      <img src="img/logo.png" alt="Logo" class="h-12">
      <span class="text-xl font-bold text-blue-700">World AI</span>
    </a>
    <nav class="flex flex-col space-y-4 text-lg">
      <a href="admin_dashboard.php" class="text-blue-700 font-semibold">Dashboard</a>
      <a href="user_manage.php" class="hover:text-blue-600">User Management</a>
      <a href="credit_manage.php" class="hover:text-blue-600">Credit Management</a>
      <a href="schedule_manage.php" class="hover:text-blue-600">Schedules</a>
      <a href="admin_notifications.php" class="hover:text-blue-600">Notifications</a>
      <a href="logout.php" class="text-red-600 hover:underline">Logout</a>
    </nav>
  </aside>

  <!-- MAIN CONTENT WRAPPER (with left margin so it doesn't sit under the sidebar) -->
  <div class="ml-64 flex flex-col w-full min-h-screen bg-white">

    <!-- CONSISTENT HEADER -->
    <header class="bg-[#f1f1f1] shadow h-24 flex items-center justify-center px-6">
      <h1 class="text-3xl font-bold">Admin Dashboard</h1>
    </header>

    <!-- PAGE-SPECIFIC CONTENT GOES HERE -->
    <main class="flex-1 p-6 overflow-auto">
  <div class="flex gap-6">
    <!-- Calendar on the left -->
    <div id="calendar" class="flex-1 bg-[#f1f1f1] rounded-lg shadow p-4 h-[75vh]"></div>

    <!-- Event panel on the right -->
    <aside id="eventListPanel" class="w-96 bg-gray-100 p-6 border rounded shadow-inner overflow-auto">
      <h2 class="text-xl font-bold mb-4">Event Details</h2>
      <p class="text-gray-500 italic">Click a day to see scheduled events.</p>
    </aside>
  </div>
</main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const calendarEl = document.getElementById('calendar');
      const eventPanel = document.getElementById('eventListPanel');

      const rawEvents = <?= json_encode($events) ?>;
      const eventDetailsMap = <?= json_encode($dateEventMap) ?>;

      function renderEventsForDate(dateStr) {
        const events = eventDetailsMap[dateStr];
        if (events && events.length > 0) {
          eventPanel.innerHTML = `
            <h2 class="text-xl font-bold mb-4">Schedules on ${dateStr}</h2>
            <ul class="list-disc pl-5 space-y-2">
              ${events.map(e => `<li>${e}</li>`).join('')}
            </ul>
          `;
        } else {
          eventPanel.innerHTML = `
            <h2 class="text-xl font-bold mb-4">Schedules on ${dateStr}</h2>
            <p class="text-gray-500 italic">No schedules on this date.</p>
          `;
        }
      }

      function applyHoverStyles() {
        const dayCells = document.querySelectorAll('.fc-daygrid-day');
        dayCells.forEach(cell => {
          cell.classList.add(
            'transition',
            'duration-200',
            'ease-in-out',
            'hover:bg-blue-100',
            'cursor-pointer',
            'rounded',
            'hover:shadow-md',
            'hover:scale-105'
          );
        });
      }

      // Initialize calendar
      const calendar = new FullCalendar.Calendar(calendarEl, {
  initialView: 'dayGridMonth',
  events: rawEvents, // ✅ Use the correct variable
  dateClick: function (info) {
    renderEventsForDate(info.dateStr);
  },
  eventClick: function (info) {
    renderEventsForDate(info.event.startStr);
  },
  datesSet: function () {
    applyHoverStyles(); // Reapply hover styles on each view change
  }
});


      calendar.render();
    });
  </script>
</body>
</html>
