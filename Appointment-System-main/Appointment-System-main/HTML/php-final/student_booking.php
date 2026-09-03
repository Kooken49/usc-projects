<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");

if (!isset($_SESSION['student_logged_in'])) {
    header("Location: user_login.php");
    exit();
}

$studentId = $_SESSION['student_id'];
date_default_timezone_set('Asia/Manila');
$now = new DateTime();
$cutoff = new DateTime($now->format('Y-m-d') . ' 20:00:00');

$feedback = "";

// Get current credits and pending booking count
$creditQuery = $conn->prepare("SELECT SUM(CreditAmount) AS TotalCredits FROM BookingCredits WHERE StudentID = ? AND ExpiryDate >= CURDATE()");
$creditQuery->bind_param("i", $studentId);
$creditQuery->execute();
$creditResult = $creditQuery->get_result()->fetch_assoc();
$totalCredits = $creditResult['TotalCredits'] ?? 0;

$pendingStmt = $conn->prepare("SELECT COUNT(*) AS BookingCount FROM StudentBooking WHERE StudentID = ? AND Status = 'Pending'");
$pendingStmt->bind_param("i", $studentId);
$pendingStmt->execute();
$pendingResult = $pendingStmt->get_result()->fetch_assoc();
$pendingBookings = $pendingResult['BookingCount'] ?? 0;

