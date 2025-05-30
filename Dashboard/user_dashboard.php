<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}
// Include database connection file (adjust the file path as necessary)
include '../db_connection.php'; // Make sure you replace this with your actual database connection file
// Get the logged-in user ID
$userID = $_SESSION['user_id'];
// SQL query to fetch the user's name and debit card details
$query = "SELECT 
            u.Name AS UserName, 
            dc.DebitCardID, 
            dc.CardNumber, 
            dc.ExpiryDate, 
            dc.Status, 
            a.Balance
          FROM DebitCards dc
          JOIN Accounts a ON dc.AccountID = a.AccountID
          JOIN Users u ON a.UserID = u.UserID
          WHERE u.UserID = ?";

// Prepare and execute the query
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

// Check if the user has a debit card and display details
if ($result->num_rows > 0) {
    $debitCard = $result->fetch_assoc();
    $userName = $debitCard['UserName'];
    $maskedCardNumber = substr($debitCard['CardNumber'], 0, 4) . ' **** **** ' . substr($debitCard['CardNumber'], -4); // Mask card number
    $expiryDate = $debitCard['ExpiryDate'];
    $status = $debitCard['Status'];
    $accountBalance = $debitCard['Balance']; // Fetch account balance
} else {
    $userName = "User not found";
    $maskedCardNumber = "No debit card found";
    $expiryDate = "N/A";
    $status = "N/A";
    $accountBalance = 0.00; // Default to 0 if no account is found
}

