<?php
session_start();
include('../db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

// Fetch the Account Number for the logged-in user
$user_id = $_SESSION['user_id'];

$sql = "SELECT AccountNumber FROM Accounts WHERE UserID = $user_id";
$account_result = $conn->query($sql);

if ($account_result->num_rows > 0) {
    $account_row = $account_result->fetch_assoc();
    $account_number = $account_row['AccountNumber'];
} else {
    echo "No account found for the user.";
    exit();
}

// Fetch transaction history for the account number
$sql = "SELECT t.TransactionID, t.Amount, t.TransactionType, t.Timestamp 
        FROM Transactions t 
        JOIN Accounts a ON t.AccountID = a.AccountID 
        WHERE a.AccountNumber = '$account_number' 
        ORDER BY t.Timestamp DESC";
$result = $conn->query($sql);

// Fetch all transactions for the account number
$transactions = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en" ng-app="transactionApp">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }

        h1 {
            text-align: center;
            color: #007bff;
        }

        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        table th,
        table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #007bff;
            color: #ffffff;
            text-transform: uppercase;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        p {
            text-align: center;
            margin-top: 20px;
        }

        a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }

        a:hover {
            color: #0056b3;
        }

        .filter-section {
            text-align: center;
            margin: 20px;
        }

        input,
        select {
            padding: 8px;
            margin: 5px;
            font-size: 14px;
        }

        .clear-filters-btn {
            padding: 8px 12px;
            background-color: #f44336;
            color: white;
            border: none;
            cursor: pointer;
        }

        .clear-filters-btn:hover {
            background-color: #d32f2f;
        }
    </style>
</head>

<body ng-controller="TransactionController">
    <h1>Transaction History</h1>

    <!-- Filter Section -->
    <div class="filter-section">
        <input type="date" ng-model="startDate" placeholder="Start Date" />
        <input type="date" ng-model="endDate" placeholder="End Date" />

        <select ng-model="transactionType" ng-options="type for type in transactionTypes">
            <option value="">Select Transaction Type</option>
        </select>

        <!-- Clear Filters Button -->
        <button class="clear-filters-btn" ng-click="clearFilters()">Clear Filters</button>
    </div>

    <!-- Transaction Table -->
    <table>
        <tr>
            <th>Transaction ID</th>
            <th>Amount</th>
            <th>Transaction Type</th>
            <th>Timestamp</th>
        </tr>
        <tr ng-repeat="transaction in transactions | dateFilter:startDate:endDate | typeFilter:transactionType">
            <td>{{ transaction.TransactionID }}</td>
            <td>₹{{ transaction.Amount | number:2 }}</td>
            <td>{{ transaction.TransactionType }}</td>
            <td>{{ transaction.Timestamp | date: 'dd-MMM-yyyy HH:mm:ss' }}</td>
        </tr>
        <tr ng-if="transactions.length === 0">
            <td colspan="4">No transactions found.</td>
        </tr>
    </table>

    <p><a href="user_dashboard.php">Back to Dashboard</a></p>

    <script>
        // AngularJS App and Controller
        var app = angular.module('transactionApp', []);
        
        app.controller('TransactionController', function ($scope) {
            // PHP-generated transactions array
            $scope.transactions = <?php echo json_encode($transactions); ?>;
            
            // Transaction types
            $scope.transactionTypes = ['Deposit', 'Withdrawal', 'Transfer', 'DebitCard', 'FD Creation', 'BillPay'];

            // Clear Filters function
            $scope.clearFilters = function () {
                $scope.startDate = null;
                $scope.endDate = null;
                $scope.transactionType = null;
            };
        });

        // Custom AngularJS Filter to filter transactions by date
        app.filter('dateFilter', function () {
            return function (transactions, startDate, endDate) {
                if (!startDate && !endDate) return transactions;

                var filteredTransactions = [];

                angular.forEach(transactions, function (transaction) {
                    var transactionDate = new Date(transaction.Timestamp);
                    var start = startDate ? new Date(startDate) : null;
                    var end = endDate ? new Date(endDate) : null;

                    if ((start && transactionDate < start) || (end && transactionDate > end)) {
                        return;
                    }

                    filteredTransactions.push(transaction);
                });

                return filteredTransactions;
            };
        });

        // Custom AngularJS Filter to filter transactions by type
        app.filter('typeFilter', function () {
            return function (transactions, transactionType) {
                if (!transactionType) return transactions;

                var filteredTransactions = [];

                angular.forEach(transactions, function (transaction) {
                    if (transaction.TransactionType === transactionType) {
                        filteredTransactions.push(transaction);
                    }
                });

                return filteredTransactions;
            };
        });
    </script>
</body>

</html>
