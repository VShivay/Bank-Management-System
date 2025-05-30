<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: Admin/admin_login.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col items-center">
    <!-- Dashboard Header -->
    <header class="w-full bg-blue-600 text-white py-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center px-6">
            <h1 class="text-2xl font-bold">Admin Dashboard</h1>
            <a href="logout.php" class="text-white font-medium hover:text-blue-200">Logout</a>
        </div>
    </header>

    <!-- Dashboard Content -->
    <main class="container mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg w-full max-w-4xl">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Quick Actions</h2>
        <ul class="space-y-4">
            <li>
                <a
                    href="create_user.php"
                    class="block bg-blue-500 text-white py-3 px-6 rounded-lg text-lg font-medium hover:bg-blue-600 focus:ring-4 focus:ring-blue-300">
                    Create User Account
                </a>
            </li>
            <li>
                <a
                    href="create_cashier.php"
                    class="block bg-green-500 text-white py-3 px-6 rounded-lg text-lg font-medium hover:bg-green-600 focus:ring-4 focus:ring-green-300">
                    Create Cashier Account
                </a>
            </li>
            <li>
                <a
                    href="search.php"
                    class="block bg-yellow-500 text-white py-3 px-6 rounded-lg text-lg font-medium hover:bg-yellow-600 focus:ring-4 focus:ring-yellow-300">
                    View All Accounts
                </a>
            </li>
            <li>
                <a
                    href="update_customer_details.php"
                    class="block bg-blue-500 text-white py-3 px-6 rounded-lg text-lg font-medium hover:bg-blue-600 focus:ring-4 focus:ring-blue-300">
                    Update Customer Details
                </a>
            </li>
            <li>
                <a
                    href="request.php"
                    class="block bg-purple-500 text-white py-3 px-6 rounded-lg text-lg font-medium hover:bg-purple-600 focus:ring-4 focus:ring-purple-300">
                    Credit Card Requests
                </a>
            </li>
        </ul>
    </main>

    <!-- Footer -->
    <footer class="w-full mt-10 bg-gray-800 text-white py-4 text-center">
        <p>&copy; 2025 Your Company. All Rights Reserved.</p>
    </footer>
</body>

</html>