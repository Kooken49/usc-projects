<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");
if ($conn->connect_errno) die("Connection failed: " . $conn->connect_error);

$type = $_GET['type'] ?? 'admin';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($type === 'admin') {
        if (isset($_POST['add'])) {
            $stmt = $conn->prepare("INSERT INTO Admin (AdminName, AdminEmail, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $_POST['name'], $_POST['email'], $_POST['password']);
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['edit'])) {
            if (!empty($_POST['password'])) {
                $stmt = $conn->prepare("UPDATE Admin SET AdminName = ?, AdminEmail = ?, password = ? WHERE id = ?");
                $stmt->bind_param("sssi", $_POST['name'], $_POST['email'], $_POST['password'], $_POST['id']);
            } else {
                $stmt = $conn->prepare("UPDATE Admin SET AdminName = ?, AdminEmail = ? WHERE id = ?");
                $stmt->bind_param("ssi", $_POST['name'], $_POST['email'], $_POST['id']);
            }
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['delete'])) {
            $stmt = $conn->prepare("UPDATE Admin SET IsActive = FALSE WHERE id = ?");
            $stmt->bind_param("i", $_POST['id']);
            $stmt->execute();
            $stmt->close();
        }
    } elseif ($type === 'student') {
        if (isset($_POST['add'])) {
            $stmt = $conn->prepare("INSERT INTO Student (StudentName, StudentEmail, StudentNum, Password, Level) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $_POST['name'], $_POST['email'], $_POST['num'], $_POST['password'], $_POST['level']);
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['edit'])) {
            if (!empty($_POST['password'])) {
                $stmt = $conn->prepare("UPDATE Student SET StudentName = ?, StudentEmail = ?, StudentNum = ?, Level = ?, Password = ? WHERE StudentID = ?");
                $stmt->bind_param("sssssi", $_POST['name'], $_POST['email'], $_POST['num'], $_POST['level'], $_POST['password'], $_POST['id']);
            } else {
                $stmt = $conn->prepare("UPDATE Student SET StudentName = ?, StudentEmail = ?, StudentNum = ?, Level = ? WHERE StudentID = ?");
                $stmt->bind_param("ssssi", $_POST['name'], $_POST['email'], $_POST['num'], $_POST['level'], $_POST['id']);
            }
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['delete'])) {
            $stmt = $conn->prepare("UPDATE Student SET IsActive = FALSE WHERE StudentID = ?");
            $stmt->bind_param("i", $_POST['id']);
            $stmt->execute();
            $stmt->close();
        }
    } elseif ($type === 'teacher') {
        if (isset($_POST['add'])) {
            $stmt = $conn->prepare("INSERT INTO Teacher (TeacherName, TeacherEmail, TeacherNum, Password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $_POST['name'], $_POST['email'], $_POST['num'], $_POST['password']);
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['edit'])) {
            if (!empty($_POST['password'])) {
                $stmt = $conn->prepare("UPDATE Teacher SET TeacherName = ?, TeacherEmail = ?, TeacherNum = ?, Password = ? WHERE TeacherID = ?");
                $stmt->bind_param("ssssi", $_POST['name'], $_POST['email'], $_POST['num'], $_POST['password'], $_POST['id']);
            } else {
                $stmt = $conn->prepare("UPDATE Teacher SET TeacherName = ?, TeacherEmail = ?, TeacherNum = ? WHERE TeacherID = ?");
                $stmt->bind_param("sssi", $_POST['name'], $_POST['email'], $_POST['num'], $_POST['id']);
            }
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['delete'])) {
            $stmt = $conn->prepare("UPDATE Teacher SET IsActive = FALSE WHERE TeacherID = ?");
            $stmt->bind_param("i", $_POST['id']);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Reactivate or permanently delete
if (isset($_POST['reactivate'])) {
    if ($type === 'admin') {
        $stmt = $conn->prepare("UPDATE Admin SET IsActive = TRUE WHERE id = ?");
    } elseif ($type === 'student') {
        $stmt = $conn->prepare("UPDATE Student SET IsActive = TRUE WHERE StudentID = ?");
    } elseif ($type === 'teacher') {
        $stmt = $conn->prepare("UPDATE Teacher SET IsActive = TRUE WHERE TeacherID = ?");
    }
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
    $stmt->close();
} elseif (isset($_POST['purge'])) {
    if ($type === 'admin') {
        $stmt = $conn->prepare("DELETE FROM Admin WHERE id = ?");
    } elseif ($type === 'student') {
        $stmt = $conn->prepare("DELETE FROM Student WHERE StudentID = ?");
    } elseif ($type === 'teacher') {
        $stmt = $conn->prepare("DELETE FROM Teacher WHERE TeacherID = ?");
    }
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
    $stmt->close();
}

// Fetch data
if ($type === 'admin') {
    $data = $conn->query("SELECT * FROM Admin WHERE IsActive = TRUE");
    $inactive = $conn->query("SELECT * FROM Admin WHERE IsActive = FALSE");
} elseif ($type === 'student') {
    $data = $conn->query("SELECT * FROM Student WHERE IsActive = TRUE");
    $inactive = $conn->query("SELECT * FROM Student WHERE IsActive = FALSE");
} elseif ($type === 'teacher') {
    $data = $conn->query("SELECT * FROM Teacher WHERE IsActive = TRUE");
    $inactive = $conn->query("SELECT * FROM Teacher WHERE IsActive = FALSE");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>User Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>.font-georgia { font-family: Georgia, serif; }</style>
</head>
<body class="h-screen flex text-gray-800 font-georgia">
  <!-- Sidebar -->
  <aside class="w-64 bg-[#f1f1f1] shadow-lg p-6 flex flex-col justify-between">
    <div>
      <a href="index.html" class="flex items-center space-x-2 mb-8">
        <img src="img/logo.png" alt="Logo" class="h-12">
        <span class="text-xl font-bold text-blue-700">World AI</span>
      </a>
      <nav class="flex flex-col space-y-4 text-lg">
    <a href="admin_dashboard.php" class="hover:text-blue-600">Dashboard</a>
      <a href="#" class="text-blue-700 font-semibold">User Management</a>
      <a href="credit_manage.php" class="hover:text-blue-600">Credit Management</a>
      <a href="schedule_manage.php" class="hover:text-blue-600">Schedules</a>
      <a href="admin_notifications.php" class="hover:text-blue-600">Notifications</a>
      <a href="logout.php" class="text-red-600 hover:underline">Logout</a>
      </nav>
    </div>
  </aside>

  <!-- Main Content -->
  <div class="flex-1 bg-white flex flex-col min-h-screen">
    <header class="bg-[#f1f1f1] flex justify-center items-center px-6 py-6 shadow h-24">
      <h1 class="text-3xl font-bold">User Management</h1>
    </header>
    <main class="p-6 flex-1 overflow-auto">

      <!-- Type Switcher -->
      <form method="GET" class="mb-6">
        <label class="font-semibold mr-2">Manage:</label>
        <select name="type" onchange="this.form.submit()" class="border p-2 rounded">
          <option value="admin" <?= $type === 'admin' ? 'selected' : '' ?>>Admin</option>
          <option value="student" <?= $type === 'student' ? 'selected' : '' ?>>Student</option>
          <option value="teacher" <?= $type === 'teacher' ? 'selected' : '' ?>>Teacher</option>
        </select>
      </form>

      <!-- Add User -->
      <form method="POST" class="mb-10 space-y-2">
        <h2 class="text-xl font-semibold mb-2">Add <?= ucfirst($type) ?></h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <input name="name" placeholder="Name" required class="border p-2 rounded">
          <input name="email" type="email" placeholder="Email" required class="border p-2 rounded">
          <?php if ($type !== 'admin'): ?>
            <input name="num" placeholder="Contact" class="border p-2 rounded">
          <?php endif; ?>
          <input name="password" type="password" placeholder="Password" required class="border p-2 rounded">
          <?php if ($type === 'student'): ?>
            <select name="level" class="border p-2 rounded">
              <option value="Basic">Basic</option>
              <option value="Advanced">Advanced</option>
            </select>
          <?php endif; ?>
        </div>
        <button name="add" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add</button>
      </form>

      <!-- Active Users -->
      <h2 class="text-xl font-semibold mb-4">Active <?= ucfirst($type) ?>s</h2>
      <div class="overflow-x-auto mb-10">
        <table class="min-w-full divide-y divide-gray-200 border rounded">
          <thead class="bg-blue-100 text-blue-900">
            <tr>
              <th class="px-4 py-2">ID</th>
              <th class="px-4 py-2">Name</th>
              <th class="px-4 py-2">Email</th>
              <?php if ($type !== 'admin'): ?><th class="px-4 py-2">Number</th><?php endif; ?>
              <?php if ($type === 'student'): ?><th class="px-4 py-2">Level</th><?php endif; ?>
              <th class="px-4 py-2">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
          <?php while ($row = $data->fetch_assoc()): ?>
            <tr>
              <form method="POST">
                <td class="px-4 py-2"><?= $type === 'admin' ? $row['id'] : ($type === 'student' ? $row['StudentID'] : $row['TeacherID']) ?></td>
                <td class="px-4 py-2"><input name="name" value="<?= htmlspecialchars($row[$type === 'admin' ? 'AdminName' : ($type === 'student' ? 'StudentName' : 'TeacherName')]) ?>" class="border p-1 rounded" required></td>
                <td class="px-4 py-2"><input name="email" value="<?= htmlspecialchars($row[$type === 'admin' ? 'AdminEmail' : ($type === 'student' ? 'StudentEmail' : 'TeacherEmail')]) ?>" class="border p-1 rounded" required></td>
                <?php if ($type !== 'admin'): ?>
                  <td class="px-4 py-2"><input name="num" value="<?= htmlspecialchars($row[$type === 'student' ? 'StudentNum' : 'TeacherNum']) ?>" class="border p-1 rounded"></td>
                <?php endif; ?>
                <?php if ($type === 'student'): ?>
                  <td class="px-4 py-2">
                    <select name="level" class="border p-1 rounded">
                      <option value="Basic" <?= $row['Level'] == 'Basic' ? 'selected' : '' ?>>Basic</option>
                      <option value="Advanced" <?= $row['Level'] == 'Advanced' ? 'selected' : '' ?>>Advanced</option>
                    </select>
                  </td>
                <?php endif; ?>
                <td class="px-4 py-2 space-x-1">
                  <input type="hidden" name="id" value="<?= $row[$type === 'admin' ? 'id' : ($type === 'student' ? 'StudentID' : 'TeacherID')] ?>">
                  <input name="password" placeholder="New Password" class="border p-1 rounded" type="password">
                  <button name="edit" class="bg-yellow-400 px-2 py-1 rounded hover:bg-yellow-500">Save</button>
                  <button name="delete" onclick="return confirm('Are you sure?');" class="bg-red-500 px-2 py-1 text-white rounded hover:bg-red-600">Deactivate</button>
                </td>
              </form>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>

      <!-- Inactive Users -->
      <h2 class="text-xl font-semibold mb-4">Deactivated <?= ucfirst($type) ?>s</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 border rounded">
          <thead class="bg-gray-100 text-gray-800">
            <tr>
              <th class="px-4 py-2">ID</th>
              <th class="px-4 py-2">Name</th>
              <th class="px-4 py-2">Email</th>
              <?php if ($type !== 'admin'): ?><th class="px-4 py-2">Number</th><?php endif; ?>
              <?php if ($type === 'student'): ?><th class="px-4 py-2">Level</th><?php endif; ?>
              <th class="px-4 py-2">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
          <?php while ($row = $inactive->fetch_assoc()): ?>
            <tr>
              <form method="POST">
                <td class="px-4 py-2"><?= $type === 'admin' ? $row['id'] : ($type === 'student' ? $row['StudentID'] : $row['TeacherID']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($row[$type === 'admin' ? 'AdminName' : ($type === 'student' ? 'StudentName' : 'TeacherName')]) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($row[$type === 'admin' ? 'AdminEmail' : ($type === 'student' ? 'StudentEmail' : 'TeacherEmail')]) ?></td>
                <?php if ($type !== 'admin'): ?>
                  <td class="px-4 py-2"><?= htmlspecialchars($row[$type === 'student' ? 'StudentNum' : 'TeacherNum']) ?></td>
                <?php endif; ?>
                <?php if ($type === 'student'): ?>
                  <td class="px-4 py-2"><?= $row['Level'] ?></td>
                <?php endif; ?>
                <td class="px-4 py-2 space-x-2">
                  <input type="hidden" name="id" value="<?= $row[$type === 'admin' ? 'id' : ($type === 'student' ? 'StudentID' : 'TeacherID')] ?>">
                  <button name="reactivate" class="bg-green-500 px-2 py-1 text-white rounded hover:bg-green-600">Reactivate</button>
                  <button name="purge" onclick="return confirm('Delete permanently?');" class="bg-red-600 px-2 py-1 text-white rounded hover:bg-red-700">Delete</button>
                </td>
              </form>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>

    </main>
  </div>
</body>
</html>
