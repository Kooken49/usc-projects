<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "dashboard_db");

// Fetch pending bookings with student details
$bookings = $conn->query("
    SELECT sb.BookingID, sb.StudentID, sb.PreferredDate, sb.PreferredTimeStart, sb.PreferredTimeEnd,
           s.StudentName
    FROM StudentBooking sb
    JOIN Student s ON sb.StudentID = s.StudentID
    WHERE sb.Status = 'Pending'
");

// Fetch teacher list for dropdown
$teachers = $conn->query("SELECT TeacherID, TeacherName FROM Teacher");

// When admin submits the form
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['confirm'])) {
    foreach ($_POST['teacher'] as $bookingID => $teacherID) {
        // Get booking info again
        $stmt = $conn->prepare("SELECT StudentID, PreferredDate, PreferredTimeStart, PreferredTimeEnd FROM StudentBooking WHERE BookingID = ?");
        $stmt->bind_param("i", $bookingID);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();

        if ($booking) {
            $studentID = $booking['StudentID'];
            $date = $booking['PreferredDate'];
            $start = $booking['PreferredTimeStart'];
            $end = $booking['PreferredTimeEnd'];

            // Insert into ScheduleSlot
            $insert = $conn->prepare("INSERT INTO ScheduleSlot (TeacherID, StudentID, Date, TimeStart, TimeEnd) VALUES (?, ?, ?, ?, ?)");
            $insert->bind_param("iisss", $teacherID, $studentID, $date, $start, $end);
            $insert->execute();

            // Update StudentBooking status to Approved
            $update = $conn->prepare("UPDATE StudentBooking SET Status = 'Approved' WHERE BookingID = ?");
            $update->bind_param("i", $bookingID);
            $update->execute();

            // Prepare Notification content
            $title = "Schedule Confirmed";
            $message = "Your class is scheduled on $date from $start to $end.";

            // Insert into Notification table
            $notify = $conn->prepare("INSERT INTO Notification (SenderType, Title, Message) VALUES ('System', ?, ?)");
            $notify->bind_param("ss", $title, $message);
            $notify->execute();
            $notificationID = $conn->insert_id;

            // Add to NotificationRecipient for Student
            $rec1 = $conn->prepare("INSERT INTO NotificationRecipient (NotificationID, RecipientType, StudentID) VALUES (?, 'Student', ?)");
            $rec1->bind_param("ii", $notificationID, $studentID);
            $rec1->execute();

            // Add to NotificationRecipient for Teacher
            $rec2 = $conn->prepare("INSERT INTO NotificationRecipient (NotificationID, RecipientType, TeacherID) VALUES (?, 'Teacher', ?)");
            $rec2->bind_param("ii", $notificationID, $teacherID);
            $rec2->execute();

            // 🟨 Deduct 1 token from student's earliest expiry token
            $tokenStmt = $conn->prepare("
                SELECT TokenID, TokenCount
                FROM Token
                WHERE StudentID = ? AND TokenCount > 0
                ORDER BY ExpiryDate ASC
                LIMIT 1
            ");
            $tokenStmt->bind_param("i", $studentID);
            $tokenStmt->execute();
            $tokenResult = $tokenStmt->get_result();

            if ($token = $tokenResult->fetch_assoc()) {
                $tokenID = $token['TokenID'];
                $newCount = $token['TokenCount'] - 1;

                $updateToken = $conn->prepare("UPDATE Token SET TokenCount = ? WHERE TokenID = ?");
                $updateToken->bind_param("ii", $newCount, $tokenID);
                $updateToken->execute();
            }
        }
    }

    echo "<p>Schedules confirmed, notifications sent, and tokens deducted successfully.</p>";
}
?>

<h2>Pending Bookings – Assign Teachers</h2>
<form method="POST">
    <table border="1" cellpadding="5">
        <tr>
            <th>Student Name</th>
            <th>Date</th>
            <th>Time Start</th>
            <th>Time End</th>
            <th>Assign Teacher</th>
        </tr>

        <?php while ($row = $bookings->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['StudentName']) ?></td>
                <td><?= $row['PreferredDate'] ?></td>
                <td><?= $row['PreferredTimeStart'] ?></td>
                <td><?= $row['PreferredTimeEnd'] ?></td>
                <td>
                    <select name="teacher[<?= $row['BookingID'] ?>]" required>
                        <option value="">-- Select --</option>
                        <?php
                        mysqli_data_seek($teachers, 0);
                        while ($t = $teachers->fetch_assoc()):
                        ?>
                            <option value="<?= $t['TeacherID'] ?>"><?= htmlspecialchars($t['TeacherName']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <br>
    <button type="submit" name="confirm">Confirm Schedule</button>
</form>
