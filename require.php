<?php
$hostname = "localhost";
$username = "root";
$password = "";
$database = "udhiram";

// Capture POST data
$attender = $_POST['attender'];
$phone = $_POST['phone'];
$a_address=$_POST['a_address'];
$a_aadhar=$_POST['a_aadhar'];
$patient = $_POST['patient'];
$p_aadhar=$_POST['p_aadhar'];
$group = $_POST['group'];
$doctor = $_POST['doctor'];
$regno = $_POST['regno'];
$desig = $_POST['desig'];
$hospital = $_POST['hospital'];
$units = $_POST['units'];
$address = $_POST['address'];
$date = $_POST['date'];
// Handle file upload
if ($_FILES['report']['error'] !== UPLOAD_ERR_OK) {
    die("File upload error.");
}
$pdf_files = $_FILES['report']['tmp_name'];
$report = file_get_contents($pdf_files);
if ($_FILES['a_copy']['error'] !== UPLOAD_ERR_OK) {
    die("File upload error.");
}
$files = $_FILES['a_copy']['tmp_name'];
$copy_a = file_get_contents($files);
if ($_FILES['p_copy']['error'] !== UPLOAD_ERR_OK) {
    die("File upload error.");
}
$pdf = $_FILES['p_copy']['tmp_name'];
$copy_p = file_get_contents($pdf);
// Create database connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare SQL statement
$sql = "INSERT INTO patient(attender, phone, patient, blood, units, doctor, regno, hospital, address, report,a_address,a_aadhar,a_copy,p_aadhar,p_copy,desig,date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?,?,?,?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssssssssssss", $attender, $phone, $patient, $group, $units, $doctor, $regno, $hospital, $address, $report,$a_address,$a_aadhar,$a_copy,$p_aadhar,$p_copy,$desig,$date);

// Execute statement and check for errors
if ($stmt->execute()) {
    echo "Record inserted successfully.";
} else {
    echo "Error inserting record: " . $stmt->error;
}

// Clean up
$stmt->close();
$conn->close();
?>
