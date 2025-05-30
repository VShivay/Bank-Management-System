<?php
session_start();
include('../db_connection.php');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert'>You must be logged in to view your credit card history.</div>";
    exit();
}

// Fetch credit card transactions
$query = "SELECT * FROM CreditCardTransactions 
          WHERE CreditCardID IN (SELECT CreditCardID FROM CreditCards WHERE UserID = ?) 
          ORDER BY Timestamp DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credit Card Transaction History</title>
    <style>
        /* Reset some default styles */
        body,
        p,
        div {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Basic Body Styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f6;
            color: #333;
            padding: 20px;
        }

        /* Alert Box Styles */
        .alert {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        /* Transaction List Styles */
        .transactions {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        /* Single Transaction Card */
        .transaction-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            border: 1px solid #e0e0e0;
            transition: transform 0.2s ease-in-out;
        }

        .transaction-card:hover {
            transform: scale(1.05);
        }

        /* Style for Transaction Info */
        .transaction-card p {
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .transaction-card p strong {
            color: #007BFF;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }

            .transactions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <?php
    if ($result->num_rows > 0) {
        echo "<div class='transactions'>";
        while ($row = $result->fetch_assoc()) {
            echo "<div class='transaction-card'>";
            echo "<p><strong>Transaction ID:</strong> " . $row['TransactionID'] . "</p>";
            echo "<p><strong>Amount:</strong> $" . number_format($row['Amount'], 2) . "</p>";
            echo "<p><strong>Transaction Type:</strong> " . $row['TransactionType'] . "</p>";
            echo "<p><strong>Payment Type:</strong> " . $row['PaymentType'] . "</p>";
            echo "<p><strong>Timestamp:</strong> " . $row['Timestamp'] . "</p>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<div class='alert'>No transactions found.</div>";
    }
    ?>

</body>

</html>