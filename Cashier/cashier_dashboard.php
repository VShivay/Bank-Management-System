<?php
// Include database connection
include('../db_connection.php');

// Start session and check if cashier is logged in
session_start();
if (!isset($_SESSION['cashier_id'])) {
    header('Location: cashier_login.php'); // Redirect to login if not authenticated
    exit();
}

// Function to execute database queries
function execute_query($sql)
{
    global $conn;
    $result = $conn->query($sql);
    if (!$result) {
        die("Database Query Failed: " . $conn->error);
    }
    return $result;
}

// Function to validate account type
function validate_account_type($account_number, $account_type)
{
    global $conn;
    $sql = "SELECT Acc_type FROM Accounts WHERE AccountNumber = '$account_number'";
    $result = execute_query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['Acc_type'] === $account_type;
    }
    return false; // Account not found
}

// Handle Deposit
if (isset($_POST['deposit'])) {
    $account_number = $_POST['deposit_account_number'];
    $account_type = $_POST['account_type'];
    $amount = $_POST['deposit_amount'];

    if (!validate_account_type($account_number, $account_type)) {
        echo "<script>alert('Invalid Account Type for this account number.');</script>";
        exit();
    }

    if ($amount > 0) {
        $sql = "UPDATE Accounts SET Balance = Balance + $amount WHERE AccountNumber = '$account_number'";
        if (execute_query($sql) && $conn->affected_rows > 0) {
            $sql = "INSERT INTO Transactions (AccountID, Amount, TransactionType) 
                    SELECT AccountID, $amount, 'Deposit' FROM Accounts WHERE AccountNumber = '$account_number'";
            execute_query($sql);

            echo "<script>alert('Deposit Successful');</script>";
        } else {
            echo "<script>alert('Deposit Failed: Invalid Account Number');</script>";
        }
    } else {
        echo "<script>alert('Amount must be positive');</script>";
    }
}

// Handle Withdrawal
if (isset($_POST['withdraw'])) {
    $account_number = $_POST['withdraw_account_number'];
    $account_type = $_POST['account_type'];
    $amount = $_POST['withdraw_amount'];

    if (!validate_account_type($account_number, $account_type)) {
        echo "<script>alert('Invalid Account Type for this account number.');</script>";
        exit();
    }

    if ($amount > 0) {
        $sql = "SELECT AccountID, Balance FROM Accounts WHERE AccountNumber = '$account_number'";
        $result = execute_query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['Balance'] >= $amount) {
                $sql = "UPDATE Accounts SET Balance = Balance - $amount WHERE AccountNumber = '$account_number'";
                execute_query($sql);

                $account_id = $row['AccountID'];
                $sql = "INSERT INTO Transactions (AccountID, Amount, TransactionType) 
                        VALUES ($account_id, $amount, 'Withdrawal')";
                execute_query($sql);

                echo "<script>alert('Withdrawal Successful');</script>";
            } else {
                echo "<script>alert('Insufficient Balance');</script>";
            }
        } else {
            echo "<script>alert('Invalid Account Number');</script>";
        }
    } else {
        echo "<script>alert('Amount must be positive');</script>";
    }
}

