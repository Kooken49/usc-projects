<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['member_id'])) {
    $member_id = intval($_POST['member_id']);

    // First delete related membership records (to maintain foreign key constraints)
    $conn->query("DELETE FROM Membership WHERE MemberID = $member_id");

    // Then delete the member
    $sql = "DELETE FROM Member WHERE MemberID = $member_id";
    if ($conn->query($sql) === TRUE) {
        header('Content-Type: application/json');
        echo json_encode(["success" => true, "message" => "Member deleted successfully!"]);
    } else {
        echo "<script>alert('Error deleting member: " . addslashes($conn->error) . "'); window.location.href='member_management.php';</script>";
    }
} else {
    header("Location: index.php");
    exit();
}

$conn->close();
?>
