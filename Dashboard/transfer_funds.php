<?php
session_start();
include('../db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipient_account_number = $_POST['recipient_account_number']; // User enters AccountNumber
    $amount = $_POST['amount'];

    // Validate transfer details
    if (empty($recipient_account_number) || empty($amount)) {
        echo "All fields are required.";
        exit();
    }

    if ($amount <= 0) {
        echo "Amount must be greater than zero.";
        exit();
    }

    // Fetch the sender's account details
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT a.AccountID, a.AccountNumber, a.Balance 
            FROM Accounts a 
            WHERE a.UserID = $user_id";
    $result = $conn->query($sql);
    if ($result->num_rows == 0) {
        echo "Your account does not exist.";
        exit();
    }
    $user = $result->fetch_assoc();

    // Check if sender has sufficient balance
    if ($user['Balance'] < $amount) {
        echo "Insufficient funds.";
        exit();
    }

    // Fetch recipient account using AccountNumber
    $sql = "SELECT AccountID FROM Accounts WHERE AccountNumber = '$recipient_account_number'";
    $result = $conn->query($sql);
    if ($result->num_rows == 0) {
        echo "Recipient account does not exist.";
        exit();
    }
    $recipient_account = $result->fetch_assoc();

    // Begin transaction for safety
    $conn->begin_transaction();
    try {
        // Deduct from sender account
        $sql = "UPDATE Accounts SET Balance = Balance - $amount WHERE AccountID = " . $user['AccountID'];
        $conn->query($sql);

        // Add to recipient account
        $sql = "UPDATE Accounts SET Balance = Balance + $amount WHERE AccountID = " . $recipient_account['AccountID'];
        $conn->query($sql);

        // Log the transaction for sender
        $sql = "INSERT INTO Transactions (AccountID, Amount, TransactionType) 
                VALUES (" . $user['AccountID'] . ", $amount, 'Transfer')";
        $conn->query($sql);

        // Log the transaction for recipient
        $sql = "INSERT INTO Transactions (AccountID, Amount, TransactionType, RecipientAccountID) 
                VALUES (" . $recipient_account['AccountID'] . ", $amount, 'Transfer', " . $user['AccountID'] . ")";
        $conn->query($sql);

        // Commit transaction
        $conn->commit();
        echo "<script>alert('Success');</script>";
    } catch (Exception $e) {
        // Rollback transaction in case of failure
        $conn->rollback();
        echo "Transaction failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Funds</title>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            color: #333;
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }

        /* Container for the form */
        .form-container {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            padding: 30px;
            transition: all 0.3s ease;
        }

        .form-container:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
        }

        /* Heading Styles */
        h1 {
            font-size: 24px;
            text-align: center;
            margin-bottom: 20px;
            color: #4CAF50;
            letter-spacing: 1px;
        }

        /* Form Labels and Inputs */
        label {
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
            font-weight: 500;
            color: #555;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            transition: border 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="number"]:focus {
            border-color: #4CAF50;
            outline: none;
        }

        /* Button Styles */
        button {
            width: 100%;
            padding: 14px;
            background-color: #4CAF50;
            color: #fff;
            font-size: 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #45a049;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .form-container {
                padding: 20px;
                width: 90%;
            }

            h1 {
                font-size: 20px;
            }
        }

        /* Link Styles */
        p {
            text-align: center;
            margin-top: 15px;
        }

        p a {
            color: #007bff;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        p a:hover {
            color: #0056b3;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h1>Transfer Funds</h1>
        <form method="POST" action="transfer_funds.php">
            <label for="recipient_account_number">Recipient Account Number:</label>
            <input type="text" id="recipient_account_number" name="recipient_account_number" required>

            <label for="amount">Amount:</label>
            <input type="number" id="amount" name="amount" step="0.01" required>

            <button type="submit">Transfer</button>
        </form>
        <p><a href="user_dashboard.php">Back to Dashboard</a></p>
    </div>
</body>

</html>