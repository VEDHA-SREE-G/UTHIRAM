<?php
// Get bank ID from URL
$bankId = $_GET['bankid'] ?? null;
include 'userheader.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        .footer {
            text-align: center;
            padding: 10px;
            background-color: #333;
            color: white;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid black;
        }
        th {
            background-color: black;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .search-box {
            margin-top: 60px;
            width: 400px;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .search-container {
            margin: 20px 0;
            text-align: center;
        }
        .status-select {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .update-button {
            margin-left: 70px; /* Adjust the spacing as needed */
            background-color: #007bff; /* Appealing blue color */
            color: white; /* Text color for the button */
            border: none; /* Remove border */
            padding: 10px 15px; /* Padding for better appearance */
            border-radius: 5px; /* Rounded corners */
            cursor: pointer; /* Pointer cursor on hover */
        }
        .update-button:hover {
            background-color: #0056b3; /* Darker shade of blue on hover */
        }
        .status-select option {
            background-color: #fff; /* White background for options */
        }
        .status-select option[value="Pending"] {
            background-color: #FFEA00; /* Appealing yellow */
        }
        .status-select option[value="Accept"] {
            background-color: #28A745; /* Appealing green */
        }
        .status-select option[value="Reject"] {
            background-color: #DC3545; /* Appealing red */
        }
    </style>
</head>
<body>
    <div class="search-container">
        <input type="text" id="searchBox" class="search-box" placeholder="Search🔍...">
    </div>
    <table id="patientTable">
        <tr>
            <th>ID</th>
            <th>HOSPITAL</th>
            <th>LOCATION</th>
            <th>GROUP</th>
            <th>NEED</th>
            <th>DATE OF OPERATION</th>
            <th>DONATE</th>
            <th>STATUS</th>
        </tr>
        <?php
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

        // Query to fetch the data from the patient table, including status and donation
        $sql = "SELECT id, hospital, address, blood, units, donate, status, date FROM patient";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                // Set default selected status options
                $statusOptions = [
                    'Pending' => '',
                    'Accept' => '',
                    'Reject' => ''
                ];
                // Set the selected option based on the status from the database
                if (isset($row["status"])) {
                    $statusOptions[$row["status"]] = 'selected';
                }

                // Default values for bank and donate
                $bank = "N/A";    
                
                // Donation value from the database
                $donate = ($row["donate"] > 0) ? $row["donate"] : "N/A";  
                
                // Status dropdown and button
                $status = '<select class="status-select" data-current-status="' . $row["status"] . '" onchange="toggleDonateInput(this, ' . $row["id"] . ')">
                    <option value="Pending" ' . $statusOptions['Pending'] . '>Pending</option>
                    <option value="Accept" ' . $statusOptions['Accept'] . '>Accept</option>
                    <option value="Reject" ' . $statusOptions['Reject'] . '>Reject</option>
                </select>
                <button class="update-button" onclick="updateStatus(' . $row["id"] . ', ' . $bankId . ', \'' . $row["hospital"] . '\', \'' . $row["address"] . '\', \'' . $row["blood"] . '\', \'' . $row["date"] . '\');">Update Status</button>';
                
                echo "<tr>";
                echo "<td>" . $row["id"] . "</td>";
                echo "<td class='hospital'>" . $row["hospital"] . "</td>";
                echo "<td class='district'>" . $row["address"] . "</td>";
                echo "<td class='group'>" . $row["blood"] . "</td>";
                echo "<td class='units'>" . $row["units"]. "</td>"; 
                echo "<td class='date'>" . $row["date"]. "</td>";   
                echo "<td id='donate-cell-" . $row["id"] . "'>" . $donate . "</td>";   
                echo "<td>" . $status . "</td>";   
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No records found</td></tr>";
        }
        $conn->close();
        ?>
    </table>
    <footer class="footer">
        <p>© 2024 UTHIRAM. All Rights Reserved.</p>
    </footer>
    <script>
        function toggleDonateInput(selectElement, patientId) {
            const status = selectElement.value;
            const donateCell = document.getElementById('donate-cell-' + patientId);

            if (status === 'Accept') {
                donateCell.innerHTML = '<input type="number" id="donate-' + patientId + '" placeholder="Enter units to donate">';
            } else {
                donateCell.innerHTML = 'N/A';
            }
        }

        function updateStatus(patientId, bankId, hospitalDetails, hospitalAddress, group, date) {
            const statusSelect = event.target.previousElementSibling; // Get the select element
            const newStatus = statusSelect.value; // Get the new selected status
            const currentStatus = statusSelect.getAttribute('data-current-status'); // Get the current status

            // Only update if the status has changed
            if (newStatus !== currentStatus) {
                let donateValue = 0;
                if (newStatus === 'Accept') {
                    donateValue = document.getElementById('donate-' + patientId).value;
                    if (!donateValue || donateValue <= 0) {
                        alert("Please enter a valid number of units to donate.");
                        return;
                    }
                }

                // Make an AJAX request to update the status and donation in the database
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "update_status.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        alert(xhr.responseText); // Display response message
                        location.reload(); // Reload the page to reflect changes
                    }
                };

                // Also send email notification if the status is 'Accept'
                if (newStatus === 'Accept') {
                    xhr.send("patient_id=" + patientId + "&bank_id=" + bankId + "&status=" + newStatus + "&donate=" + donateValue + "&date=" + date + "&hospitalDetails=" + encodeURIComponent(hospitalDetails) + "&hospitalAddress=" + encodeURIComponent(hospitalAddress) + "&group=" + encodeURIComponent(group));
                } else {
                    xhr.send("patient_id=" + patientId + "&bank_id=" + bankId + "&status=" + newStatus + "&donate=" + donateValue + "&date=" + date);
                }
            } else {
                alert("Status is already set to " + currentStatus);
            }
            
        }

        // Add search functionality
        document.getElementById('searchBox').addEventListener('input', function(event) {
            const query = event.target.value.toLowerCase();
            const rows = document.querySelectorAll('#patientTable tr:not(:first-child)');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const match = Array.from(cells).some(cell => cell.textContent.toLowerCase().includes(query));
                row.style.display = match ? '' : 'none';
            });
        });
    </script>
</body>
</html>
