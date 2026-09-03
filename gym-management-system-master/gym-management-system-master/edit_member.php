<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $member_id = $_POST['member_id'];
    $name = $_POST['name'];
    $plan_id = !empty($_POST['plan_id']) ? $_POST['plan_id'] : null;
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    $status = !empty($_POST['status']) ? $_POST['status'] : null;

    // --- 1. Update Member table ---
    $stmt = $conn->prepare("UPDATE Member SET Name = ? WHERE MemberID = ?");
    $stmt->bind_param("si", $name, $member_id);
    $stmt->execute();
    $stmt->close();

    // --- 2. Check if membership exists ---
    $check = $conn->prepare("SELECT MembershipID FROM Membership WHERE MemberID = ?");
    $check->bind_param("i", $member_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // Membership exists → update
        $check->bind_result($membership_id);
        $check->fetch();
        $check->close();

        $stmt = $conn->prepare("UPDATE Membership 
                                SET PlanID = ?, StartDate = ?, EndDate = ?, Status = ?
                                WHERE MembershipID = ?");
        $stmt->bind_param("isssi", $plan_id, $start_date, $end_date, $status, $membership_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // No membership → insert new if plan is provided
        $check->close();
        if ($plan_id) {
            $stmt = $conn->prepare("INSERT INTO Membership (MemberID, PlanID, StartDate, EndDate, Status)
                                    VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisss", $member_id, $plan_id, $start_date, $end_date, $status);
            $stmt->execute();
            $stmt->close();
        }
    }

    $conn->close();

    // Redirect back with success message
    session_start();
    $_SESSION['success_message'] = "Member updated successfully!";
    header("Location: index.php?show=member_management.php");
    exit();
}
?>
