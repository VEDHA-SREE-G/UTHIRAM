<?php
include 'a_header.php'; // Include your header file
$conn = new mysqli("localhost", "root", "", "udhiram");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Assume patient_id is provided (you can get this from a session or URL parameter)
$patientId = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;// Replace with the actual patient ID you want to fetch

// Prepare and execute the SQL statement
$sql = "SELECT * FROM tracks WHERE patient_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patientId);
$stmt->execute();
$result = $stmt->get_result();

// Debug: Check if the query executed successfully and returned rows
if (!$result) {
    echo "Error executing query: " . $stmt->error;
    exit();
}

// Output number of rows returned for debugging
// echo "<div>Number of rows returned: " . $result->num_rows . "</div>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Process</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column; /* Arrange items vertically */
            height: 100vh;
            background-color: #f0f0f0;
            font-family: Arial, sans-serif;
            margin: 0; /* Remove default margin */
        }
        .tracking-status {
            display: flex; /* Use flex for alignment within each status */
            align-items: center;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: box-shadow 0.3s;
            margin: 10px 0; /* Add vertical spacing between statuses */
            width: 300px; /* Set a fixed width for consistency */
        }
        .tracking-status:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        .icon {
            font-size: 40px;
            color: #ff9800; /* Orange */
            margin-right: 15px; /* Space between icon and text */
        }
        .status-text {
            font-size: 18px;
            color: #333; /* Dark text */
        }
    </style>
</head>
<body>

<!-- Default tracking status -->
<div class="tracking-status">
    <i class="fa-solid fa-exclamation-triangle icon"></i>
    <span class="status-text">Need Raised</span>
</div>

<?php
// Check if any results were returned
if ($result->num_rows > 0) {
    // Fetch each row and display the tracking status
    while ($row = $result->fetch_assoc()) {
        echo '<div class="tracking-status">';
        echo '<i class="fa-solid fa-exclamation-triangle icon"></i>';
        echo '<span class="status-text">Patient ID: ' . htmlspecialchars($row['patient_id']) . ' - Bank Name: ' . htmlspecialchars($row['bank_name']) . ' - Donating Units: ' . htmlspecialchars($row['donating_units']) . '</span>';
        echo '</div>';
    }
} else {
    echo '<div class="tracking-status">';
    echo '<i class="fa-solid fa-info-circle icon"></i>';
    echo '<span class="status-text">In Progress! for Patient ID: ' . htmlspecialchars($patientId) . '</span>';
    echo '</div>';
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>

</body>
</html>
