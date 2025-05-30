<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Credit Card Requests</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-gray-100 via-blue-100 to-gray-200 min-h-screen flex flex-col items-center">
    <div class="container mx-auto px-4 py-8">
        <header class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 bg-gradient-to-r from-blue-500 to-indigo-600 text-transparent bg-clip-text">
                Pending Credit Card Requests
            </h1>
        </header>

        <div class="grid gap-6 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
            <?php
            session_start();
            include('../db_connection.php');

            if (!isset($_SESSION['admin_id'])) {
                echo "<div class='text-center p-4 bg-red-100 text-red-800 rounded-lg'>You must be an admin to view this page.</div>";
                exit();
            }

            $query = "
                SELECT cc.CreditCardID, cc.UserID, cc.CardNumber, u.Name 
                FROM CreditCards cc
                JOIN Users u ON cc.UserID = u.UserID
                WHERE cc.Status = 'Pending'
            ";
            $result = $conn->query($query);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $user_id = $row['UserID'];
                    $credit_card_id = $row['CreditCardID'];
                    $card_number = $row['CardNumber'];
                    $user_name = $row['Name'];
            ?>
                    <div class="bg-white shadow-lg rounded-lg p-6">
                        <h3 class="text-xl font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($user_name); ?></h3>
                        <p class="text-gray-700"><strong>User ID:</strong> <?php echo htmlspecialchars($user_id); ?></p>
                        <p class="text-gray-700"><strong>Card Number:</strong> <?php echo htmlspecialchars($card_number); ?></p>
                        <p class="text-gray-700"><strong>Credit Card ID:</strong> <?php echo htmlspecialchars($credit_card_id); ?></p>

                        <form method="POST" action="approve_reject_credit_card.php" class="mt-4">
                            <input type="hidden" name="credit_card_id" value="<?php echo htmlspecialchars($credit_card_id); ?>">
                            <label for="card_limit" class="block text-sm font-medium text-gray-700">Set Card Limit:</label>
                            <input
                                type="number"
                                name="card_limit"
                                step="0.01"
                                required
                                class="mt-1 w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 p-2">
                            <div class="flex space-x-4 mt-4">
                                <input
                                    type="submit"
                                    name="action"
                                    value="Approve"
                                    class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-all">
                                <input
                                    type="submit"
                                    name="action"
                                    value="Reject"
                                    class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-all">
                            </div>
                        </form>
                    </div>
            <?php
                }
            } else {
                echo "<div class='text-center text-gray-700 bg-blue-100 rounded-lg p-4'>No pending credit card requests.</div>";
            }
            ?>
        </div>
    </div>
</body>

</html>