<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "dashboard_db");

if ($conn->connect_errno) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $bookingId = $_POST['BookingID'];

    // APPROVE
    if (isset($_POST['approve']) && isset($_POST['TeacherID'])) {
        $teacherId = $_POST['TeacherID'];

        // Get student booking details
        $stmt = $conn->prepare("SELECT StudentID, PreferredDate, PreferredTimeStart, PreferredTimeEnd FROM StudentBooking WHERE BookingID = ?");
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $bookingResult = $stmt->get_result();
        if (!$bookingResult || $bookingResult->num_rows === 0) {
            die("Booking not found.");
        }
        $booking = $bookingResult->fetch_assoc();
        $stmt->close();

        // Insert into ScheduleSlot
        $stmt = $conn->prepare("INSERT INTO ScheduleSlot (TeacherID, StudentID, Date, TimeStart, TimeEnd) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $teacherId, $booking['StudentID'], $booking['PreferredDate'], $booking['PreferredTimeStart'], $booking['PreferredTimeEnd']);
        $stmt->execute();
        $stmt->close();

        // Update booking status
        $conn->query("UPDATE StudentBooking SET Status = 'Approved' WHERE BookingID = $bookingId");

        // Deduct 1 credit from BookingCredits
        $creditFetch = $conn->prepare("
            SELECT CreditID, CreditAmount 
            FROM BookingCredits 
            WHERE StudentID = ? AND ExpiryDate >= CURDATE() AND CreditAmount > 0
            ORDER BY ExpiryDate ASC
        ");
        $creditFetch->bind_param("i", $booking['StudentID']);
        $creditFetch->execute();
        $creditResult = $creditFetch->get_result();

        $remainingToDeduct = 1;

        while ($remainingToDeduct > 0 && $row = $creditResult->fetch_assoc()) {
            $creditID = $row['CreditID'];
            $amount = $row['CreditAmount'];

            if ($amount >= $remainingToDeduct) {
                $newAmount = $amount - $remainingToDeduct;
                $update = $conn->prepare("UPDATE BookingCredits SET CreditAmount = ? WHERE CreditID = ?");
                $update->bind_param("ii", $newAmount, $creditID);
                $update->execute();
                $update->close();
                $remainingToDeduct = 0;
            } else {
                $remainingToDeduct -= $amount;
                $update = $conn->prepare("UPDATE BookingCredits SET CreditAmount = 0 WHERE CreditID = ?");
                $update->bind_param("i", $creditID);
                $update->execute();
                $update->close();
            }
        }
        $creditFetch->close();

        // Send notifications
        $title = "Schedule Confirmed";
        $message = "Your schedule on {$booking['PreferredDate']} from {$booking['PreferredTimeStart']} to {$booking['PreferredTimeEnd']} has been approved.";
        $conn->query("INSERT INTO Notification (SenderType, Title, Message) VALUES ('Admin', '$title', '$message')");
        $notificationId = $conn->insert_id;

        $conn->query("INSERT INTO NotificationRecipient (NotificationID, RecipientType, StudentID) VALUES ($notificationId, 'Student', {$booking['StudentID']})");
        $conn->query("INSERT INTO NotificationRecipient (NotificationID, RecipientType, TeacherID) VALUES ($notificationId, 'Teacher', $teacherId)");
    }

    // DENY
    elseif (isset($_POST['deny'])) {
        $stmt = $conn->prepare("SELECT StudentID, PreferredDate, PreferredTimeStart FROM StudentBooking WHERE BookingID = ?");
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $title = "Schedule Request Denied";
        $message = "Your request for {$booking['PreferredDate']} at {$booking['PreferredTimeStart']} was denied by the admin.";
        $conn->query("INSERT INTO Notification (SenderType, Title, Message) VALUES ('Admin', '$title', '$message')");
        $notificationId = $conn->insert_id;

        $conn->query("INSERT INTO NotificationRecipient (NotificationID, RecipientType, StudentID) VALUES ($notificationId, 'Student', {$booking['StudentID']})");

        $conn->query("DELETE FROM StudentBooking WHERE BookingID = $bookingId");
    }
}

// Fetch pending bookings and teachers
$bookings = $conn->query("
    SELECT sb.BookingID, sb.StudentID, s.StudentName, sb.PreferredDate, sb.PreferredTimeStart, sb.PreferredTimeEnd
    FROM StudentBooking sb
    JOIN Student s ON sb.StudentID = s.StudentID
    WHERE sb.Status = 'Pending'
");

$teacherList = $conn->query("SELECT TeacherID, TeacherName FROM Teacher")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Schedule Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .font-georgia {
      font-family: Georgia, serif;
    }

    html, body {
      height: 100%;
    }
  </style>
</head>

<body class="h-screen flex text-gray-800 font-georgia">

    <aside class="fixed top-0 left-0 h-full w-64 bg-[#f1f1f1] shadow-lg p-6 z-10">
    <a href="index.html" class="flex items-center space-x-2 mb-8">
      <img src="img/logo.png" alt="Logo" class="h-12">
      <span class="text-xl font-bold text-blue-700">World AI</span>
    </a>
    <nav class="flex flex-col space-y-4 text-lg">
      <a href="admin_dashboard.php" class="hover:text-blue-600">Dashboard</a>
      <a href="admin_manage.php" class="hover:text-blue-600">Admins</a>
      <a href="user_manage.php" class="hover:text-blue-600">User Management</a>
      <a href="credit_manage.php" class="hover:text-blue-600">Credit Management</a>
      <a href="#" class="text-blue-700 font-semibold">Schedules</a>
      <a href="admin_notifications.php" class="hover:text-blue-600">Notifications</a>
      <a href="logout.php" class="text-red-600 hover:underline">Logout</a>
    </nav>
  </aside>

  <!-- MAIN CONTENT WRAPPER (with left margin so it doesn't sit under the sidebar) -->
  <div class="ml-64 flex flex-col w-full min-h-screen bg-white">

    <!-- CONSISTENT HEADER -->
    <header class="bg-[#f1f1f1] shadow h-24 flex items-center justify-center px-6">
      <h1 class="text-3xl font-bold">Admin Management</h1>
    </header>

    <!-- PAGE-SPECIFIC CONTENT GOES HERE -->
    <main class="flex-1 p-6 overflow-auto">
      <section class="mb-10">
        <h2 class="text-2xl font-bold mb-4">Schedule Requests</h2>
        <div class="overflow-x-auto bg-white shadow rounded-lg">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-100 text-blue-900">
              <tr>
                <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Student</th>
                <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Date</th>
                <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Time</th>
                <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Teacher</th>
                <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <?php while ($row = $bookings->fetch_assoc()): ?>
              <tr>
                <td class="px-6 py-4 text-sm"><?= htmlspecialchars($row['StudentName']) ?></td>
                <td class="px-6 py-4 text-sm"><?= $row['PreferredDate'] ?></td>
                <td class="px-6 py-4 text-sm"><?= $row['PreferredTimeStart'] ?> - <?= $row['PreferredTimeEnd'] ?></td>
                <td class="px-6 py-4 text-sm">
                  <form method="POST" class="flex items-center space-x-2">
                    <input type="hidden" name="BookingID" value="<?= $row['BookingID'] ?>">
                    <select name="TeacherID" required class="border rounded px-2 py-1 text-sm">
                      <option value="">Select</option>
                      <?php foreach ($teacherList as $teacher): ?>
                        <option value="<?= $teacher['TeacherID'] ?>"><?= htmlspecialchars($teacher['TeacherName']) ?></option>
                      <?php endforeach; ?>
                    </select>
                </td>
                <td class="px-6 py-4 text-sm flex gap-2">
                    <button type="submit" name="approve" class="bg-green-500 text-white px-4 py-1 rounded hover:bg-green-600">Approve</button>
                  </form>
                  <form method="POST" onsubmit="return confirm('Are you sure to deny this request?');">
                    <input type="hidden" name="BookingID" value="<?= $row['BookingID'] ?>">
                    <button type="submit" name="deny" class="bg-red-500 text-white px-4 py-1 rounded hover:bg-red-600">Deny</button>
                  </form>
                </td>
              </tr>
              <?php endwhile; ?>
              <?php if ($bookings->num_rows === 0): ?>
              <tr>
                <td colspan="5" class="text-center py-4 text-gray-500">No pending requests.</td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
