<?php
include 'db.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id = $_POST['member_id'];
    $plan_id = $_POST['plan_id'];

    $stmt = $conn->prepare("UPDATE Membership SET PlanID = ? WHERE MemberID = ?");
    $stmt->bind_param("ii", $plan_id, $member_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
}
$conn->close();
?>
