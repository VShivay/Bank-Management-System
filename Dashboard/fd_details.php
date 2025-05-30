<?php
session_start();
include '../db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$fdID = isset($_GET['fdid']) ? $_GET['fdid'] : null;
if (!$fdID) {
    echo "Invalid FD ID.";
    exit();
}

// Query to fetch full FD details for the given FDID
$query = "SELECT FDID, Amount, InterestRate, TenureMonths, MaturityAmount, StartDate, MaturityDate, Status 
          FROM FixedDeposits 
          WHERE FDID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $fdID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $fdDetails = $result->fetch_assoc();
} else {
    echo "FD details not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fixed Deposit Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-3xl bg-white shadow-lg rounded-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Fixed Deposit Details</h1>

        <table class="w-full text-left border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-50">
                    <th class="border border-gray-300 px-4 py-2 text-gray-700 font-medium">Field</th>
                    <th class="border border-gray-300 px-4 py-2 text-gray-700 font-medium">Details</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border border-gray-300 px-4 py-2">FD ID</td>
                    <td class="border border-gray-300 px-4 py-2"><?php echo $fdDetails['FDID']; ?></td>
                </tr>
                <tr class="bg-gray-50">
                    <td class="border border-gray-300 px-4 py-2">Amount</td>
                    <td class="border border-gray-300 px-4 py-2">$<?php echo number_format($fdDetails['Amount'], 2); ?></td>
                </tr>
                <tr>
                    <td class="border border-gray-300 px-4 py-2">Interest Rate (%)</td>
                    <td class="border border-gray-300 px-4 py-2"><?php echo number_format($fdDetails['InterestRate'], 2); ?>%</td>
                </tr>
                <tr class="bg-gray-50">
                    <td class="border border-gray-300 px-4 py-2">Tenure (Months)</td>
                    <td class="border border-gray-300 px-4 py-2"><?php echo $fdDetails['TenureMonths']; ?></td>
                </tr>
                <tr>
                    <td class="border border-gray-300 px-4 py-2">Maturity Amount</td>
                    <td class="border border-gray-300 px-4 py-2">$<?php echo number_format($fdDetails['MaturityAmount'], 2); ?></td>
                </tr>
                <tr class="bg-gray-50">
                    <td class="border border-gray-300 px-4 py-2">Start Date</td>
                    <td class="border border-gray-300 px-4 py-2"><?php echo $fdDetails['StartDate']; ?></td>
                </tr>
                <tr>
                    <td class="border border-gray-300 px-4 py-2">Maturity Date</td>
                    <td class="border border-gray-300 px-4 py-2"><?php echo $fdDetails['MaturityDate']; ?></td>
                </tr>
                <tr class="bg-gray-50">
                    <td class="border border-gray-300 px-4 py-2">Status</td>
                    <td class="border border-gray-300 px-4 py-2"><?php echo $fdDetails['Status']; ?></td>
                </tr>
            </tbody>
        </table>

        <div class="mt-6 text-center">
            <a href="user_dashboard.php" class="text-indigo-600 hover:underline">Back to Dashboard</a>
        </div>
    </div>
</body>

</html>