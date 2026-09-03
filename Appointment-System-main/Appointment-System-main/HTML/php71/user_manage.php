<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");
if ($conn->connect_errno) die("Connection failed: " . $conn->connect_error);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Add Student
    if (isset($_POST['add_student'])) {
        $name = $_POST['StudentName'];
        $email = $_POST['StudentEmail'];
        $num = $_POST['StudentNum'];
        $pass = $_POST['Password'];
        $plan = $_POST['Plan'] ?? 'Basic';
        $level = $_POST['Level'] ?? 'Beginner';

        if ($name && $email && $pass) {
            $stmt = $conn->prepare("INSERT INTO Student (StudentName, StudentEmail, StudentNum, Password, Plan, Level) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $num, $pass, $plan, $level);
            $stmt->execute();
            $stmt->close();
        }
    }
    // Delete Student
    elseif (isset($_POST['delete_student'])) {
        $id = $_POST['StudentID'];

        $conn->prepare("DELETE FROM ScheduleSlot WHERE StudentID = ?")->bind_param("i", $id)->execute();
        $conn->prepare("DELETE FROM NotificationRecipient WHERE StudentID = ?")->bind_param("i", $id)->execute();
        $conn->prepare("DELETE FROM Token WHERE StudentID = ?")->bind_param("i", $id)->execute();
        $conn->prepare("DELETE FROM Student WHERE StudentID = ?")->bind_param("i", $id)->execute();
        $conn->query("DELETE FROM Notification WHERE NotificationID NOT IN (SELECT DISTINCT NotificationID FROM NotificationRecipient)");
    }
    // Add Teacher
    elseif (isset($_POST['add_teacher'])) {
        $name = $_POST['TeacherName'];
        $email = $_POST['TeacherEmail'];
        $num = $_POST['TeacherNum'];
        $pass = $_POST['Password'];

        if ($name && $email && $pass) {
            $stmt = $conn->prepare("INSERT INTO Teacher (TeacherName, TeacherEmail, TeacherNum, Password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $num, $pass);
            $stmt->execute();
            $stmt->close();
        }
    }
    // Delete Teacher
    elseif (isset($_POST['delete_teacher'])) {
        $id = $_POST['TeacherID'];

        $conn->prepare("DELETE FROM ScheduleSlot WHERE TeacherID = ?")->bind_param("i", $id)->execute();
        $conn->prepare("DELETE FROM NotificationRecipient WHERE TeacherID = ?")->bind_param("i", $id)->execute();
        $conn->prepare("DELETE FROM Teacher WHERE TeacherID = ?")->bind_param("i", $id)->execute();
        $conn->query("DELETE FROM Notification WHERE NotificationID NOT IN (SELECT DISTINCT NotificationID FROM NotificationRecipient)");
    }
}

$students = $conn->query("SELECT * FROM Student");
$teachers = $conn->query("SELECT * FROM Teacher");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .font-georgia { font-family: Georgia, serif; }
  </style>
</head>
<body class="h-screen flex text-gray-800 font-georgia">

  <!-- Sidebar -->
     <div class="flex"></div>
    <aside class="fixed top-0 left-0 h-full w-64 bg-[#f1f1f1] shadow-lg p-6 z-10">
    <a href="index.html" class="flex items-center space-x-2 mb-8">
      <img src="img/logo.png" alt="Logo" class="h-12">
      <span class="text-xl font-bold text-blue-700">World AI</span>
    </a>
    <nav class="flex flex-col space-y-4 text-lg">
      <a href="admin_dashboard.php" class="hover:text-blue-600">Dashboard</a>
      <a href="admin_manage.php" class="hover:text-blue-600">Admins</a>
      <a href="#" class="text-blue-700 font-semibold">User Management</a>
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
      <h1 class="text-3xl font-bold">User Management</h1>
    </header>

    <!-- PAGE-SPECIFIC CONTENT GOES HERE -->
    <main class="flex-1 p-6 overflow-auto">
      <!-- Student Management Section -->
      <section class="bg-gray-50 p-6 rounded-lg shadow-md max-w-5xl mx-auto">
        <h2 class="text-2xl font-semibold mb-4">User Management</h2>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          <input name="StudentName" placeholder="Name" required class="border rounded px-4 py-2">
          <input name="StudentEmail" type="email" placeholder="Email" required class="border rounded px-4 py-2">
          <input name="StudentNum" placeholder="Contact" class="border rounded px-4 py-2">
          <input name="Password" type="password" placeholder="Password" required class="border rounded px-4 py-2">
          <select name="Plan" class="border rounded px-4 py-2">
            <option disabled selected>Choose Plan</option>
            <option value="Basic">Basic</option>
            <option value="Standard">Standard</option>
            <option value="Premium">Premium</option>
          </select>
          <select name="Level" class="border rounded px-4 py-2">
            <option disabled selected>Choose Level</option>
            <option value="Beginner">Beginner</option>
            <option value="Intermediate">Intermediate</option>
            <option value="Advanced">Advanced</option>
          </select>
          <div class="md:col-span-2">
            <button name="add_student" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 mt-2">Add Student</button>
          </div>
        </form>

        <div class="overflow-x-auto">
          <table class="min-w-full border">
            <thead>
              <tr class="bg-gray-200 text-sm uppercase tracking-wider">
                <th class="px-4 py-2 border">ID</th>
                <th class="px-4 py-2 border">Name</th>
                <th class="px-4 py-2 border">Email</th>
                <th class="px-4 py-2 border">Contact</th>
                <th class="px-4 py-2 border">Plan</th>
                <th class="px-4 py-2 border">Level</th>
                <th class="px-4 py-2 border">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $students->fetch_assoc()): ?>
              <tr class="hover:bg-gray-100">
                <td class="px-4 py-2 border"><?= $row['StudentID'] ?></td>
                <td class="px-4 py-2 border"><?= htmlspecialchars($row['StudentName']) ?></td>
                <td class="px-4 py-2 border"><?= htmlspecialchars($row['StudentEmail']) ?></td>
                <td class="px-4 py-2 border"><?= htmlspecialchars($row['StudentNum']) ?></td>
                <td class="px-4 py-2 border"><?= $row['Plan'] ?></td>
                <td class="px-4 py-2 border"><?= $row['Level'] ?></td>
                <td class="px-4 py-2 border">
                  <form method="POST" onsubmit="return confirm('Delete this student?');">
                    <input type="hidden" name="StudentID" value="<?= $row['StudentID'] ?>">
                    <button name="delete_student" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</button>
                  </form>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Teacher Management Section -->
      <section class="bg-gray-50 p-6 rounded-lg shadow-md max-w-5xl mx-auto">
        <h2 class="text-2xl font-semibold mb-4">Teacher Management</h2>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          <input name="TeacherName" placeholder="Name" required class="border rounded px-4 py-2">
          <input name="TeacherEmail" type="email" placeholder="Email" required class="border rounded px-4 py-2">
          <input name="TeacherNum" placeholder="Contact" class="border rounded px-4 py-2">
          <input name="Password" type="password" placeholder="Password" required class="border rounded px-4 py-2">
          <div class="md:col-span-2">
            <button name="add_teacher" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 mt-2">Add Teacher</button>
          </div>
        </form>

        <div class="overflow-x-auto">
          <table class="min-w-full border">
            <thead>
              <tr class="bg-gray-200 text-sm uppercase tracking-wider">
                <th class="px-4 py-2 border">ID</th>
                <th class="px-4 py-2 border">Name</th>
                <th class="px-4 py-2 border">Email</th>
                <th class="px-4 py-2 border">Contact</th>
                <th class="px-4 py-2 border">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $teachers->fetch_assoc()): ?>
              <tr class="hover:bg-gray-100">
                <td class="px-4 py-2 border"><?= $row['TeacherID'] ?></td>
                <td class="px-4 py-2 border"><?= htmlspecialchars($row['TeacherName']) ?></td>
                <td class="px-4 py-2 border"><?= htmlspecialchars($row['TeacherEmail']) ?></td>
                <td class="px-4 py-2 border"><?= htmlspecialchars($row['TeacherNum']) ?></td>
                <td class="px-4 py-2 border">
                  <form method="POST" onsubmit="return confirm('Delete this teacher?');">
                    <input type="hidden" name="TeacherID" value="<?= $row['TeacherID'] ?>">
                    <button name="delete_teacher" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</button>
                  </form>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
