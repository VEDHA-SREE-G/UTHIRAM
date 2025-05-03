<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'udhiram'); // Update with your database details

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get form data
    $bank = $_POST['bank'];
    $email = $_POST['phone'];
    $regno = $_POST['regno'];
    $address = $_POST['address'];
    $proof = $_FILES['proof']['tmp_name'];

    // OpenCage API Key
    $apiKey = '9e78f42a42154a82bfc90a1e8c03ee66';  // Update with your actual OpenCage API Key
    
    // Get latitude and longitude from address
    $url = "https://api.opencagedata.com/geocode/v1/json?q=" . urlencode($address) . "&key=" . $apiKey;

    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    // Check for errors in the API response
    if (isset($data['status']['message']) && $data['status']['message'] !== 'OK') {
        echo json_encode(["success" => false, "message" => "API error: " . $data['status']['message']]);
        exit;
    }

    // Check if results exist and extract latitude and longitude
    if (!empty($data['results']) && isset($data['results'][0]['geometry'])) {
        $latitude = $data['results'][0]['geometry']['lat'];
        $longitude = $data['results'][0]['geometry']['lng'];

        // Prepare SQL statement
        $stmt = $conn->prepare("INSERT INTO blood_bank (bank, phone, regno, address, proof, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssdd", $bank, $email,  $regno,$address, $proofContent, $latitude, $longitude);
        
        // Read the content of the uploaded file
        $proofContent = file_get_contents($proof);
        
        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            error_log("Database Insert Error: " . $stmt->error); // Log the error
            echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "No results found for the given address."]);
    }

    $conn->close();
}
?>
