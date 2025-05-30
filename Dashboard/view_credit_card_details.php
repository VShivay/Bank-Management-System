<?php
session_start();
include('../db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to view your credit card details.";
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Fetch credit card details for the user
$query = "SELECT CardNumber, ExpiryDate, CVV, CardLimit, CurrentOutstanding, Status 
          FROM CreditCards 
          WHERE UserID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $card_details = $result->fetch_assoc();

?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Credit Card Details</title>
        <style>
            /* General Styles */
            body {
                font-family: 'Arial', sans-serif;
                background-color: #f4f4f9;
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            }

            h1 {
                color: #333;
            }

            p {
                color: #555;
                margin: 10px 0;
            }

            .container {
                background: #fff;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                padding: 20px 30px;
                max-width: 500px;
                text-align: center;
                width: 90%;
            }

            .btn {
                display: inline-block;
                margin-top: 20px;
                padding: 10px 20px;
                background-color: #007bff;
                color: #fff;
                text-decoration: none;
                font-size: 16px;
                border-radius: 5px;
                transition: background-color 0.3s ease;
            }

            .btn:hover {
                background-color: #0056b3;
            }

            .card-detail {
                margin: 15px 0;
            }

            .card-detail strong {
                display: block;
                font-size: 14px;
                color: #007bff;
            }

            @media (max-width: 600px) {
                .container {
                    padding: 15px;
                }

                .btn {
                    font-size: 14px;
                    padding: 8px 15px;
                }
            }
        </style>
    </head>

    <body>
        <div class="container">
            <?php
            if ($card_details['Status'] === 'Approved') {
                // Approved Credit Card Details
            ?>
                <h1>Your Approved Credit Card Details</h1>
                <div class="card-detail">
                    <strong>Card Number:</strong>
                    <p><?php echo $card_details['CardNumber']; ?></p>
                </div>
                <div class="card-detail">
                    <strong>Expiry Date:</strong>
                    <p><?php echo date('F Y', strtotime($card_details['ExpiryDate'])); ?></p>
                </div>
                <div class="card-detail">
                    <strong>CVV:</strong>
                    <p><?php echo $card_details['CVV']; ?></p>
                </div>
                <div class="card-detail">
                    <strong>Card Limit:</strong>
                    <p>₹<?php echo number_format($card_details['CardLimit'], 2); ?></p>
                </div>
                <div class="card-detail">
                    <strong>Current Outstanding:</strong>
                    <p>₹<?php echo number_format($card_details['CurrentOutstanding'], 2); ?></p>
                </div>
                <a href="user_dashboard.php" class="btn">Back to Dashboard</a>
            <?php
            } elseif ($card_details['Status'] === 'Pending') {
                // Pending Status
            ?>
                <h1>Your Credit Card Request</h1>
                <p>Your credit card application is under review. Please wait for approval.</p>
                <a href="user_dashboard.php" class="btn">Back to Dashboard</a>
            <?php
            } elseif ($card_details['Status'] === 'Rejected') {
                // Rejected Status
            ?>
                <h1>Your Credit Card Request</h1>
                <p>Unfortunately, your credit card application was <strong>rejected</strong>.</p>
                <form method="POST" action="apply_credit_card.php">
                    <input type="submit" class="btn" value="Reapply for Credit Card">
                </form>
            <?php
            }
            ?>
        </div>
    </body>

    </html>
<?php
} else {
    // No Credit Card Application Found
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Apply for Credit Card</title>
        <style>
            body {
                font-family: 'Arial', sans-serif;
                background-color: #f4f4f9;
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            }

            .container {
                background: #fff;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                padding: 20px 30px;
                max-width: 500px;
                text-align: center;
                width: 90%;
            }

            .btn {
                display: inline-block;
                margin-top: 20px;
                padding: 10px 20px;
                background-color: #007bff;
                color: #fff;
                text-decoration: none;
                font-size: 16px;
                border-radius: 5px;
                transition: background-color 0.3s ease;
            }

            .btn:hover {
                background-color: #0056b3;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <h1>Apply for a Credit Card</h1>
            <form method="POST" action="apply_credit_card.php">
                <input type="submit" class="btn" value="Apply for Credit Card">
            </form>
        </div>
    </body>

    </html>
<?php
}
?>