<?php
session_start();
include('../db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cashierID = $_POST['cashierID'];
    $password = $_POST['password'];

    // Use a prepared statement to fetch the cashier record
    $sql = "SELECT * FROM Cashiers WHERE CashierID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $cashierID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verify the hashed password
        if (password_verify($password, $row['Password'])) {
            $_SESSION['cashier_id'] = $cashierID;
            header("Location: cashier_dashboard.php");
            exit();
        } else {
            $errorMessage = "Invalid credentials!";
        }
    } else {
        $errorMessage = "Invalid credentials!";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-gray-100 to-blue-200 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-lg rounded-lg w-full max-w-md p-8">
        <h1 class="text-2xl font-bold text-center text-blue-700 mb-6">Cashier Login</h1>

        <?php if (isset($errorMessage)): ?>
            <p class="text-red-600 text-center mb-4 font-semibold"><?php echo $errorMessage; ?></p>
        <?php endif; ?>

        <form method="POST" action="cashier_login.php" class="space-y-4">
            <!-- Cashier ID -->
            <div>
                <label for="cashierID" class="block text-sm font-medium text-gray-700">Cashier ID</label>
                <input
                    type="text"
                    name="cashierID"
                    id="cashierID"
                    placeholder="Enter your Cashier ID"
                    maxlength="6"
                    required
                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="Enter your password"
                    required
                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <!-- Login Button -->
            <button
                type="submit"
                class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300">
                Login
            </button>
        </form>

        <p class="text-center text-gray-600 text-sm mt-6">
            <a href="../Admin/index.html" class="text-blue-500 font-semibold hover:underline">Go to Home Page</a>
        </p>
        <p class="text-center text-gray-400 text-xs mt-4">
            &copy; 2025 Your Company. All rights reserved.
        </p>
    </div>
</body>

</html>