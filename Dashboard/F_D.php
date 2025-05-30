<?php
// Start the session and check if the user is logged in
session_start();

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Include database connection file
include('../db_connection.php');

// Initialize variables
$error = "";
$success = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_fd'])) {
    // Get the form input values
    $amount = $_POST['amount'];
    $tenure = $_POST['tenure'];

    // Validate input
    if (empty($amount) || empty($tenure) || $amount <= 0 || $tenure <= 0) {
        $error = "Please enter valid amount and tenure.";
    } else {
        // Get the logged-in user's ID from the session
        $user_id = $_SESSION['user_id'];

        // Retrieve user's account ID and balance from the database
        $query = "SELECT AccountID, Balance FROM Accounts WHERE UserID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($account_id, $balance);
        $stmt->fetch();
        $stmt->close();

        if ($balance < $amount) {
            $error = "Insufficient balance in your account.";
        } else {
            // Debit the amount from the user's account balance
            $new_balance = $balance - $amount;

            // Update the user's account balance
            $update_query = "UPDATE Accounts SET Balance = ? WHERE AccountID = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("di", $new_balance, $account_id);
            if ($update_stmt->execute()) {
                // Insert the fixed deposit record
                $interest_rate = 7.00; // Default interest rate of 7%
                $maturity_amount = $amount * pow(1 + ($interest_rate / 100 / 12), $tenure); // Calculate maturity amount
                $start_date = date('Y-m-d');
                $maturity_date = date('Y-m-d', strtotime("+$tenure months"));

                $insert_fd_query = "INSERT INTO FixedDeposits (UserID, AccountID, Amount, InterestRate, TenureMonths, MaturityAmount, StartDate, MaturityDate, Status) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active')";
                $insert_fd_stmt = $conn->prepare($insert_fd_query);
                $insert_fd_stmt->bind_param("iiidddss", $user_id, $account_id, $amount, $interest_rate, $tenure, $maturity_amount, $start_date, $maturity_date);

                if ($insert_fd_stmt->execute()) {
                    // Insert the transaction record
                    $transaction_query = "INSERT INTO Transactions (AccountID, Amount, TransactionType, Timestamp) 
                                          VALUES (?, ?, 'FD Creation', NOW())";
                    $transaction_stmt = $conn->prepare($transaction_query);
                    $transaction_stmt->bind_param("id", $account_id, $amount);

                    if ($transaction_stmt->execute()) {
                        $success = "Fixed deposit created and transaction recorded successfully.";
                    } else {
                        $error = "Failed to record the transaction.";
                    }
                    $transaction_stmt->close();
                } else {
                    $error = "Failed to create fixed deposit.";
                }
                $insert_fd_stmt->close();
            } else {
                $error = "Failed to update your account balance.";
            }
            $update_stmt->close();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Fixed Deposit</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Create Fixed Deposit</h1>

        <?php
        // Display error or success message
        if ($error) {
            echo "<p class='text-red-500 text-sm mb-4'>$error</p>";
        }
        if ($success) {
            echo "<p class='text-green-500 text-sm mb-4'>$success</p>";
        }
        ?>

        <!-- Form to enter amount and tenure -->
        <form action="F_D.php" method="POST" class="space-y-4">
            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                <input
                    type="number"
                    name="amount"
                    id="amount"
                    required
                    class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="tenure" class="block text-sm font-medium text-gray-700">Tenure (Months)</label>
                <input
                    type="number"
                    name="tenure"
                    id="tenure"
                    required
                    class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <button
                type="submit"
                name="create_fd"
                class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                Create Fixed Deposit
            </button>
        </form>

        <!-- Back to Dashboard link -->
        <p class="text-center text-sm text-gray-500 mt-4">
            <a href="user_dashboard.php" class="text-indigo-600 hover:underline">Back to Dashboard</a>
        </p>
    </div>
</body>

</html>