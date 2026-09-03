<?php
session_start();
if (!isset($_SESSION['student_logged_in'])) {
    header("Location: user_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "dashboard_db");
if ($conn->connect_errno) die("Connection failed: " . $conn->connect_error);

$studentID = $_SESSION['student_id'];

$query = $conn->prepare("
    SELECT n.Title, n.Message, n.CreatedAt, n.SenderType,
           CASE 
               WHEN n.SenderType = 'Admin' THEN 'Admin'
               WHEN n.SenderType = 'Teacher' THEN 'Teacher'
               WHEN n.SenderType = 'Student' THEN 'Student'
               ELSE 'System'
           END AS SenderName
    FROM NotificationRecipient nr
    JOIN Notification n ON nr.NotificationID = n.NotificationID
    WHERE (nr.RecipientType = 'Teacher' AND nr.TeacherID = ?)
       OR (nr.RecipientType = 'Group' AND nr.GroupTarget = 'AllTeachers')
    ORDER BY n.CreatedAt DESC
");

$query->bind_param("i", $studentID);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student's Page - Inbox</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
  <style>
    .font-georgia { font-family: Georgia, serif; }
    html, body { height: 100%; }
  </style>
</head>
<body class="h-screen flex text-gray-800 font-georgia">

  <!-- ======================== Sidebar ======================== -->
  <aside class="w-64 bg-[#f1f1f1] shadow-lg p-6 flex flex-col justify-between">
    <div>
      <a href="student_dashboard.php" class="flex items-center space-x-2 mb-8">
        <img src="img/logo.png" alt="Logo" class="h-12">
        <span class="text-xl font-bold text-blue-700">World AI</span>
      </a>

      <nav class="flex flex-col space-y-4 text-lg">
        <a href="student_dashboard.php" class="hover:text-blue-600">Dashboard</a>
        <a href="student_booking.php" class="hover:text-blue-600">Booking</a>
        <a href="#" class="text-blue-700 font-semibold">Inbox</a>
        <a href="logout.php" class="hover:text-red-600">Logout</a>
      </nav>
    </div>
  </aside>

  <!-- ======================== Main Content ======================== -->
  <div class="flex-1 bg-white flex flex-col min-h-screen">

    <!-- ======================== Header ======================== -->
    <header class="bg-[#f1f1f1] shadow h-24 flex items-center justify-center px-6 flex-shrink-0">
      <h1 class="text-3xl font-bold">Student Inbox</h1>
    </header>

    <!-- ======================== Inbox Table ======================== -->
    <main class="flex-1 p-6 overflow-y-auto">
      <div class="bg-white border rounded-lg shadow p-4">
        <table class="min-w-full table-auto border-collapse">
          <thead>
            <tr class="bg-gray-100 border-b">
              <th class="text-left p-3 text-lg font-semibold">Title</th>
              <th class="text-left p-3 text-lg font-semibold">Message</th>
              <th class="text-left p-3 text-lg font-semibold">Sent From</th>
              <th class="text-left p-3 text-lg font-semibold">Sent Date</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr class="border-b hover:bg-gray-50 align-top">
                <td class="p-3 font-medium w-1/5"><?php echo htmlspecialchars($row['Title']); ?></td>
                <td class="p-3 w-2/5"><?php echo nl2br(htmlspecialchars($row['Message'])); ?></td>
                <td class="p-3 w-1/5"><?php echo htmlspecialchars($row['SenderName']); ?></td>
                <td class="p-3 w-1/5"><?php echo htmlspecialchars($row['CreatedAt']); ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </main>

  </div>
</body>
</html>
