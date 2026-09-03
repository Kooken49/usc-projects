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

// Cleanup expired or zero-credit tokens
$conn->query("
    DELETE FROM BookingCredits
    WHERE CreditAmount <= 0
       OR ExpiryDate < CURDATE()
");

// Handle credit addition
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_credit'])) {
    $studentId = $_POST['StudentID'];
    $plan = $_POST['Plan'] ?? null;

    $creditMap = [
        'OneTimeAWeek' => 4,
        'TwoTimesAWeek' => 8,
        'ThreeTimesAWeek' => 12,
        'Everyday' => 25
    ];

    if ($plan && isset($creditMap[$plan])) {
        $creditAmount = $creditMap[$plan];
        $expiryDate = date('Y-m-d', strtotime('+1 month'));

        $stmt = $conn->prepare("INSERT INTO BookingCredits (StudentID, CreditAmount, Plan, ExpiryDate) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $studentId, $creditAmount, $plan, $expiryDate);
        $stmt->execute();
        $stmt->close();
    }
}

$students = $conn->query("SELECT StudentID, StudentName FROM Student");
$credits = $conn->query("
    SELECT bc.*, s.StudentName
    FROM BookingCredits bc
    JOIN Student s ON bc.StudentID = s.StudentID
    ORDER BY bc.IssuedAt DESC
");

$plans = [
    'OneTimeAWeek' => ['label' => 'One Time a Week', 'credits' => 4, 'price' => 10],
    'TwoTimesAWeek' => ['label' => 'Two Times a Week', 'credits' => 8, 'price' => 20],
    'ThreeTimesAWeek' => ['label' => 'Three Times a Week', 'credits' => 12, 'price' => 30],
    'Everyday' => ['label' => 'Everyday', 'credits' => 25, 'price' => 100]
];
$expiryDateFormatted = date('M/d', strtotime('+1 month'));
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Admin Dashboard - Token Management</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
      <a href="admin_manage.php" class="hover:text-blue-600">Admins</a>
      <a href="user_manage.php" class="hover:text-blue-600">User Management</a>
      <a href="#" class="text-blue-700 font-semibold">Credit Management</a>
      <a href="schedule_manage.php" class="hover:text-blue-600">Schedules</a>
      <a href="admin_notifications.php" class="hover:text-blue-600">Notifications</a>
      <a href="logout.php" class="text-red-600 hover:underline">Logout</a>
    </nav>
  </aside>

  <!-- MAIN CONTENT WRAPPER (with left margin so it doesn't sit under the sidebar) -->
  <div class="ml-64 flex flex-col w-full min-h-screen bg-white">

    <!-- CONSISTENT HEADER -->
    <header class="bg-[#f1f1f1] shadow h-24 flex items-center justify-center px-6">
      <h1 class="text-3xl font-bold">Credit Management</h1>
    </header>

    <!-- PAGE-SPECIFIC CONTENT GOES HERE -->
    <main class="flex-1 p-6 overflow-auto">

      <!-- Add Token Section -->
      <section class="bg-[#f9f9f9] p-6 rounded-xl shadow-md">
        <h2 class="text-2xl font-semibold mb-4">Assign Booking Credits</h2>
        <form method="POST" class="space-y-4">
          <!-- Student Dropdown -->
          <div>
            <label for="StudentID" class="block text-sm font-medium mb-1">Select Student</label>
            <select name="StudentID" id="StudentID" class="w-full border border-gray-300 rounded-lg p-2" required>
              <option value="">-- Select Student --</option>
              <?php while ($row = $students->fetch_assoc()): ?>
                <option value="<?= $row['StudentID'] ?>"><?= htmlspecialchars($row['StudentName']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <!-- Plan Selection -->
          <div>
            <label class="block text-sm font-medium mb-1">Select Plan (Only One Allowed)</label>
            <div class="space-y-2">
              <?php foreach ($plans as $key => $plan): ?>
                <label class="block">
                  <input type="radio" name="Plan" value="<?= $key ?>" onclick="updatePrice(<?= $plan['price'] ?>)" required>
                  <?= $plan['label'] ?> (<?= $plan['credits'] ?> Credits) — Expires: <?= $expiryDateFormatted ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Price Display -->
          <div id="priceDisplay" class="text-lg font-medium"><strong>Price:</strong> $0</div>

          <!-- Submit -->
          <button name="add_credit" class="w-full bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700">
            Add Credit
          </button>
        </form>
      </section>

      <!-- Credit History -->
      <section class="bg-[#f9f9f9] p-6 rounded-xl shadow-md">
        <h2 class="text-2xl font-semibold mb-4">Booking Credit History</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full table-auto border border-gray-300">
            <thead>
              <tr class="bg-gray-200">
                <th class="px-4 py-2 border">Student</th>
                <th class="px-4 py-2 border">Plan</th>
                <th class="px-4 py-2 border">Credits</th>
                <th class="px-4 py-2 border">Issued At</th>
                <th class="px-4 py-2 border">Expires</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $credits->fetch_assoc()): ?>
              <tr class="text-center">
                <td class="border px-4 py-2"><?= htmlspecialchars($row['StudentName']) ?></td>
                <td class="border px-4 py-2"><?= $row['Plan'] ?></td>
                <td class="border px-4 py-2"><?= $row['CreditAmount'] ?></td>
                <td class="border px-4 py-2"><?= $row['IssuedAt'] ?></td>
                <td class="border px-4 py-2"><?= $row['ExpiryDate'] ?></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </section>

    </main>
  </div>

  <script>
    function updatePrice(price) {
      document.getElementById('priceDisplay').innerHTML = "<strong>Price:</strong> $" + price;
    }
  </script>
</body>
</html>
