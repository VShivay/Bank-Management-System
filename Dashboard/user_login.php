<?php
session_start();
include('../db.php'); // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mobileNumber = $_POST['mobileNumber'];
    $password = $_POST['password'];

    // Fetch the hashed password from the database for the given mobile number
    $sql = "SELECT * FROM Users WHERE MobileNumber = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $mobileNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the entered password against the hashed password in the database
        if (password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['UserID'];
            header("Location: user_dashboard.php");
            exit;
        } else {
            $errorMessage = "Invalid credentials!";
        }
    } else {
        $errorMessage = "Invalid credentials!";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-blue-100 to-purple-200 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-lg rounded-xl w-full max-w-md p-8">
        <h1 class="text-3xl font-extrabold text-center text-purple-700 mb-8">Welcome Back!</h1>

        <?php if (isset($errorMessage)): ?>
            <p class="text-red-600 text-center font-semibold mb-4"><?php echo $errorMessage; ?></p>
        <?php endif; ?>

        <form method="POST" action="user_login.php" class="space-y-6">
            <!-- Mobile Number -->
            <div>
                <label for="mobileNumber" class="block text-sm font-semibold text-gray-700">Mobile Number</label>
                <input
                    type="text"
                    name="mobileNumber"
                    id="mobileNumber"
                    maxlength="10"
                    placeholder="Enter your mobile number"
                    required
                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500 text-gray-800">
            </div>
            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="Enter your password"
                    required
                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500 text-gray-800">
            </div>
            <!-- Submit Button -->
            <button
                type="submit"
                class="w-full bg-purple-600 text-white py-3 px-6 rounded-lg text-lg font-medium hover:bg-purple-700 focus:outline-none focus:ring-4 focus:ring-purple-300">
                Login
            </button>
        </form>
        <!-- Link to Home Page -->
        <p class="text-center text-gray-600 text-sm mt-6">
            <a href="User.html" class="text-purple-500 font-semibold hover:underline">Go to Home Page</a>
        </p>
        <p class="text-center text-gray-600 text-sm mt-6">
            <a href="forget_account_password.php" class="text-purple-500 font-semibold hover:underline">Forget Password</a>
        </p>
        <!-- Footer -->
        <p class="text-center text-gray-500 text-xs mt-4">
            © 2025 Your Company. All Rights Reserved.
        </p>
    </div>
</body>

</html>