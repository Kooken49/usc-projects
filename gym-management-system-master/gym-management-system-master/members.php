<?php
include 'db.php';  // Include the database connection file

// Fetch all members
$sql = "SELECT * FROM Member";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 50px;
            /* background-color: #f4f4f4; */
        }
        h2 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ccc;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #e2e2e2;
        }
        .no-members {
            text-align: center;
            color: #888;
        }
    </style>
</head>
<body>
    <h2>Member List</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Address</th>
            <th>Email</th>
            <th>Phone</th>
        </tr>

        <?php
        // Check if there are members and display them
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row["Member ID"]) . "</td>
                        <td>" . htmlspecialchars($row["Name"]) . "</td>
                        <td>" . htmlspecialchars($row["Address"]) . "</td>
                        <td>" . htmlspecialchars($row["EmailID"]) . "</td>
                        <td>" . htmlspecialchars($row["PhoneNo"]) . "</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='5' class='no-members'>No members found</td></tr>";
        }
        ?>
    </table>
</body>
</html>

<?php
$conn->close(); // Close the database connection
?>
