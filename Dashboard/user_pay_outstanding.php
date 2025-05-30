<?php
session_start();
include('../db_connection.php');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to pay your bill.";
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

// Function to record bank account transactions
function recordBankTransaction($conn, $account_id, $amount, $transaction_type)
{
    $query = "INSERT INTO Transactions (AccountID, Amount, TransactionType) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ids", $account_id, $amount, $transaction_type);
    return $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $card_number = $_POST['card_number'];
    $payment_amount = $_POST['payment_amount'];

    // Validate the credit card
    $query = "SELECT CreditCardID, CurrentOutstanding FROM CreditCards WHERE CardNumber = ? AND UserID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $card_number, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $card_details = $result->fetch_assoc();
        $credit_card_id = $card_details['CreditCardID'];
        $current_outstanding = $card_details['CurrentOutstanding'];

        if ($payment_amount > $current_outstanding) {
            $message = "Payment amount exceeds the outstanding balance.";
        } else {
            // Get the user's bank account
            $account_query = "SELECT AccountID, Balance FROM Accounts WHERE UserID = ?";
            $account_stmt = $conn->prepare($account_query);
            $account_stmt->bind_param("i", $_SESSION['user_id']);
            $account_stmt->execute();
            $account_result = $account_stmt->get_result();

            if ($account_result->num_rows > 0) {
                $account_details = $account_result->fetch_assoc();
                $account_id = $account_details['AccountID'];
                $account_balance = $account_details['Balance'];

                if ($payment_amount > $account_balance) {
                    $message = "Insufficient account balance.";
                } else {
                    // Deduct from account balance
                    $update_account_query = "UPDATE Accounts SET Balance = Balance - ? WHERE AccountID = ?";
                    $update_account_stmt = $conn->prepare($update_account_query);
                    $update_account_stmt->bind_param("di", $payment_amount, $account_id);

                    if ($update_account_stmt->execute()) {
                        // Record the transactions
                        recordCreditCardTransaction($conn, $credit_card_id, $payment_amount, 'CardBill');
                        recordBankTransaction($conn, $account_id, $payment_amount, 'BillPay');

                        // Update the outstanding balance
                        $update_card_query = "UPDATE CreditCards SET CurrentOutstanding = CurrentOutstanding - ? 
                                              WHERE CreditCardID = ?";
                        $update_card_stmt = $conn->prepare($update_card_query);
                        $update_card_stmt->bind_param("di", $payment_amount, $credit_card_id);
                        $update_card_stmt->execute();

                        $message = "Payment successful!";
                    } else {
                        $message = "Error processing payment.";
                    }
                }
            } else {
                $message = "Bank account not found.";
            }
        }
    } else {
        $message = "Invalid credit card number.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Credit Card Bill</title>
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
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            text-align: center;
            width: 90%;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        form label {
            display: block;
            margin: 15px 0 5px;
            font-size: 14px;
            color: #555;
        }

        form input {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            margin-top: 15px;
            font-size: 14px;
            color: #d9534f;
        }

        .message.success {
            color: #5cb85c;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Pay Credit Card Bill</h1>
        <?php if (isset($message)) : ?>
            <p class="message <?php echo strpos($message, 'successful') !== false ? 'success' : ''; ?>">
                <?php echo $message; ?>
            </p>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="card_number">Card Number:</label>
            <input type="text" name="card_number" required maxlength="12">
            <label for="payment_amount">Payment Amount:</label>
            <input type="number" name="payment_amount" step="0.01" required>
            <button type="submit">Pay</button>
        </form>
    </div>
</body>

</html>