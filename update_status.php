<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "udhiram";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get POST data
$patientId = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0;
$bankId = isset($_POST['bank_id']) ? intval($_POST['bank_id']) : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';
$donate = isset($_POST['donate']) ? intval($_POST['donate']) : 0;

// Ensure that required data exists
if (empty($patientId) || empty($bankId) || empty($status)) {
    die("Missing required fields: patient_id, bank_id, or status.");
}

// Fetch hospital, address, and units from the patient table
$patientSql = "SELECT attender,phone,a_aadhar,p_aadhar,doctor,desig,patient,hospital, address, latitude, longitude, date, blood, units FROM patient WHERE id = ?";
$patientStmt = $conn->prepare($patientSql);
if (!$patientStmt) {
    die("Prepare failed for patient query: " . $conn->error);
}
$patientStmt->bind_param("i", $patientId);
$patientStmt->execute();
$patientResult = $patientStmt->get_result();

if ($patientResult->num_rows > 0) {
    $patientData = $patientResult->fetch_assoc();
    $hospitalDetails = $patientData['hospital'];
    $hospitalAddress = $patientData['address'];
    $latitude = $patientData['latitude'];  
    $longitude = $patientData['longitude'];  
    $date = $patientData['date'];
    $group = $patientData['blood'];
    $currentUnits = $patientData['units'];
    $attender= $patientData['attender'];
    $phone= $patientData['phone'];
    $a_aadhar= $patientData['a_aadhar'];
    $patient= $patientData['patient'];
    $p_aadhar= $patientData['p_aadhar'];
    $doctor= $patientData['doctor'];
    $desig= $patientData['desig'];
} else {
    die("No patient found with ID: " . $patientId);
}

// Fetch bank and district from blood_bank table
$bankSql = "SELECT bank, address FROM blood_bank WHERE regno = ?";
$bankStmt = $conn->prepare($bankSql);
if (!$bankStmt) {
    die("Prepare failed for bank query: " . $conn->error);
}
$bankStmt->bind_param("i", $bankId);
$bankStmt->execute();
$bankResult = $bankStmt->get_result();

if ($bankResult->num_rows > 0) {
    $bankData = $bankResult->fetch_assoc();
    $bankDetails = $bankData['bank'];
    $bankDistrict = $bankData['address'];
} else {
    die("No bank found with ID: " . $bankId);
}

// Update the status and donation in the patient table
$sql = "UPDATE patient SET status = ?, donate = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed for update query: " . $conn->error);
}
$stmt->bind_param("sii", $status, $donate, $patientId);

if ($stmt->execute()) {
    echo "Status and donation updated successfully.";

    // If donation amount is greater than 0, send an email
    if ($donate > 0) {
        $emailData = json_encode([
            'bankDetails' => $bankDetails,
            'bankDistrict' => $bankDistrict,
            'hospitalDetails' => $hospitalDetails,
            'hospitalAddress' => $hospitalAddress,
            'donate' => $donate,
            'date' => $date,
            'group' => $group,
        ]);

        // Send email request to Node.js server
        $ch = curl_init('http://localhost:3001/send-email');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $emailData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            echo 'Curl error: ' . curl_error($ch);
        } else {
            echo 'Email send response: ' . $response;
        }
        curl_close($ch);
    }

    // Handle units if donation is greater than 0
    if ($donate > 0) {
        if ($currentUnits == $donate) {
            // Delete the row if need is equal to donate
            $deleteSql = "DELETE FROM patient WHERE id = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            if (!$deleteStmt) {
                die("Prepare failed for delete query: " . $conn->error);
            }
            $deleteStmt->bind_param("i", $patientId);

            if ($deleteStmt->execute()) {
                echo "Row deleted successfully after fulfilling the blood need.";
            } else {
                die("Error deleting row: " . $deleteStmt->error);
            }
            $deleteStmt->close();
        } elseif ($currentUnits > $donate) {
            // Update the row if need > donate
            $newUnits = $currentUnits - $donate;
            $updateSql = "UPDATE patient SET units = ?, donate = 'n/a', status = 'pending' WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            if (!$updateStmt) {
                die("Prepare failed for update query: " . $conn->error);
            }
            $updateStmt->bind_param("ii", $newUnits, $patientId);

            if ($updateStmt->execute()) {
                echo "Units updated to " . $newUnits . ", donation set to 'n/a', and status set to 'pending'.";
               // Assuming you have the following variables defined:
// $patientId, $bankId, $bankName, $bankAddress, $donatingUnits, $action


        }
    }
    $insertSql = "INSERT INTO tracks(patient_id, bank_id, bank_name, bank_address, donating_units, action, date) VALUES (?, ?, ?, ?, ?, 'DONATE', NOW())";
