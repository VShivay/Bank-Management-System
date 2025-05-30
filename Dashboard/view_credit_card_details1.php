<?php
session_start();
include('db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to view your credit card details.";
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Fetch the user's credit card details
$query = "SELECT CardNumber, ExpiryDate, CVV, CardLimit, CurrentOutstanding, Status 
          FROM CreditCards 
          WHERE UserID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $card_details = $result->fetch_assoc();

    if ($card_details['Status'] === 'Approved') {
        // Display approved card details
?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Credit Card Details</title>
        </head>

        <body>
            <h1>Your Credit Card Details</h1>
            <p><strong>Card Number:</strong> <?php echo $card_details['CardNumber']; ?></p>
            <p><strong>Expiry Date:</strong> <?php echo date('F Y', strtotime($card_details['ExpiryDate'])); ?></p>
            <p><strong>CVV:</strong> <?php echo $card_details['CVV']; ?></p>
            <p><strong>Card Limit:</strong> ₹<?php echo number_format($card_details['CardLimit'], 2); ?></p>
            <p><strong>Current Outstanding:</strong> ₹<?php echo number_format($card_details['CurrentOutstanding'], 2); ?></p>
        </body>

        </html>
    <?php
    } elseif ($card_details['Status'] === 'Rejected') {
        // Inform the user their card was rejected and offer to reapply
    ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Credit Card Request</title>
        </head>

        <body>
            <h1>Your Credit Card Request</h1>
            <p>Unfortunately, your credit card application has been <strong>rejected</strong>.</p>
            <form method="POST" action="apply_credit_card.php">
                <input type="submit" value="Reapply for Credit Card">
            </form>
        </body>

        </html>
    <?php
    } else {
        // If status is pending
        echo "<p>Your credit card application is still under review.</p>";
    }
} else {
    // No credit card application found
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Credit Card Application</title>
    </head>

    <body>
        <h1>Apply for a Credit Card</h1>
        <form method="POST" action="apply_credit_card.php">
            <input type="submit" value="Apply for Credit Card">
        </form>
    </body>

    </html>
<?php
}
?>