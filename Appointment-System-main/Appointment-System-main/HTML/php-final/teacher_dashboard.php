<?php
session_start();

if (!isset($_SESSION['teacher_logged_in'])) {
    header("Location: user_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "dashboard_db");
$teacherID = $_SESSION['teacher_id'];

// Fetch class schedule
$scheduleData = [];
$scheduleQuery = $conn->prepare("SELECT Date, TimeStart, TimeEnd FROM ScheduleSlot WHERE TeacherID = ?");
$scheduleQuery->bind_param("i", $teacherID);
$scheduleQuery->execute();
$scheduleResult = $scheduleQuery->get_result();

while ($row = $scheduleResult->fetch_assoc()) {
    $scheduleData[] = $row;
}

// Grouped schedule for calendar
$dateMap = [];
foreach ($scheduleData as $row) {
    $date = $row['Date'];
    $text = "🕒 {$row['TimeStart']} - {$row['TimeEnd']}";
    $dateMap[$date][] = $text;
}

$calendarEvents = [];
foreach ($dateMap as $date => $items) {
    $calendarEvents[] = [
        'title' => count($items) . ' class' . (count($items) > 1 ? 'es' : ''),
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
  <title>Teacher Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet" />
  <style>
    .font-georgia { font-family: Georgia, serif; }
    html, body { height: 100%; margin: 0; }
  </style>
</head>

<body class="text-gray-800 font-georgia h-screen overflow-hidden">
  <div class="flex h-full">
    <!-- ========== Sidebar ========== -->
    <aside class="w-64 bg-[#f1f1f1] shadow-lg p-6 flex flex-col justify-between">
      <div>
        <a href="teacher_dashboard.php" class="flex items-center space-x-2 mb-8">
          <img src="img/logo.png" alt="Logo" class="h-12">
          <span class="text-xl font-bold text-blue-700">World AI</span>
        </a>
        <nav class="flex flex-col space-y-4 text-lg">
          <a href="#" class="text-blue-700 font-semibold">Dashboard</a>
          <a href="teacher_inbox.php" class="hover:text-blue-600">Inbox</a>
          <a href="logout.php" class="hover:text-red-600">Logout</a>
        </nav>
      </div>
    </aside>

    <!-- ========== Main Content ========== -->
    <div class="flex-1 flex flex-col bg-white">
      
      <!-- ========== Header ========== -->
      <header class="bg-[#f1f1f1] shadow h-24 flex items-center px-6">
        <h1 class="text-3xl font-bold">Welcome, <?= htmlspecialchars($_SESSION['teacher_name']) ?></h1>
      </header>

      <!-- ========== Calendar & Events Section ========== -->
      <main class="flex flex-1 overflow-hidden">
        <!-- Calendar -->
        <div class="flex-1 p-6 overflow-auto">
          <div id="calendar" class="bg-[#f1f1f1] rounded-lg shadow p-4 h-[75vh]"></div>
        </div>

        <!-- Event Detail Panel -->
        <aside id="eventListPanel" class="w-96 bg-gray-100 p-6 border-l shadow-inner overflow-auto">
          <h2 class="text-xl font-bold mb-4">Event Details</h2>
          <p class="text-gray-500 italic">Click a day to see scheduled events.</p>
        </aside>
      </main>
    </div>
  </div>

  <!-- ========== Scripts ========== -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const calendarEl = document.getElementById('calendar');
      const eventPanel = document.getElementById('eventListPanel');

      const summaryList = <?= json_encode($calendarEvents) ?>;
      const detailsMap = <?= json_encode($dateMap) ?>;

      function renderEventsForDate(dateStr) {
        const events = detailsMap[dateStr] || [];
        if (events.length > 0) {
          eventPanel.innerHTML = `
            <h2 class="text-xl font-bold mb-4">Schedule on ${dateStr}</h2>
            <ul class="list-disc pl-5 space-y-2">
              ${events.map(e => `<li>${e}</li>`).join('')}
            </ul>
          `;
        } else {
          eventPanel.innerHTML = `
            <h2 class="text-xl font-bold mb-4">Schedule on ${dateStr}</h2>
            <p class="text-gray-500 italic">No classes scheduled.</p>
          `;
        }
      }

      function applyHoverStyles() {
        document.querySelectorAll('.fc-daygrid-day').forEach(cell => {
          cell.classList.add(
            'transition', 'duration-200', 'ease-in-out',
            'hover:bg-blue-100', 'cursor-pointer',
            'rounded', 'hover:shadow-md', 'hover:scale-105'
          );
        });
      }

      const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: summaryList,
        dateClick: info => renderEventsForDate(info.dateStr),
        eventClick: info => renderEventsForDate(info.event.startStr),
        datesSet: applyHoverStyles
      });

      calendar.render();
    });
  </script>
</body>
</html>
