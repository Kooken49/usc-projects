<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dashboard_db");
if ($conn->connect_errno) die("Connection failed: " . $conn->connect_error);

$type = $_GET['type'] ?? 'admin';
$errorMessage = "";

// Password validation function
function is_valid_password($password) {
    if (strlen($password) < 8) return false;
    if (!preg_match('/[A-Z]/', $password)) return false;
    if (preg_match_all('/[a-zA-Z]/', $password) < 4) return false;
    if (preg_match_all('/[0-9]/', $password) < 4) return false;
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) return false;
    return true;
}

// POST handling
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($type === 'admin') {
        if (isset($_POST['add'])) {
            $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO Admin (AdminName, AdminEmail, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $_POST['name'], $_POST['email'], $hashed);
            $stmt->execute(); 
            $stmt->close();
        } elseif (isset($_POST['edit'])) {
            if (!empty($_POST['password'])) {
                $password = $_POST['password'];
                $confirm = $_POST['confirm_password'];

                if ($password !== $confirm) {
                    $errorMessage = "Passwords do not match.";
                } elseif (!is_valid_password($password)) {
                    $errorMessage = "Password must be at least 8 characters, with 4 letters, 4 numbers, 1 uppercase letter, and 1 special character.";
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE Admin SET AdminName = ?, AdminEmail = ?, password = ? WHERE id = ?");
                    $stmt->bind_param("sssi", $_POST['name'], $_POST['email'], $hashed, $_POST['id']);
                    $stmt->execute(); 
                    $stmt->close();
                }
            } else {
                $stmt = $conn->prepare("UPDATE Admin SET AdminName = ?, AdminEmail = ? WHERE id = ?");
                $stmt->bind_param("ssi", $_POST['name'], $_POST['email'], $_POST['id']);
                $stmt->execute(); 
                $stmt->close();
            }
        } elseif (isset($_POST['delete'])) {
            $stmt = $conn->prepare("UPDATE Admin SET IsActive = FALSE WHERE id = ?");
            $stmt->bind_param("i", $_POST['id']);
            $stmt->execute(); 
            $stmt->close();
        }
    } elseif ($type === 'student') {
        if (isset($_POST['add'])) {
            $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO Student (StudentName, StudentEmail, StudentNum, Password, Level) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $_POST['name'], $_POST['email'], $_POST['num'], $hashed, $_POST['level']);
            $stmt->execute(); 
            $stmt->close();
        } elseif (isset($_POST['edit'])) {
            if (!empty($_POST['password'])) {
                $password = $_POST['password'];
                $confirm = $_POST['confirm_password'];

                if ($password !== $confirm) {
                    $errorMessage = "Passwords do not match.";
                } elseif (!is_valid_password($password)) {
                    $errorMessage = "Password must be at least 8 characters, with 4 letters, 4 numbers, 1 uppercase letter, and 1 special character.";
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE Student SET StudentName = ?, StudentEmail = ?, StudentNum = ?, Level = ?, Password = ? WHERE StudentID = ?");
                    $stmt->bind_param("sssssi", $_POST['name'], $_POST['email'], $_POST['num'], $_POST['level'], $hashed, $_POST['id']);
                    $stmt->execute(); 
                    $stmt->close();
                }
            } else {
                $stmt = $conn->prepare("UPDATE Student SET StudentName = ?, StudentEmail = ?, StudentNum = ?, Level = ? WHERE StudentID = ?");
                $stmt->bind_param("ssssi", $_POST['name'], $_POST['email'], $_POST['num'], $_POST['level'], $_POST['id']);
                $stmt->execute(); 
                $stmt->close();
            }
        } elseif (isset($_POST['delete'])) {
            $stmt = $conn->prepare("UPDATE Student SET IsActive = FALSE WHERE StudentID = ?");
            $stmt->bind_param("i", $_POST['id']);
            $stmt->execute(); 
            $stmt->close();
        }
    } elseif ($type === 'teacher') {
        if (isset($_POST['add'])) {
            $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO Teacher (TeacherName, TeacherEmail, TeacherNum, Password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $_POST['name'], $_POST['email'], $_POST['num'], $hashed);
            $stmt->execute(); 
            $stmt->close();
        } elseif (isset($_POST['edit'])) {
            if (!empty($_POST['password'])) {
                $password = $_POST['password'];
                $confirm = $_POST['confirm_password'];

                if ($password !== $confirm) {
                    $errorMessage = "Passwords do not match.";
                } elseif (!is_valid_password($password)) {
                    $errorMessage = "Password must be at least 8 characters, with 4 letters, 4 numbers, 1 uppercase letter, and 1 special character.";
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE Teacher SET TeacherName = ?, TeacherEmail = ?, TeacherNum = ?, Password = ? WHERE TeacherID = ?");
                    $stmt->bind_param("ssssi", $_POST['name'], $_POST['email'], $_POST['num'], $hashed, $_POST['id']);
                    $stmt->execute(); 
                    $stmt->close();
                }
            } else {
                $stmt = $conn->prepare("UPDATE Teacher SET TeacherName = ?, TeacherEmail = ?, TeacherNum = ? WHERE TeacherID = ?");
                $stmt->bind_param("sssi", $_POST['name'], $_POST['email'], $_POST['num'], $_POST['id']);
                $stmt->execute(); 
                $stmt->close();
            }
        } elseif (isset($_POST['delete'])) {
            $stmt = $conn->prepare("UPDATE Teacher SET IsActive = FALSE WHERE TeacherID = ?");
            $stmt->bind_param("i", $_POST['id']);
            $stmt->execute(); 
            $stmt->close();
        }
    }

    if (isset($_POST['reactivate'])) {
        $table = ucfirst($type);
        $idField = $type === 'admin' ? 'id' : ($type === 'student' ? 'StudentID' : 'TeacherID');
        $stmt = $conn->prepare("UPDATE $table SET IsActive = TRUE WHERE $idField = ?");
        $stmt->bind_param("i", $_POST['id']);
        $stmt->execute(); 
        $stmt->close();
    } elseif (isset($_POST['purge'])) {
        $table = ucfirst($type);
        $idField = $type === 'admin' ? 'id' : ($type === 'student' ? 'StudentID' : 'TeacherID');
        $stmt = $conn->prepare("DELETE FROM $table WHERE $idField = ?");
        $stmt->bind_param("i", $_POST['id']);
        $stmt->execute(); 
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>User Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .font-georgia { font-family: Georgia, serif; }
  </style>
</head>
<body class="h-screen font-georgia text-gray-800">

  <!-- Sidebar on top -->
  <aside class="fixed top-0 left-0 w-64 h-full bg-[#f1f1f1] shadow-lg p-6 z-50 flex flex-col justify-between">
    <div>
      <a href="index.html" class="flex items-center space-x-2 mb-8">
        <img src="img/logo.png" alt="Logo" class="h-12">
        <span class="text-xl font-bold text-blue-700">World AI</span>
      </a>
      <nav class="flex flex-col space-y-4 text-lg">
        <a href="admin_dashboard.php" class="hover:text-blue-600">Dashboard</a>
        <a href="#" class="text-blue-700 font-semibold">User  Management</a>
        <a href="credit_manage.php" class="hover:text-blue-600">Credit Management</a>
        <a href="schedule_manage.php" class="hover:text-blue-600">Schedules</a>
        <a href="admin_notifications.php" class="hover:text-blue-600">Notifications</a>
        <a href="logout.php" class="text-red-600 hover:underline">Logout</a>
      </nav>
    </div>
  </aside>

  <!-- Main Content shifted to the right of the fixed sidebar -->
  <div class="ml-64 min-h-screen flex flex-col">
    <header class="bg-[#f1f1f1] flex justify-center items-center px-6 py-6 shadow h-24">
      <h1 class="text-3xl font-bold">User  Management</h1>
    </header>
    <main class="p-6 flex-1 overflow-auto">
      <!-- your PHP logic and forms here (unchanged) -->
      <!-- place the rest of your code from <form method="GET"... onwards here -->

      <!-- Combined Type Switcher + Search Form -->
      <form method="GET" class="mb-6 flex flex-wrap items-center gap-4">
        <label class="font-semibold">Manage:</label>
        <select name="type" class="border p-2 rounded" onchange="this.form.submit()">
          <option value="admin" <?= $type === 'admin' ? 'selected' : '' ?>>Admin</option>
          <option value="student" <?= $type === 'student' ? 'selected' : '' ?>>Student</option>
          <option value="teacher" <?= $type === 'teacher' ? 'selected' : '' ?>>Teacher</option>
        </select>

        <input
          type="text"
          name="search"
          value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
          placeholder="Search by ID, name, email<?= $type === 'student' ? ', or number' : '' ?>"
          class="border border-gray-300 rounded px-3 py-1 text-sm"
        >

        <?php if ($type === 'student'): ?>
          <select name="level" class="border p-2 rounded text-sm">
            <option value="">All Levels</option>
            <option value="Basic" <?= ($_GET['level'] ?? '') === 'Basic' ? 'selected' : '' ?>>Basic</option>
            <option value="Advanced" <?= ($_GET['level'] ?? '') === 'Advanced' ? 'selected' : '' ?>>Advanced</option>
          </select>
        <?php endif; ?>

        <label class="flex items-center space-x-1 text-sm">
          <input type="checkbox" name="show_inactive" value="1" <?= isset($_GET['show_inactive']) ? 'checked' : '' ?>>
          <span>Include inactive</span>
        </label>

        <button
          type="submit"
          class="bg-blue-600 text-white text-sm font-semibold px-4 py-1 rounded hover:bg-blue-700"
        >
          Search
        </button>
      </form>

      <!-- Add User -->
      <form method="POST" class="mb-10 space-y-2">
        <!-- unchanged PHP logic above here... -->
        <?php if (!empty($errorMessage)): ?>
          <div class="mb-4 p-4 border border-red-400 bg-red-100 text-red-700 rounded">
            <?= htmlspecialchars($errorMessage) ?>
          </div>
        <?php endif; ?>

        <h2 class="text-xl font-semibold mb-4"><?= ucfirst($type) ?> List</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full border border-gray-400 rounded text-base">
            <thead class="bg-blue-100 text-blue-900">
              <tr>
                <th class="px-4 py-2 text-left border border-gray-300">ID</th>
                <th class="px-4 py-2 text-left border border-gray-300">Name</th>
                <th class="px-4 py-2 text-left border border-gray-300">Email</th>
                <?php if ($type !== 'admin'): ?>
                  <th class="px-4 py-2 text-left border border-gray-300">Number</th>
                <?php endif; ?>
                <?php if ($type === 'student'): ?>
                  <th class="px-4 py-2 text-left border border-gray-300">Level</th>
                <?php endif; ?>
                <th class="px-4 py-2 text-left border border-gray-300">Status</th>
                <th class="px-4 py-2 text-left border border-gray-300 w-[25%]">Edits</th>
                <th class="px-4 py-2 text-left border border-gray-300 w-[10%]">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            <?php
              $search = $_GET['search'] ?? '';
              $levelFilter = $_GET['level'] ?? '';
              $showInactive = isset($_GET['show_inactive']);
              $like = "%$search%";

              if ($type === 'admin') {
                  $query = "SELECT * FROM Admin WHERE (AdminName LIKE ? OR AdminEmail LIKE ? OR id LIKE ?)";
                  if (!$showInactive) $query .= " AND IsActive = TRUE";
                  $stmt = $conn->prepare($query);
                  $stmt->bind_param("sss", $like, $like, $like);
              } elseif ($type === 'student') {
                  $query = "SELECT * FROM Student WHERE (StudentName LIKE ? OR StudentEmail LIKE ? OR StudentNum LIKE ? OR StudentID LIKE ?)";
                  if ($levelFilter !== '') $query .= " AND Level = ?";
                  if (!$showInactive) $query .= " AND IsActive = TRUE";
                  if ($levelFilter !== '') {
                      $stmt = $conn->prepare($query);
                      $stmt->bind_param("sssss", $like, $like, $like, $like, $levelFilter);
                  } else {
                      $stmt = $conn->prepare($query);
                      $stmt->bind_param("ssss", $like, $like, $like, $like);
                  }
              } elseif ($type === 'teacher') {
                  $query = "SELECT * FROM Teacher WHERE (TeacherName LIKE ? OR TeacherEmail LIKE ? OR TeacherNum LIKE ? OR TeacherID LIKE ?)";
                  if (!$showInactive) $query .= " AND IsActive = TRUE";
                  $stmt = $conn->prepare($query);
                  $stmt->bind_param("ssss", $like, $like, $like, $like);
              }

              $stmt->execute();
              $data = $stmt->get_result();
              ?>
              <?php while ($row = $data->fetch_assoc()): ?>
                <?php
                  $isActive = $row['IsActive'];
                  $rowID = $type === 'admin' ? $row['id'] : ($type === 'student' ? $row['StudentID'] : $row['TeacherID']);
                ?>
                <form method="POST">
                  <tr class="<?= $isActive ? '' : 'bg-gray-100' ?>">
                    <td class="px-4 py-2 border border-gray-300"><?= $rowID ?></td>
                    <td class="px-4 py-2 border border-gray-300"><?= htmlspecialchars($row[$type === 'admin' ? 'AdminName' : ($type === 'student' ? 'StudentName' : 'TeacherName')]) ?></td>
                    <td class="px-4 py-2 border border-gray-300"><?= htmlspecialchars($row[$type === 'admin' ? 'AdminEmail' : ($type === 'student' ? 'StudentEmail' : 'TeacherEmail')]) ?></td>
                    <?php if ($type !== 'admin'): ?>
                      <td class="px-4 py-2 border border-gray-300"><?= htmlspecialchars($row[$type === 'student' ? 'StudentNum' : 'TeacherNum']) ?></td>
                    <?php endif; ?>
                    <?php if ($type === 'student'): ?>
                      <td class="px-4 py-2 border border-gray-300"><?= $row['Level'] ?></td>
                    <?php endif; ?>
                    <td class="px-4 py-2 border border-gray-300"><?= $isActive ? 'Active' : 'Inactive' ?></td>

                    <!-- Edits Column -->
                    <td class="px-4 py-2 border border-gray-300 w-[25%]">
                      <?php if ($isActive): ?>
                        <input type="hidden" name="id" value="<?= $rowID ?>">
                        <div class="flex flex-col space-y-1">
                          <input name="name" value="<?= htmlspecialchars($row[$type === 'admin' ? 'AdminName' : ($type === 'student' ? 'StudentName' : 'TeacherName')]) ?>" placeholder="Name" class="border p-1 rounded text-sm w-full">
                          <input name="email" value="<?= htmlspecialchars($row[$type === 'admin' ? 'AdminEmail' : ($type === 'student' ? 'StudentEmail' : 'TeacherEmail')]) ?>" placeholder="Email" class="border p-1 rounded text-sm w-full">
                          <?php if ($type !== 'admin'): ?>
                            <input name="num" value="<?= htmlspecialchars($row[$type === 'student' ? 'StudentNum' : 'TeacherNum']) ?>" placeholder="Contact" class="border p-1 rounded text-sm w-full">
                          <?php endif; ?>
                          <?php if ($type === 'student'): ?>
                            <select name="level" class="border p-1 rounded text-sm w-full">
                              <option value="Basic" <?= $row['Level'] == 'Basic' ? 'selected' : '' ?>>Basic</option>
                              <option value="Advanced" <?= $row['Level'] == 'Advanced' ? 'selected' : '' ?>>Advanced</option>
                            </select>
                          <?php endif; ?>
                          <input name="password" placeholder="New Password" class="border p-1 rounded text-sm w-full" type="password">
                          <input name="confirm_password" placeholder="Confirm Password" class="border p-1 rounded text-sm w-full" type="password">
                        </div>
                      <?php else: ?>
                        <span class="italic text-gray-400">N/A</span>
                      <?php endif; ?>
                    </td>

                    <!-- Actions Column -->
                    <td class="px-2 py-2 border border-gray-300 w-[10%]">
                      <?php if ($isActive): ?>
                        <button name="edit" class="bg-yellow-500 px-2 py-1 text-sm font-bold rounded hover:bg-yellow-600 block mb-1 w-full">Save</button>
                        <button name="delete" onclick="return confirm('Are you sure to deactivate?');" class="bg-red-500 px-2 py-1 text-sm font-bold text-white rounded hover:bg-red-600 block w-full">Deactivate</button>
                      <?php else: ?>
                        <input type="hidden" name="id" value="<?= $rowID ?>">
                        <button name="reactivate" class="<button name="reactivate" class="bg-green-500 px-2 py-1 text-sm font-bold text-white rounded hover:bg-green-600 w-full">Reactivate</button>
                      <?php endif; ?>
                    </td>
                  </tr>
                </form>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </form>
    </main>
  </div>
</body>
</html>