$remainingCredits = max(0, $totalCredits - $pendingBookings);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $date = $_POST['class_date'];
    $timeStart = $_POST['start_time'];

    if ($totalCredits <= 0) {
        $feedback = "<p class='text-red-600 font-semibold'>You do not have enough credits to book a class.</p>";
    } elseif ($pendingBookings >= $totalCredits) {
        $feedback = "<p class='text-red-600 font-semibold'>Booking denied. You have reached your credit limit of $totalCredits bookings.</p>";
    } else {
        $startTime = new DateTime($timeStart);
        $endTime = clone $startTime;
        $endTime->modify('+50 minutes');
        $timeStartFormatted = $startTime->format('H:i:s');
        $timeEndFormatted = $endTime->format('H:i:s');
        $requestedDateTime = new DateTime("$date $timeStartFormatted");

        $earliestAllowed = ($now < $cutoff)
            ? (clone $now)->modify('+1 day')->setTime(0, 0)
            : (clone $now)->modify('+2 days')->setTime(20, 0);

        if ($requestedDateTime < $earliestAllowed) {
            $feedback = "<p class='text-red-600 font-semibold'>Booking not allowed. You can only book from " . $earliestAllowed->format('Y-m-d H:i') . " onward.</p>";
        } else {
            $conflict = $conn->prepare("
                SELECT 1 FROM StudentBooking
                WHERE StudentID = ? AND PreferredDate = ? 
                AND (
                    (PreferredTimeStart <= ? AND PreferredTimeEnd > ?) OR
                    (PreferredTimeStart < ? AND PreferredTimeEnd >= ?) OR
                    (PreferredTimeStart >= ? AND PreferredTimeEnd <= ?)
                )
                AND Status IN ('Pending', 'Approved')
            ");
            $conflict->bind_param("isssssss", $studentId, $date,
                                  $timeStartFormatted, $timeStartFormatted,
                                  $timeEndFormatted, $timeEndFormatted,
                                  $timeStartFormatted, $timeEndFormatted);
            $conflict->execute();
            $conflictResult = $conflict->get_result();

            if ($conflictResult->num_rows > 0) {
                $feedback = "<p class='text-red-600 font-semibold'>Booking conflict detected. You already have a booking during this time.</p>";
            } else {
                $stmt = $conn->prepare("INSERT INTO StudentBooking (StudentID, PreferredDate, PreferredTimeStart, PreferredTimeEnd) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $studentId, $date, $timeStartFormatted, $timeEndFormatted);

                if ($stmt->execute()) {
                    $feedback = "<p class='text-green-600 font-semibold'>Booking submitted for $date from $timeStartFormatted to $timeEndFormatted!</p>";
                } else {
                    $feedback = "<p class='text-red-600 font-semibold'>Error: " . $conn->error . "</p>";
                }
                $stmt->close();
            }
        }
    }
}
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
        <a href="student_dashboard.php" class="hover:text-blue-600">Dashboard</a>
        <a href="#" class="text-blue-700 font-semibold">Booking</a>
        <a href="student_inbox.php" class="hover:text-blue-600">Inbox</a>
        <a href="logout.php" class="hover:text-red-600">Logout</a>
      </nav>
    </div>
  </aside>

  <!-- Main Content -->
  <div class="flex-1 bg-white flex flex-col min-h-screen">
    <header class="bg-[#f1f1f1] flex justify-center items-center px-6 py-6 shadow h-24">
      <h1 class="text-3xl font-bold font-georgia">Student Booking</h1>
    </header>

    <section class="p-8 bg-white max-w-3xl mx-auto w-full">
      <div class="bg-[#f9f9f9] p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-4 text-blue-700">Book a Class</h2>

        <!-- Credit Display -->
        <div class="mb-4 p-4 bg-indigo-50 border-l-4 border-indigo-500 text-indigo-800 rounded">
          <p class="font-semibold">
            Available Booking Credits: 
            <span class="font-bold text-indigo-700"><?= $remainingCredits ?></span>
            (<?= $totalCredits ?> total - <?= $pendingBookings ?> pending)
          </p>
        </div>

        <?= $feedback ?>

        <!-- Booking Form -->
        <form method="POST" class="space-y-6">
          <div id="date_wrapper" class="cursor-pointer">
            <label for="class_date" class="block text-lg font-semibold mb-2">Select Date</label>
            <div class="relative">
              <input type="date" id="class_date" name="class_date" required
                class="w-full border border-gray-300 rounded-md p-3 focus:outline-none focus:ring-2 focus:ring-blue-400 select-none">
            </div>
          </div>

          <div>
            <label for="start_time" class="block text-lg font-semibold mb-2">Start Time</label>
            <select name="start_time" id="start_time" onchange="updateEndTime()" required
              class="w-full border border-gray-300 rounded-md p-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
              <?php
              for ($h = 6; $h <= 23; $h++) {
                $hour = str_pad($h, 2, "0", STR_PAD_LEFT) . ":00";
                echo "<option value='$hour'>$hour</option>";
              }
              ?>
            </select>
          </div>

          <div id="end_time_display" class="text-lg font-semibold text-gray-700"></div>

          <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-md transition duration-200">
            Book Class
          </button>
        </form>
      </div>
    </section>

    <!-- Bookings Table -->
    <section class="p-8 bg-gray-50 max-w-6xl mx-auto w-full mt-8">
      <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-6 text-blue-700">My Booking Requests</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full table-auto border border-gray-200">
            <thead class="bg-blue-100 text-blue-900">
              <tr>
                <th class="px-4 py-3 text-left">Date</th>
                <th class="px-4 py-3 text-left">Start Time</th>
                <th class="px-4 py-3 text-left">End Time</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Requested At</th>
              </tr>
            </thead>
            <tbody class="text-gray-700">
              <?php
              $result = $conn->prepare("SELECT PreferredDate, PreferredTimeStart, PreferredTimeEnd, Status, CreatedAt 
                                        FROM StudentBooking 
                                        WHERE StudentID = ? 
                                        ORDER BY CreatedAt DESC");
              $result->bind_param("i", $studentId);
              $result->execute();
              $bookings = $result->get_result();

              if ($bookings->num_rows > 0) {
                while ($row = $bookings->fetch_assoc()) {
                  echo "<tr class='border-t'>
                          <td class='px-4 py-3'>{$row['PreferredDate']}</td>
                          <td class='px-4 py-3'>{$row['PreferredTimeStart']}</td>
                          <td class='px-4 py-3'>{$row['PreferredTimeEnd']}</td>
                          <td class='px-4 py-3'>
                            <span class='px-2 py-1 rounded text-sm " . 
                            ($row['Status'] === 'Pending' ? "bg-yellow-200 text-yellow-800" :
                             ($row['Status'] === 'Approved' ? "bg-green-200 text-green-800" :
                             "bg-red-200 text-red-800")) . "'>{$row['Status']}</span>
                          </td>
                          <td class='px-4 py-3'>{$row['CreatedAt']}</td>
                        </tr>";
                }
              } else {
                echo "<tr><td colspan='5' class='px-4 py-3 text-center'>No bookings yet.</td></tr>";
              }

              $result->close();
              $conn->close();
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>

  <!-- JS -->
  <script>
    function updateEndTime() {
      const start = document.getElementById("start_time").value;
      const [hour, minute] = start.split(":").map(Number);

      let startDate = new Date();
      startDate.setHours(hour);
      startDate.setMinutes(minute);

      let endDate = new Date(startDate.getTime() + 50 * 60000);
      const endHour = String(endDate.getHours()).padStart(2, '0');
      const endMin = String(endDate.getMinutes()).padStart(2, '0');

      document.getElementById("end_time_display").innerText = `End Time: ${endHour}:${endMin}`;
    }

    window.onload = updateEndTime;

    document.getElementById("date_wrapper").addEventListener("click", function () {
      const dateInput = document.getElementById("class_date");
      dateInput.showPicker?.();
      dateInput.focus();
    });
  </script>
</body>
</html>
