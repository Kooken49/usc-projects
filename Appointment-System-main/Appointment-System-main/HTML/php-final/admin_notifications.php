<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$mysqli = new mysqli("localhost", "root", "", "dashboard_db");
if ($mysqli->connect_errno) {
    die("Failed to connect: " . $mysqli->connect_error);
}

// Fetch dropdown data
$students = $mysqli->query("SELECT StudentID, StudentName FROM Student ORDER BY StudentName");
$teachers = $mysqli->query("SELECT TeacherID, TeacherName FROM Teacher ORDER BY TeacherName");

$msg = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_notification'], $_POST['notification_id'])) {
        $notificationIdToDelete = (int) $_POST['notification_id'];
        $mysqli->query("DELETE FROM NotificationRecipient WHERE NotificationID = $notificationIdToDelete");
        $mysqli->query("DELETE FROM Notification WHERE NotificationID = $notificationIdToDelete");
        $msg = "Notification deleted successfully.";
    } elseif (isset($_POST['title'], $_POST['message'], $_POST['recipient_type'])) {
        $title = $mysqli->real_escape_string($_POST['title']);
        $message = $mysqli->real_escape_string($_POST['message']);
        $recipientType = $_POST['recipient_type'];
        $studentRecipientId = $_POST['student_recipient_id'] ?? null;
        $teacherRecipientId = $_POST['teacher_recipient_id'] ?? null;
        $groupTarget = $_POST['group_target'] ?? null;

        $stmt = $mysqli->prepare("INSERT INTO Notification (SenderType, Title, Message) VALUES ('Admin', ?, ?)");
        $stmt->bind_param("ss", $title, $message);
        $stmt->execute();
        $notificationId = $stmt->insert_id;
        $stmt->close();

      if ($recipientType === 'Student' && $studentRecipientId) {
          $stmt = $mysqli->prepare("INSERT INTO NotificationRecipient (NotificationID, RecipientType, StudentID) VALUES (?, 'Student', ?)");
          $stmt->bind_param("ii", $notificationId, $studentRecipientId);
      } elseif ($recipientType === 'Teacher' && $teacherRecipientId) {
          $stmt = $mysqli->prepare("INSERT INTO NotificationRecipient (NotificationID, RecipientType, TeacherID) VALUES (?, 'Teacher', ?)");
          $stmt->bind_param("ii", $notificationId, $teacherRecipientId);
      } elseif ($recipientType === 'Group' && $groupTarget) {
          $stmt = $mysqli->prepare("INSERT INTO NotificationRecipient (NotificationID, RecipientType, GroupTarget) VALUES (?, 'Group', ?)");
          $stmt->bind_param("is", $notificationId, $groupTarget);
      }

        if (isset($stmt)) {
            $stmt->execute();
            $stmt->close();
            $msg = "Notification sent successfully.";
        }
    }
}

