<?php
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "BankSystem";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user ID from session
$userId = $_SESSION['user_id'];

// Fetch account and user details
$sql = "
    SELECT 
        u.Name, u.Email, u.MobileNumber,u.PANCardNumber, u.AadhaarNumber,u.Address,u.City,u.PinCode, 
        a.AccountNumber, a.Balance 
    FROM 
        Users u 
    JOIN 
        Accounts a ON u.UserID = a.UserID 
    WHERE 
        u.UserID = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "No account details found.";
    $conn->close();
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Details</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-blue-100 via-purple-200 to-blue-300 min-h-screen flex flex-col">
    <div class="container mx-auto px-4 py-6">
        <header class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-transparent bg-clip-text">
                Account Details
            </h1>
        </header>

        <main class="bg-white shadow-lg rounded-lg p-8 max-w-3xl mx-auto">
            <p class="text-lg text-gray-700 mb-4">
                <strong>Name:</strong> <span class="text-gray-900"><?php echo htmlspecialchars($user['Name']); ?></span>
            </p>
            <p class="text-lg text-gray-700 mb-4">
                <strong>Email:</strong> <span class="text-gray-900"><?php echo htmlspecialchars($user['Email']); ?></span>
            </p>
            <p class="text-lg text-gray-700 mb-4">
                <strong>Mobile Number:</strong> <span class="text-gray-900"><?php echo htmlspecialchars($user['MobileNumber']); ?></span>
            </p>
            <p class="text-lg text-gray-700 mb-4">
                <strong>Account Number:</strong> <span class="text-gray-900"><?php echo htmlspecialchars($user['AccountNumber']); ?></span>
            </p>
            <p class="text-lg text-gray-700 mb-4">
                <strong>PAN Card Number:</strong> <span class="text-gray-900"><?php echo htmlspecialchars($user['PANCardNumber']); ?></span>
            </p>
            <p class="text-lg text-gray-700 mb-4">
                <strong>Aadhaar Number:</strong> <span class="text-gray-900"><?php echo htmlspecialchars($user['AadhaarNumber']); ?></span>
            </p>
            <p class="text-lg text-gray-700 mb-4">
                <strong>Address:</strong> <span class="text-gray-900"><?php echo htmlspecialchars($user['Address']); ?></span>
            </p>
            <p class="text-lg text-gray-700 mb-4">
                <strong>City:</strong> <span class="text-gray-900"><?php echo htmlspecialchars($user['City']); ?></span>
            </p>
            <p class="text-lg text-gray-700 mb-4">
                <strong>Pincode:</strong> <span class="text-gray-900"><?php echo htmlspecialchars($user['PinCode']); ?></span>
            </p>
            <p class="text-lg text-gray-700 mb-4">
                <strong>Balance:</strong> <span class="text-gray-900">$<?php echo number_format($user['Balance'], 2); ?></span>
            </p>
        </main>

        <nav class="mt-6 text-center">
            <ul class="flex justify-center space-x-6">
                <li>
                    <a href="user_dashboard.php" class="text-lg font-medium text-gray-700 bg-gradient-to-r from-blue-400 to-indigo-600 text-white px-4 py-2 rounded-lg hover:from-blue-500 hover:to-indigo-700 transition-all duration-300">
                        Back to Dashboard
                    </a>
                </li>
                <li>
                    <a href="logout1.php" class="text-lg font-medium text-gray-700 bg-gradient-to-r from-red-400 to-red-600 text-white px-4 py-2 rounded-lg hover:from-red-500 hover:to-red-700 transition-all duration-300">
                        Logout
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</body>

</html>