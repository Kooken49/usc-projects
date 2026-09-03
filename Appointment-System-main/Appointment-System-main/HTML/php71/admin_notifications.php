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
  <div class="flex"></div>
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
      <a href="schedule_manage.php" class="hover:text-blue-600">Schedules</a>
      <a href="#" class="text-blue-700 font-semibold">Notifications</a>
      <a href="logout.php" class="text-red-600 hover:underline">Logout</a>
    </nav>
  </aside>

  <!-- MAIN CONTENT WRAPPER (with left margin so it doesn't sit under the sidebar) -->
  <div class="ml-64 flex flex-col w-full min-h-screen bg-white">

    <!-- CONSISTENT HEADER -->
    <header class="bg-[#f1f1f1] shadow h-24 flex items-center justify-center px-6">
      <h1 class="text-3xl font-bold">Admin</h1>
    </header>

    <!-- PAGE-SPECIFIC CONTENT GOES HERE -->
    <main class="flex-1 p-6 overflow-auto">
      <!-- Notification Sender Form -->
      <div class="max-w-2xl mx-auto bg-[#f9f9f9] p-6 rounded-2xl shadow-md mb-6">
        <h2 class="text-2xl font-bold mb-4 text-center text-blue-800">Send Notification</h2>
        <form method="post" class="space-y-4">
          <div>
            <label for="title" class="block font-semibold mb-1">Title</label>
            <input type="text" id="title" name="title" placeholder="Enter notification title" required
              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>

          <div>
            <label for="message" class="block font-semibold mb-1">Message</label>
            <textarea id="message" name="message" rows="4" placeholder="Enter your message" required
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

          <div id="student_select" style="display:none;">
            <label>Select Student:</label>
            <select name="recipient_id" class="w-full px-4 py-2 border rounded-lg">
              <!-- PHP loop for students -->
            </select>
          </div>

          <div id="teacher_select" style="display:none;">
            <label>Select Teacher:</label>
            <select name="recipient_id" class="w-full px-4 py-2 border rounded-lg">
              <!-- PHP loop for teachers -->
            </select>
          </div>

          <div id="group_select" style="display:none;">
            <label>Group:</label>
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

      <!-- Notification History Table -->
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
              <!-- PHP Loop for Notification Rows -->
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