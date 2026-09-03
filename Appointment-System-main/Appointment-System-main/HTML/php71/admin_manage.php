<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['add'])) {
        $name = $_POST['AdminName'];
        $email = $_POST['AdminEmail'];
        $pass = $_POST['password'];

        if (!empty($name) && !empty($email) && !empty($pass)) {
            $stmt = $conn->prepare("INSERT INTO Admin (AdminName, AdminEmail, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $pass);
            $stmt->execute();
            $stmt->close();
        }
    } elseif (isset($_POST['delete'])) {
        $id = (int) $_POST['id'];
        $conn->query("DELETE FROM Admin WHERE id = $id");
    }
}

// Fetch admins
$result = $conn->query("SELECT * FROM Admin");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
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
      <a href="#" class="text-blue-700 font-semibold">Admins</a>
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
      <h1 class="text-3xl font-bold">Admin Management</h1>
    </header>

    <!-- PAGE-SPECIFIC CONTENT GOES HERE -->
    <main class="flex-1 p-6 overflow-auto">
      <section class="bg-gray-100 p-6 rounded-lg shadow-md max-w-xl mx-auto">
        <h2 class="text-2xl font-semibold mb-4">Add New Admin</h2>
        <form class="space-y-4" method="POST">
          <div>
            <label class="block text-sm font-medium mb-1" for="admin-name">Name</label>
            <input type="text" name="AdminName" id="admin-name" class="w-full border border-gray-300 rounded px-4 py-2" placeholder="Enter name" required>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1" for="admin-email">Email</label>
            <input type="email" name="AdminEmail" id="admin-email" class="w-full border border-gray-300 rounded px-4 py-2" placeholder="Enter email" required>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1" for="admin-password">Password</label>
            <input type="password" name="password" id="admin-password" class="w-full border border-gray-300 rounded px-4 py-2" placeholder="Enter password" required>
          </div>
          <button type="submit" name="add" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Admin</button>
        </form>
      </section>

      <section class="bg-gray-50 p-6 rounded-lg shadow-md max-w-4xl mx-auto">
        <h2 class="text-2xl font-semibold mb-4">Current Admins</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full bg-white border border-gray-300">
            <thead>
              <tr class="bg-gray-200 text-left text-sm uppercase tracking-wider">
                <th class="px-4 py-2 border-b">ID</th>
                <th class="px-4 py-2 border-b">Name</th>
                <th class="px-4 py-2 border-b">Email</th>
                <th class="px-4 py-2 border-b">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result->fetch_assoc()): ?>
              <tr class="hover:bg-gray-100">
                <td class="px-4 py-2 border-b"><?= $row['id'] ?></td>
                <td class="px-4 py-2 border-b"><?= htmlspecialchars($row['AdminName']) ?></td>
                <td class="px-4 py-2 border-b"><?= htmlspecialchars($row['AdminEmail']) ?></td>
                <td class="px-4 py-2 border-b">
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <button type="submit" name="delete" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</button>
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
