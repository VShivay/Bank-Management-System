<?php
session_start();
include('../db.php'); // Include your database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Trim the inputs to avoid leading/trailing spaces
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepare the query to prevent SQL injection
    $sql = "SELECT * FROM Admins WHERE Username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the query returns any rows
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        // Direct password comparison (no hashing)
        if ($password == $admin['Password']) {
            $_SESSION['admin_id'] = $admin['AdminID']; // Store admin ID in session
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $errorMessage = "Invalid credentials! Password mismatch.";
        }
    } else {
        $errorMessage = "Invalid credentials! Username not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-gray-100 to-blue-200 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-lg rounded-lg w-full max-w-md p-8">
        <h1 class="text-2xl font-bold text-center text-blue-700 mb-6">Admin Login</h1>

        <?php if (isset($errorMessage)): ?>
            <p class="text-red-600 text-center mb-4 font-semibold"><?php echo $errorMessage; ?></p>
        <?php endif; ?>

        <form method="POST" action="admin_login.php" class="space-y-4">
            <!-- Username -->
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input
                    type="text"
                    name="username"
                    id="username"
                    placeholder="Enter your username"
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
            <a href="index.html" class="text-blue-500 font-semibold hover:underline">Go to Home Page</a>
        </p>
        <p class="text-center text-gray-400 text-xs mt-4">
            &copy; 2025 Your Company. All rights reserved.
        </p>
    </div>
</body>

</html>