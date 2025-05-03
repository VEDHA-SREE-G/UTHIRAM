<?php
// update_status.php

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

// Retrieve data from AJAX request
$patientId = $_POST['patient_id'];
$bankId = $_POST['bank_id'];
$status = $_POST['status'];
$donate = $_POST['donate'];
$date = $_POST['date'];
$hospitalDetails = $_POST['hospitalDetails'] ?? '';
$hospitalAddress = $_POST['hospitalAddress'] ?? '';
$group = $_POST['group'] ?? '';

// Check if donation is needed
if ($donate > 0) {
    // Fetch hospital coordinates
    $hospitalSql = "SELECT latitude, longitude FROM patient WHERE id = ?";
    $stmt = $conn->prepare($hospitalSql);
    $stmt->bind_param("i", $patientId);
    $stmt->execute();
    $hospitalResult = $stmt->get_result();
    $hospital = $hospitalResult->fetch_assoc();

    // Fetch nearest bank with flag 0
    $bankSql = "SELECT id, latitude, longitude, email FROM banks WHERE flag = 0 ORDER BY (6371 * ACOS(COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(latitude)))) ASC LIMIT 1";
    $stmt = $conn->prepare($bankSql);
    $stmt->bind_param("ddd", $hospital['latitude'], $hospital['longitude'], $hospital['latitude']);
    $stmt->execute();
    $nearestBankResult = $stmt->get_result();

    if ($nearestBankResult->num_rows > 0) {
        $nearestBank = $nearestBankResult->fetch_assoc();

        // Send email to nearest bank
        $to = $nearestBank['email'];
        $subject = "Urgent Donation Needed";
        $message = "A new donation request has been made for patient ID: $patientId.\nPlease prepare for donation of $donate units.";
        mail($to, $subject, $message);

        // Update the patient's record flag
        $updateSql = "UPDATE patient SET flag = 1 WHERE id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("i", $patientId);
        $stmt->execute();

        echo "Status updated and email sent to the nearest bank.";
    } else {
        echo "No available bank found for donation.";
    }
} else {
    // If donation is not needed, just update the status
    $updateSql = "UPDATE patient SET status = ?, donate = ? WHERE id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("ssi", $status, $donate, $patientId);
    $stmt->execute();

    echo "Status updated successfully.";
}

$stmt->close();
$conn->close();
?>
