<?php
session_start();
include 'db.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and escape form data
    $name = $conn->real_escape_string($_POST['name']);
    $address = $conn->real_escape_string($_POST['address']);
    $city = $conn->real_escape_string($_POST['city']);
    $province = $conn->real_escape_string($_POST['province']);
    $zipcode = $conn->real_escape_string($_POST['zipcode']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $date_of_birth = $conn->real_escape_string($_POST['date_of_birth']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $physical_condition = $conn->real_escape_string($_POST['physical_condition']);
    $plan_id = $conn->real_escape_string($_POST['plan_id']);

    // 1. Insert into Member table
    $sql = "INSERT INTO Member (Name, Address, City, Province, Zipcode, Gender, DateOfBirth, PhoneNo, EmailID, PhysicalCondition)
            VALUES ('$name', '$address', '$city', '$province', '$zipcode', '$gender', '$date_of_birth', '$phone', '$email', '$physical_condition')";

    if ($conn->query($sql) === TRUE) {
        // Get the new MemberID
        $member_id = $conn->insert_id;

        // 2. Insert into Membership table
        $start_date = date("Y-m-d");
        $end_date = date("Y-m-d", strtotime("+1 month")); // Example: membership lasts 1 month
        $status = "Pending";

        $sql2 = "INSERT INTO Membership (MemberID, PlanID, StartDate, EndDate, Status)
                 VALUES ('$member_id', '$plan_id', '$start_date', '$end_date', '$status')";

        if ($conn->query($sql2) === TRUE) {
            $_SESSION['success_message'] = "Successfully Registered!";
            header("Location: index.php");
            exit;
        } else {
            echo "Error inserting into Membership: " . $conn->error;
        }

    } else {
        echo "Error inserting into Member: " . $conn->error;
    }
}

$conn->close();
?>