$insertStmt = $conn->prepare($insertSql);

if (!$insertStmt) {
    die("Prepare failed: " . $conn->error);
}

// Bind the parameters
$insertStmt->bind_param("iissi", $patientId, $bankId, $bankDetails , $bankDistrict, $donate);

// Execute the statement
if ($insertStmt->execute()) {
    echo "Tracking entry created successfully.";
} else {
    die("Error inserting into track table: " . $insertStmt->error);
}

// Close the statement
$insertStmt->close();

            } else {
                die("Error updating units: " . $updateStmt->error);
            }
} else {
    die("Error updating status and donation: " . $stmt->error);
}

// Additional logic for sending an email to the nearest bank when need > donate
if ($currentUnits > $donate) {
    // Update the row if need > donate
    $newUnits = $currentUnits - $donate;
    $updateSql = "UPDATE patient SET units = ?, donate = 'n/a', status = 'pending' WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    if (!$updateStmt) {
        die("Prepare failed for update query: " . $conn->error);
    }
    $updateStmt->bind_param("ii", $newUnits, $patientId);

    if ($updateStmt->execute()) {
        echo "Units updated to " . $newUnits . ", donation set to 'n/a', and status set to 'pending'.";

        // If the need is still greater than the donation, find the nearest bank with flag != 1
      // Ensure latitude and longitude are numeric values
if (!is_numeric($latitude) || !is_numeric($longitude)) {
    die("Invalid latitude or longitude values.");
}

// Prepare SQL query using subquery or left join
// ... (previous code)

// Prepare SQL query to find the nearest blood bank using regno and phone
$bankSql = "
    SELECT blood_bank.id, blood_bank.bank, blood_bank.address, blood_bank.phone
FROM blood_bank
WHERE blood_bank.id NOT IN (
    SELECT bank_id 
    FROM patient_bank 
    WHERE patient_id = ? AND flag = 1
)
ORDER BY (6371 * ACOS(
    COS(RADIANS(?)) * COS(RADIANS(blood_bank.latitude)) * COS(RADIANS(blood_bank.longitude) - RADIANS(?)) 
    + SIN(RADIANS(?)) * SIN(RADIANS(blood_bank.latitude))
)) ASC 
LIMIT 1

";

$bankStmt = $conn->prepare($bankSql);
if (!$bankStmt) {
    die("Prepare failed for nearest bank query: " . $conn->error);
}

$bankStmt->bind_param("dddd", $patientId, $latitude, $longitude, $latitude);

$bankStmt->execute();
$bankResult = $bankStmt->get_result();

if ($bankResult->num_rows > 0) {
    $nearestBank = $bankResult->fetch_assoc();
    $bankPhone = $nearestBank['phone'];
    $emailData = json_encode([
        'email'=>$bankPhone,
        'hospital'=>$hospitalDetails,
        'address'=>$hospitalAddress,
        'date'=>$date ,
        'group'=>$group ,
      'unit'=>$newUnits,
      'patient'=>$patient,
      'p_aadhar'=>$p_aadhar,
      'attender'=>$attender,
      'phone'=>$phone,
      'a_aadhar'=>$a_aadhar,
      'doctor'=>$doctor,
      'desig'=>$desig,
    ]);

    $ch = curl_init('http://localhost:3002/send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $emailData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            echo 'Curl error: ' . curl_error($ch);
        } else {
            echo 'Email send response: ' . $response;
        }
        curl_close($ch);
    

        // Insert a new entry in the patient_blood table for the selected blood bank
        $flagInsertSql = "INSERT INTO patient_bank (patient_id,bank_id, flag) VALUES (?, ?, 1)";
        $flagStmt = $conn->prepare($flagInsertSql);
        $flagStmt->bind_param("ii", $patientId, $nearestBank['id']); // Using regno for the bank ID
        $flagStmt->execute();
        echo "Flag set for bank ID: " . $nearestBank['id'];
    } else {
        echo "Error sending email.";
    }

    // Insert into patient_bank table using bank_id and patient_id
    $patientBankSql = "INSERT INTO patient_bank (bank_id, patient_id) VALUES (?, ?)";
    $patientBankStmt = $conn->prepare($patientBankSql);
    $patientBankStmt->bind_param("ii", $nearestBank['id'], $patientId); // Using regno for bank_id
    $patientBankStmt->execute();

    if ($patientBankStmt) {
        echo "Inserted into patient_bank: bank_id = " . $nearestBank['id'] . ", patient_id = " . $patientId;
    } else {
        echo "Error inserting into patient_bank.";
    }


// ... (remaining code)

    } else {
        die("Error updating units: " . $updateStmt->error);
    }
    $updateStmt->close();
}

$conn->close();
?>