// Fetch notifications
$result = $mysqli->query("
    SELECT 
        n.NotificationID,
        n.SenderType,
        n.Title,
        n.Message,
        n.CreatedAt,
        r.RecipientType,
        s.StudentName,
        t.TeacherName,
        r.GroupTarget
    FROM Notification n
    LEFT JOIN NotificationRecipient r ON n.NotificationID = r.NotificationID
    LEFT JOIN Student s ON r.StudentID = s.StudentID
    LEFT JOIN Teacher t ON r.TeacherID = t.TeacherID
    ORDER BY n.CreatedAt DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard - Notifications</title>
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
  <!-- Sidebar -->
  <aside class="fixed top-0 left-0 h-full w-64 bg-[#f1f1f1] shadow-lg p-6 z-10">
    <a href="index.html" class="flex items-center space-x-2 mb-8">
      <img src="img/logo.png" alt="Logo" class="h-12">
      <span class="text-xl font-bold text-blue-700">World AI</span>
    </a>
    <nav class="flex flex-col space-y-4 text-lg">
      <a href="admin_dashboard.php" class="hover:text-blue-600">Dashboard</a>
      <a href="user_manage.php" class="hover:text-blue-600">User Management</a>
      <a href="credit_manage.php" class="hover:text-blue-600">Credit Management</a>
      <a href="schedule_manage.php" class="hover:text-blue-600">Schedules</a>
      <a href="#" class="text-blue-700 font-semibold">Notifications</a>
      <a href="logout.php" class="text-red-600 hover:underline">Logout</a>
    </nav>
  </aside>

  <!-- Main content -->
  <div class="ml-64 flex flex-col w-full min-h-screen bg-white">
    <header class="bg-[#f1f1f1] shadow h-24 flex items-center justify-center px-6">
      <h1 class="text-3xl font-bold">Notification</h1>
    </header>

    <main class="flex-1 p-6 overflow-auto">
      <!-- Notification Sender Form -->
      <div class="max-w-2xl mx-auto bg-[#f9f9f9] p-6 rounded-2xl shadow-md mb-6">
        <h2 class="text-2xl font-bold mb-4 text-center text-blue-800">Send Notification</h2>
        <?php if (!empty($msg)): ?>
          <p class="text-green-600 text-center font-semibold mb-4"><?= $msg ?></p>
        <?php endif; ?>
        <form method="post" class="space-y-4">
          <div>
            <label for="title" class="block font-semibold mb-1">Title</label>
            <input type="text" id="title" name="title" required
              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>

          <div>
            <label for="message" class="block font-semibold mb-1">Message</label>
            <textarea id="message" name="message" rows="4" required
              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"></textarea>
          </div>

          <div>
            <label for="recipient_type" class="block font-semibold mb-1">Recipient Type</label>
            <select name="recipient_type" id="recipient_type" onchange="toggleRecipientFields()" required
              class="w-full px-4 py-2 border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-400">
              <option value="">Select recipient type</option>
              <option value="Student">Individual Student</option>
              <option value="Teacher">Individual Teacher</option>
              <option value="Group">Group</option>
            </select>
          </div>

          <!-- Student Selection Dropdown -->
          <div id="student_select" style="display:none;">
            <label class="block font-semibold mb-1">Select Student:</label>
            <select name="student_recipient_id" class="w-full px-4 py-2 border rounded-lg">
              <?php $students->data_seek(0); while ($row = $students->fetch_assoc()): ?>
                <option value="<?= $row['StudentID'] ?>"><?= htmlspecialchars($row['StudentName']) ?> (ID: <?= $row['StudentID'] ?>)</option>
              <?php endwhile; ?>
            </select>
          </div>

          <!-- Teacher Selection Dropdown -->
          <div id="teacher_select" style="display:none;">
            <label class="block font-semibold mb-1">Select Teacher:</label>
            <select name="teacher_recipient_id" class="w-full px-4 py-2 border rounded-lg">
              <?php $teachers->data_seek(0); while ($row = $teachers->fetch_assoc()): ?>
                <option value="<?= $row['TeacherID'] ?>"><?= htmlspecialchars($row['TeacherName']) ?> (ID: <?= $row['TeacherID'] ?>)</option>
              <?php endwhile; ?>
            </select>
          </div>

          <div id="teacher_select" style="display:none;">
            <label class="block font-semibold mb-1">Select Teacher:</label>
            <select name="recipient_id" class="w-full px-4 py-2 border rounded-lg">
              <?php $teachers->data_seek(0); while ($row = $teachers->fetch_assoc()): ?>
                <option value="<?= $row['TeacherID'] ?>"><?= htmlspecialchars($row['TeacherName']) ?> (ID: <?= $row['TeacherID'] ?>)</option>
              <?php endwhile; ?>
            </select>
          </div>

          <div id="group_select" style="display:none;">
            <label class="block font-semibold mb-1">Group:</label>
            <select name="group_target" class="w-full px-4 py-2 border rounded-lg">
              <option value="AllStudents">All Students</option>
              <option value="AllTeachers">All Teachers</option>
            </select>
          </div>

          <div class="text-center pt-4">
            <button type="submit"
              class="bg-blue-600 text-white px-6 py-2 rounded-full hover:bg-blue-700 transition duration-200">
              Send Notification
            </button>
          </div>
        </form>
      </div>

      <!-- Notification Table -->
      <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-md p-6">
        <h2 class="text-2xl font-bold mb-4 text-center text-blue-800">Previous Notifications</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left border border-gray-200">
            <thead class="bg-gray-100 text-gray-700 font-semibold">
              <tr>
                <th class="px-4 py-2 border">ID</th>
                <th class="px-4 py-2 border">Sender</th>
                <th class="px-4 py-2 border">Title</th>
                <th class="px-4 py-2 border">Message</th>
                <th class="px-4 py-2 border">Recipient</th>
                <th class="px-4 py-2 border">Sent At</th>
                <th class="px-4 py-2 border text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result->fetch_assoc()): ?>
              <tr class="border-t">
                <td class="px-4 py-2"><?= $row['NotificationID'] ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($row['SenderType']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($row['Title']) ?></td>
                <td class="px-4 py-2"><?= nl2br(htmlspecialchars($row['Message'])) ?></td>
                <td class="px-4 py-2">
                  <?php
                    if ($row['RecipientType'] === 'Student' && $row['StudentName']) {
                        echo htmlspecialchars($row['StudentName']);
                    } elseif ($row['RecipientType'] === 'Teacher' && $row['TeacherName']) {
                        echo htmlspecialchars($row['TeacherName']);
                    } elseif ($row['RecipientType'] === 'Group' && $row['GroupTarget']) {
                        echo htmlspecialchars($row['GroupTarget']);
                    } else {
                        echo "Unknown";
                    }
                  ?>
                </td>
                <td class="px-4 py-2"><?= $row['CreatedAt'] ?></td>
                <td class="px-4 py-2 text-center">
                  <form method="post" onsubmit="return confirm('Are you sure you want to delete this notification?');">
                    <input type="hidden" name="notification_id" value="<?= $row['NotificationID'] ?>">
                    <button type="submit" name="delete_notification" class="text-red-600 hover:underline">Delete</button>
                  </form>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <script>
    function toggleRecipientFields() {
      const type = document.getElementById("recipient_type").value;
      document.getElementById("student_select").style.display = (type === "Student") ? "block" : "none";
      document.getElementById("teacher_select").style.display = (type === "Teacher") ? "block" : "none";
      document.getElementById("group_select").style.display = (type === "Group") ? "block" : "none";
    }
  </script>
</body>
</html>
