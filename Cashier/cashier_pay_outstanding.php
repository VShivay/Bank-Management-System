<?php
session_start();
include('db_connection.php');

// Ensure the cashier is logged in
if (!isset($_SESSION['cashier_id'])) {
    $message = "You must be logged in as a cashier to process payments.";
    $message_class = "error";
    echo "<script>window.onload = function(){document.getElementById('message').style.display = 'block';}</script>";
    exit();
}

// Function to record credit card transactions
function recordCreditCardTransaction($conn, $credit_card_id, $amount, $payment_type)
{
    $query = "INSERT INTO CreditCardTransactions (CreditCardID, Amount, TransactionType, PaymentType) 
              VALUES (?, ?, 'Payment', ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ids", $credit_card_id, $amount, $payment_type);
    return $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $card_number = $_POST['card_number'];
    $payment_amount = $_POST['payment_amount'];

    // Validate the credit card
    $query = "SELECT CreditCardID, CurrentOutstanding FROM CreditCards WHERE CardNumber = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $card_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $card_details = $result->fetch_assoc();
        $credit_card_id = $card_details['CreditCardID'];
        $current_outstanding = $card_details['CurrentOutstanding'];

        if ($payment_amount > $current_outstanding) {
            $message = "Payment amount exceeds the outstanding balance.";
            $message_class = "error";
        } else {
            // Record the transaction
            if (recordCreditCardTransaction($conn, $credit_card_id, $payment_amount, 'Cashier')) {
                // Update the outstanding balance
                $update_card_query = "UPDATE CreditCards SET CurrentOutstanding = CurrentOutstanding - ? 
                                      WHERE CreditCardID = ?";
                $update_card_stmt = $conn->prepare($update_card_query);
                $update_card_stmt->bind_param("di", $payment_amount, $credit_card_id);
                $update_card_stmt->execute();

                $message = "Payment processed successfully by the cashier.";
                $message_class = "success";
            } else {
                $message = "Error processing payment.";
                $message_class = "error";
            }
        }
    } else {
        $message = "Invalid credit card number.";
        $message_class = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Pay Outstanding</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            max-width: 800px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-size: 16px;
            font-weight: bold;
            color: #555;
        }

        input[type="text"],
        input[type="number"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            color: #333;
            width: 100%;
        }

        input[type="text"]:focus,
        input[type="number"]:focus {
            border-color: #007bff;
            outline: none;
        }

        button {
            padding: 12px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            text-align: center;
            padding: 15px;
            margin-top: 20px;
            font-weight: bold;
            display: none;
        }

        .success {
            color: #28a745;
        }

        .error {
            color: #dc3545;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Process Credit Card Payment</h1>
        <form method="POST" action="">
            <label for="card_number">Card Number:</label>
            <input type="text" name="card_number" required maxlength="12" placeholder="Enter card number">

            <label for="payment_amount">Payment Amount:</label>
            <input type="number" name="payment_amount" step="0.01" required placeholder="Enter payment amount">

            <button type="submit">Process Payment</button>
        </form>

        <?php
        // Error or success message output
        if (isset($message)) {
            echo "<div id='message' class='message $message_class'>$message</div>";
        }
        ?>
    </div>
</body>

</html>