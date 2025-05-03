<?php
session_start(); // Start the session

// Database connection
$conn = new mysqli('localhost', 'root', '', 'udhiram');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = $_POST['phone'];
    $regno = $_POST['regno'];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT bank FROM blood_bank WHERE phone = ? AND regno = ?");
    $stmt->bind_param("ss", $phone, $regno);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a matching record is found
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Set session variables
        $_SESSION['phone'] = $phone; // Store the user's phone (email)
        $_SESSION['bank'] = $row['bank']; // Store the bank name from the query
        
        // Redirect to request.php
        header("Location: request.php?bankid=" . urlencode($regno));
        exit();
    } else {
        echo "Invalid credentials."; // Notify user of invalid credentials
    }

    $stmt->close();
}

$conn->close();
?>
