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

// Fetch notifications
$notifQuery = $conn->prepare("
    SELECT Title, Message, CreatedAt
    FROM NotificationRecipient nr
    JOIN Notification n ON nr.NotificationID = n.NotificationID
    WHERE (nr.RecipientType = 'Teacher' AND nr.TeacherID = ?)
       OR (nr.RecipientType = 'Group' AND nr.GroupTarget = 'AllTeachers')
    ORDER BY n.CreatedAt DESC
");
$notifQuery->bind_param("i", $teacherID);
$notifQuery->execute();
$notifResult = $notifQuery->get_result();
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
    .font-georgia {
      font-family: Georgia, serif;
    }
    html, body {
      height: 100%;
    }
  </style>
</head>

<body class="text-gray-800 font-georgia">
  <div class="flex h-screen overflow-hidden">
    <!-- ========== Sidebar ========== -->
    <aside class="w-64 fixed top-0 left-0 h-full bg-[#f1f1f1] shadow-lg p-6 z-10">
      <a href="teacher_dashboard.php" class="flex items-center space-x-2 mb-8">
        <img src="img/logo.png" alt="Logo" class="h-12">
        <span class="text-xl font-bold text-blue-700">World AI</span>
      </a>
      <nav class="flex flex-col space-y-4 text-lg">
        <a href="#" class="text-blue-700 font-semibold">Dashboard</a>
        <a href="logout.php" class="hover:text-red-600">Logout</a>
      </nav>
    </aside>

    <!-- ========== Main Content ========== -->
    <div class="ml-64 flex flex-col w-full bg-white">
      <!-- ========== Header ========== -->
      <header class="bg-[#f1f1f1] shadow h-24 flex items-center justify-center px-6">
        <h1 class="text-3xl font-bold">Welcome, <?php echo htmlspecialchars($_SESSION['teacher_name']); ?></h1>
      </header>

      <!-- ========== Page Content ========== -->
      <main class="flex flex-1 overflow-hidden p-6 gap-6">
        <!-- Calendar -->
        <div id="calendar" class="flex-1 bg-[#f1f1f1] rounded-lg shadow p-4 h-[75vh]"></div>

        <!-- Event panel -->
        <aside id="eventListPanel" class="w-96 bg-gray-100 p-6 border rounded shadow-inner overflow-auto">
          <h2 class="text-xl font-bold mb-4">Event Details</h2>
          <p class="text-gray-500 italic">Click a day to see scheduled events.</p>
        </aside>
      </main>

      <!-- ========== Schedule Table ========== -->
      <section class="px-6 pb-8">
        <h2 class="text-2xl font-bold mb-4">Your Class Schedule</h2>
        <table class="w-full table-auto border border-gray-300 text-base">
          <thead class="bg-gray-200">
            <tr>
              <th class="p-2 border text-left">Date</th>
              <th class="p-2 border text-left">Start Time</th>
              <th class="p-2 border text-left">End Time</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($scheduleData as $s): ?>
              <tr>
                <td class="p-2 border"><?php echo $s['Date']; ?></td>
                <td class="p-2 border"><?php echo $s['TimeStart']; ?></td>
                <td class="p-2 border"><?php echo $s['TimeEnd']; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>

      <!-- ========== Notifications ========== -->
      <section class="px-6 pb-10">
        <h2 class="text-2xl font-bold mb-4">Your Notifications</h2>
        <ul class="space-y-4">
          <?php while ($notif = $notifResult->fetch_assoc()): ?>
            <li class="p-4 bg-gray-100 rounded shadow">
              <strong class="block text-lg"><?php echo htmlspecialchars($notif['Title']); ?></strong>
              <p><?php echo nl2br(htmlspecialchars($notif['Message'])); ?></p>
              <small class="text-gray-500 block mt-1"><?php echo $notif['CreatedAt']; ?></small>
            </li>
          <?php endwhile; ?>
        </ul>
      </section>
    </div>
  </div>

  <!-- ========== FullCalendar Script ========== -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const calendarEl = document.getElementById('calendar');
      const eventPanel = document.getElementById('eventListPanel');
      const scheduleData = <?php echo json_encode($scheduleData); ?>;

      const summarizedEvents = {};
      scheduleData.forEach(item => {
        const date = item.Date;
        const title = `${item.TimeStart} - ${item.TimeEnd}`;
        if (!summarizedEvents[date]) summarizedEvents[date] = [];
        summarizedEvents[date].push({ title });
      });

      const fullEvents = Object.entries(summarizedEvents).map(([date, items]) => ({
        title: `${items.length} class${items.length > 1 ? 'es' : ''}`,
        start: date,
        display: 'auto'
      }));

      function renderEventsForDate(dateStr) {
        const eventsForDay = summarizedEvents[dateStr] || [];
        eventPanel.innerHTML = eventsForDay.length ? `
          <h2 class="text-xl font-bold mb-4">Classes on ${dateStr}</h2>
          <ul class="list-disc pl-5 space-y-2">
            ${eventsForDay.map(e => `<li>${e.title}</li>`).join('')}
          </ul>
        ` : `
          <h2 class="text-xl font-bold mb-4">Classes on ${dateStr}</h2>
          <p class="text-gray-500 italic">No classes scheduled.</p>
        `;
      }

      const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: fullEvents,
        dateClick: function (info) {
          renderEventsForDate(info.dateStr);
        },
        eventClick: function (info) {
          renderEventsForDate(info.event.startStr);
        },
        datesSet: function () {
          document.querySelectorAll('.fc-daygrid-day').forEach(cell => {
            cell.classList.add(
              'transition', 'duration-200', 'ease-in-out',
              'hover:bg-blue-100', 'cursor-pointer',
              'rounded', 'hover:shadow-md', 'hover:scale-105'
            );
          });
        }
      });

      calendar.render();
    });
  </script>
</body>

</html>