?>
<!DOCTYPE html>
<html lang="en" ng-app="bankApp">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
    <style>
        .clickable {
            color: blue;
            text-decoration: underline;
            cursor: pointer;
            transition: color 0.3s;
        }

        .clickable:hover {
            color: darkblue;
        }

        /* Dropdown Animation */
        #user-menu {
            transition: opacity 0.3s ease-in-out;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen" ng-controller="BankController">
    <!-- Navigation Bar -->
    <nav class="bg-gray-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="relative flex h-16 items-center justify-between">
                <!-- Logo & Navigation Links -->
                <div class="flex flex-1 items-center justify-start">
                    <img class="h-8 w-auto" src="Bank.webp" alt="Your Company">
                    <div class="hidden sm:block sm:ml-6">
                        <div class="flex space-x-4">
                            <a href="transaction_history.php" class="bg-gray-900 text-white rounded-md px-3 py-2 text-sm font-medium">Transaction History</a>
                            <a href="transfer_funds.php" class="text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Transfer Funds</a>
                            <a href="view_credit_card_details.php" class="text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">View Card</a>
                            <a href="user_pay_outstanding.php" class="text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Pay Outstanding</a>
                            <a href="view_credit_card_history.php" class="text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">View Credit Card History</a>
                        </div>
                    </div>
                </div>

                <!-- Account Balance, Notifications, and User Dropdown -->
                <div class="flex items-center space-x-4">
                    <!-- Account Balance Display -->
                    <div class="hidden md:block">
                        <span class="text-white text-sm font-medium">Balance: $<?php echo number_format($accountBalance, 2); ?></span>
                    </div>

                    <!-- Notifications -->
                    <!-- Profile Dropdown -->
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 sm:static sm:inset-auto">
                        <button id="notifications" class="p-1 text-gray-400 hover:text-white">
                            <svg class="size-6" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>
                        </button>

                        <div class="relative ml-3">
                            <button id="user-menu-button" class="flex rounded-full bg-gray-800 text-sm focus:outline-none">
                                <img class="w-8 h-8 rounded-full" src="Bank.webp" alt="User Avatar">
                            </button>

                            <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 bg-white border rounded-md shadow-lg z-10">
                                <a href="view_account1.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your Profile</a>
                                <a href="F_D.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Apply for FD</a>
                                <a href="logout1.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Logout</a>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
    </nav>
    </nav>
    <div class="flex flex-col h-screen">
        <!-- Main Content -->
        <div class="max-w-6xl mx-auto mt-4 bg-white shadow-lg rounded-lg p-6 animate-fadeIn flex-1">
            <h1 class="text-xl font-bold">Welcome, <?php echo htmlspecialchars($userName); ?>!</h1>

            <!-- Top Sections: Account Balance, Debit Card Details & Fixed Deposits -->
            <div class="mb-8">
                <!-- Account Balance & Debit Card Details -->
                <div class="p-4 border rounded-lg bg-gradient-to-r from-purple-500 to-indigo-500 text-white shadow-lg relative overflow-hidden h-48 mb-6">
                    <div class="absolute top-4 left-4 text-xl font-semibold">Debit Card</div>
                    <div class="absolute top-4 right-4 text-xl font-semibold"> Pay</div>
                    <div class="mt-8 text-center">
                        <div class="text-2xl tracking-widest">•••• •••• •••• <?php echo substr($maskedCardNumber, -4); ?></div>
                    </div>
                    <div class="mt-6 flex justify-between text-sm">
                        <div>
                            <p class="opacity-80">VALID THRU</p>
                            <p class="font-semibold text-lg"><?php echo $expiryDate; ?></p>
                        </div>
                        <div class="text-right">
                            <p class="opacity-80">CARD HOLDER</p>
                            <p class="font-semibold text-lg"><?php echo $userName; ?></p>
                        </div>
                    </div>
                </div>
                <!-- Fixed Deposits -->
                <div class="p-4 border rounded-lg bg-gray-50 shadow-lg flex flex-col justify-center items-center hover:shadow-md transition-transform transform hover:scale-105">
                    <h3 class="text-lg font-semibold mb-4">Fixed Deposits</h3>
                    <table class="w-full mt-4 border-collapse table-auto">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border px-4 py-2">FD ID</th>
                                <th class="border px-4 py-2">Amount</th>
                                <th class="border px-4 py-2">Interest Rate (%)</th>
                                <th class="border px-4 py-2">Tenure (Months)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr ng-repeat="fd in fixedDeposits" class="hover:bg-gray-50 cursor-pointer" ng-click="viewFDDetails(fd)">
                                <td class="border px-4 py-2 clickable" id="fd-{{fd.FDID}}" ng-click="viewFDDetails(fd)" title="Click to view details">{{fd.FDID}}</td>
                                <td class="border px-4 py-2">{{fd.Amount | currency}}</td>
                                <td class="border px-4 py-2">{{fd.InterestRate}}</td>
                                <td class="border px-4 py-2">{{fd.TenureMonths}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Bottom Sections: Credit Card Usage Chart & Last 5 Transactions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Credit Card Usage -->
                <div class="p-4 border rounded-lg bg-gray-50 hover:shadow-md transition-transform transform hover:scale-105">
                    <h3 class="text-lg font-semibold mb-2 text-center">Credit Card Usage</h3>
                    <div class="flex justify-center">
                        <canvas id="creditCardChart" class="w-60 h-60"></canvas>
                    </div>
                    <div class="mt-4 text-center">
                        <p class="text-lg font-bold">Usage: <span id="usagePercentage" class="text-red-600">0.00%</span></p>
                        <p class="text-lg font-bold">Available Limit: <span id="availableLimit" class="text-green-600">$<?php echo number_format($availableLimit, 2); ?></span></p>
                        <p class="text-lg font-bold">Total Limit: <span id="totalLimit" class="text-blue-600">$<?php echo number_format($totalLimit, 2); ?></span></p>
                    </div>
                </div>
                <!-- Last 5 Transactions -->
                <div class="p-4 border rounded-lg bg-gray-50 flex flex-col justify-center items-center hover:shadow-md transition-transform transform hover:scale-105">
                    <h3 class="text-lg font-semibold mb-4">Last 5 Transactions</h3>
                    <div class="w-full overflow-auto">
                        <table class="w-full border-collapse table-auto">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border px-4 py-2">Transaction ID</th>
                                    <th class="border px-4 py-2">Amount</th>
                                    <th class="border px-4 py-2">Type</th>
                                    <th class="border px-4 py-2">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr ng-repeat="transaction in transactions" class="hover:bg-gray-50">
                                    <td class="border px-4 py-2">{{transaction.TransactionID}}</td>
                                    <td class="border px-4 py-2">{{formatAmount(transaction)}}</td>
                                    <td class="border px-4 py-2">{{transaction.TransactionType}}</td>
                                    <td class="border px-4 py-2">{{transaction.Timestamp | date:'yyyy-MM-dd HH:mm:ss'}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <script src="script1.js"></script>
    </div>
</body>

</html>