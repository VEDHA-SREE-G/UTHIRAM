<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header</title>
    <style>
         header img{
            margin-left: 20px;
            margin-top: 10px;

        }
        .header {
            display: flex;
            justify-content: space-between; /* Align items to the right */
            align-items: center;
            background-color: #4CAF50;
            color: #ffffff;
            padding: 10px 20px;
            width: 100%; /* Ensure header spans the full width */
            box-sizing: border-box; /* Include padding in width calculation */
            position: fixed; /* Fix the header at the top */
            top: 0;
            left: 0;
            z-index: 1000; /* Ensure it stays on top */
            height: 70px;
        }

        .header-content {
            display: flex;
            align-items: center;
        }

        .email {
            
            margin-right: 20px; /* Space between email and sign-out button */
            font-size: 16px;
            font-family: Arial, sans-serif;
        }

        .logout-btn {
            background-color: #ffffff;
            color: black;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
        }

        .logout-btn:hover {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <div class="header">
    <a href="index.html"><IMG src="uploads/LOGO.png" height=50px></a>
        <?php
        // Display email and bank name based on user role
        if (isset($_SESSION['phone'])) {
            $email = htmlspecialchars($_SESSION['phone']);
            $bankName = htmlspecialchars($_SESSION['bank']);
            echo '<div class="email">' . $email . ' | ' . $bankName . '</div>';
        } else {
            echo '<div class="email">Not logged in</div>';
        }
        ?>
        <a href="logout.php" class="logout-btn">Sign Out</a>
    </div>
</body>
</html>