// Handle Fund Transfer
if (isset($_POST['transfer'])) {
    $sender_account_number = $_POST['transfer_sender_account_number'];
    $sender_account_type = $_POST['sender_account_type'];
    $receiver_account_number = $_POST['transfer_receiver_account_number'];
    $amount = $_POST['transfer_amount'];

    if (!validate_account_type($sender_account_number, $sender_account_type)) {
        echo "<script>alert('Sender Account Type does not match.');</script>";
        exit();
    }

    if ($amount > 0) {
        $sql = "SELECT AccountID, Balance FROM Accounts WHERE AccountNumber = '$sender_account_number'";
        $result = execute_query($sql);

        if ($result->num_rows > 0) {
            $sender_row = $result->fetch_assoc();
            if ($sender_row['Balance'] >= $amount) {
                $conn->begin_transaction();

                try {
                    $sql = "UPDATE Accounts SET Balance = Balance - $amount WHERE AccountNumber = '$sender_account_number'";
                    execute_query($sql);

                    $sql = "UPDATE Accounts SET Balance = Balance + $amount WHERE AccountNumber = '$receiver_account_number'";
                    execute_query($sql);

                    $sender_account_id = $sender_row['AccountID'];
                    $sql = "INSERT INTO Transactions (AccountID, Amount, TransactionType, RecipientAccountID) 
                            SELECT $sender_account_id, $amount, 'Transfer', AccountID 
                            FROM Accounts WHERE AccountNumber = '$receiver_account_number'";
                    execute_query($sql);

                    $conn->commit();
                    echo "<script>alert('Transfer Successful');</script>";
                } catch (Exception $e) {
                    $conn->rollback();
                    echo "<script>alert('Transfer Failed: " . $e->getMessage() . "');</script>";
                }
            } else {
                echo "<script>alert('Insufficient Balance in Sender\'s Account');</script>";
            }
        } else {
            echo "<script>alert('Invalid Sender Account Number');</script>";
        }
    } else {
        echo "<script>alert('Amount must be positive');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-b from-blue-100 to-blue-300">
    <header class="bg-blue-600 p-4 text-white flex justify-between">
        <h1 class="text-xl font-bold">Cashier Dashboard</h1>
        <a href="logout4.php" class="bg-red-500 px-4 py-2 rounded hover:bg-red-600">Logout</a>
    </header>
    <main class="container mx-auto mt-8 p-4 bg-white shadow-lg rounded">
        <h2 class="text-2xl font-bold mb-4">Manage Transactions</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="cashier_dashboard.php?action=deposit" class="block bg-blue-500 text-white p-4 rounded text-center hover:bg-blue-600">
                Deposit Funds
            </a>
            <a href="cashier_dashboard.php?action=withdraw" class="block bg-green-500 text-white p-4 rounded text-center hover:bg-green-600">
                Withdraw Funds
            </a>
            <a href="cashier_dashboard.php?action=transfer" class="block bg-yellow-500 text-white p-4 rounded text-center hover:bg-yellow-600">
                Transfer Funds
            </a>
            <a href="cashier_pay_outstanding.php" class="block bg-green-500 text-white p-4 rounded text-center hover:bg-green-600">
                Pay Card Bill
            </a>
        </div>

        <?php if (isset($_GET['action']) && $_GET['action'] == 'deposit'): ?>
            <form action="cashier_dashboard.php" method="POST" class="mt-8 p-4 bg-gray-100 rounded shadow">
                <h3 class="text-xl font-bold mb-4">Deposit Funds</h3>
                <label>Account Number:</label>
                <input type="text" name="deposit_account_number" class="border p-2 mb-2 w-full" required>
                <label>Account Type:</label>
                <select name="account_type" class="border p-2 mb-2 w-full" required>
                    <option value="Saving">Saving</option>
                    <option value="Current">Current</option>
                </select>
                <label>Amount:</label>
                <input type="number" name="deposit_amount" step="0.01" class="border p-2 mb-2 w-full" required>
                <button type="submit" name="deposit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Deposit
                </button>
            </form>
        <?php endif; ?>
        <?php if (isset($_GET['action']) && $_GET['action'] == 'withdraw'): ?>
            <form action="cashier_dashboard.php" method="POST" class="mt-8 p-4 bg-gray-100 rounded shadow">
                <h3 class="text-xl font-bold mb-4">Withdraw Funds</h3>
                <label>Account Number:</label>
                <input type="text" name="withdraw_account_number" class="border p-2 mb-2 w-full" required>
                <label>Account Type:</label>
                <select name="account_type" class="border p-2 mb-2 w-full" required>
                    <option value="Saving">Saving</option>
                    <option value="Current">Current</option>
                </select>
                <label>Amount:</label>
                <input type="number" name="withdraw_amount" step="0.01" class="border p-2 mb-2 w-full" required>
                <button type="submit" name="withdraw" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Withdraw
                </button>
            </form>
        <?php endif; ?>
        <?php if (isset($_GET['action']) && $_GET['action'] == 'transfer'): ?>
            <form action="cashier_dashboard.php" method="POST" class="mt-8 p-4 bg-gray-100 rounded shadow">
                <h3 class="text-xl font-bold mb-4">Transfer Funds</h3>
                <label for="transfer_sender_account_number">Sender Account Number:</label>
                <input type="text" name="transfer_sender_account_number" id="transfer_sender_account_number" class="border p-2 mb-2 w-full" required>

                <label for="sender_account_type">Sender Account Type:</label>
                <select name="sender_account_type" id="sender_account_type" class="border p-2 mb-2 w-full" required>
                    <option value="Saving">Saving</option>
                    <option value="Current">Current</option>
                </select>

                <label for="transfer_receiver_account_number">Receiver Account Number:</label>
                <input type="text" name="transfer_receiver_account_number" id="transfer_receiver_account_number" class="border p-2 mb-2 w-full" required>

                <label for="transfer_amount">Amount:</label>
                <input type="number" name="transfer_amount" id="transfer_amount" step="0.01" class="border p-2 mb-2 w-full" required>

                <button type="submit" name="transfer" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                    Transfer
                </button>
            </form>
        <?php endif; ?>



        <!-- Similar forms for Withdraw and Transfer based on `$_GET['action']` -->
    </main>
    <footer class="bg-blue-600 p-4 text-white text-center mt-8">
        © 2025 Your Company. All rights reserved.
    </footer>
</body>

</html>