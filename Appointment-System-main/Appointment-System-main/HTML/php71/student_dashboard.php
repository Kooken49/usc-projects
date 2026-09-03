<?php
session_start();
if (!isset($_SESSION['student_logged_in'])) {
    header("Location: user_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "dashboard_db");
$studentID = $_SESSION['student_id'];

// Fetch class schedule
$scheduleData = [];
$scheduleQuery = $conn->prepare("SELECT Date, TimeStart, TimeEnd FROM ScheduleSlot WHERE StudentID = ?");
$scheduleQuery->bind_param("i", $studentID);
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

// Prepare events for calendar summary
$calendarEvents = [];
foreach ($dateMap as $date => $items) {
    $calendarEvents[] = [
        'title' => count($items) . ' class' . (count($items) > 1 ? 'es' : ''),
        'start' => $date,
        'display' => 'auto'
    ];
}

// Fetch notifications
$notifQuery = $conn->prepare("
    SELECT Title, Message, CreatedAt
    FROM NotificationRecipient nr
    JOIN Notification n ON nr.NotificationID = n.NotificationID
    WHERE (nr.RecipientType = 'Student' AND nr.StudentID = ?)
       OR (nr.RecipientType = 'Group' AND nr.GroupTarget = 'AllStudents')
    ORDER BY n.CreatedAt DESC
");
$notifQuery->bind_param("i", $studentID);
$notifQuery->execute();
$notifResult = $notifQuery->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student's Page</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
  <style>
    .font-georgia { font-family: Georgia, serif; }
    html, body { height: 100%; }
  </style>
</head>
<body class="h-screen flex text-gray-800 font-georgia">

  <!-- Sidebar -->
  <aside class="w-64 bg-[#f1f1f1] shadow-lg p-6 flex flex-col justify-between">
    <div>
      <a href="student_dashboard.php" class="flex items-center space-x-2 mb-8">
        <img src="img/logo.png" alt="Logo" class="h-12">
        <span class="text-xl font-bold text-blue-700">World AI</span>
      </a>

      <nav class="flex flex-col space-y-4 text-lg">
        <a href="#" class="text-blue-700 font-semibold">Dashboard</a>
        <a href="student_booking.php" class="hover:text-blue-600">Booking</a>
        <a href="student_inbox.php" class="hover:text-blue-600">Inbox</a>
        <a href="logout.php" class="hover:text-red-600">Logout</a>
      </nav>
    </div>
  </aside>

  <!-- Main Content -->
  <div class="flex-1 bg-white flex flex-col min-h-screen">
    <!-- Header -->
    <header class="bg-[#f1f1f1] flex justify-between items-center px-6 py-6 shadow h-24">
      <h1 class="text-3xl font-bold font-georgia">Welcome, <?= htmlspecialchars($_SESSION['student_name']) ?></h1>
    </header>

    <!-- Content -->
    <main class="flex flex-1 overflow-hidden">
      <!-- Calendar -->
      <div class="flex-1 p-6 overflow-auto">
        <div id="calendar" class="bg-[#f1f1f1] rounded-lg shadow p-4 h-[75vh]"></div>
      </div>

      <!-- Event List Panel -->
      <aside id="eventListPanel" class="w-96 bg-gray-100 p-6 border-l shadow-inner overflow-auto">
        <h2 class="text-xl font-bold mb-4">Event Details</h2>
        <p class="text-gray-500 italic">Click a day to see scheduled events.</p>
      </aside>
    </main>

    <!-- Notifications -->
    <section class="p-6 border-t bg-gray-50">
      <h2 class="text-2xl font-bold mb-4">Your Notifications</h2>
      <ul class="space-y-4">
        <?php while ($notif = $notifResult->fetch_assoc()): ?>
          <li class="bg-white shadow p-4 rounded border-l-4 border-blue-500">
            <h3 class="font-semibold text-lg"><?= htmlspecialchars($notif['Title']) ?></h3>
            <p class="text-gray-700"><?= nl2br(htmlspecialchars($notif['Message'])) ?></p>
            <small class="text-gray-500"><?= $notif['CreatedAt'] ?></small>
          </li>
        <?php endwhile; ?>
      </ul>
    </section>
  </div>

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
