<?php
// Database connection
$servername = "localhost";
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "udhiram"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch the data from the patient table
$sql = "SELECT id, hospital, address FROM patient";
$result = $conn->query($sql);

// Check if any rows were returned
if ($result->num_rows > 0) {
    // Output each row as a table row
    while($row = $result->fetch_assoc()) {
        // Set default values for missing fields
        $bank = "N/A";    // Default value for bank
        $donate = "N/A";  // Default value for donate
        $status = "Pending"; 
        $need = "N/A";
        // Default value for status
        
        echo "<tr>";
        echo "<td>" . $row["id"] . "</td>";
        echo "<td>" . $row["hospital"] . "</td>";
        echo "<td>" . $row["address"] . "</td>";
        echo "<td>" . $need . "</td>";
        echo "<td>" . $bank . "</td>";      // Default bank value
        echo "<td>" . $donate . "</td>";    // Default donate value
        echo "<td>" . $status . "</td>";    // Default status value
        echo "</tr>";
    }
} else {
    // No records found, display a message
    echo "<tr><td colspan='7'>No records found</td></tr>";
}

$conn->close();
?>
