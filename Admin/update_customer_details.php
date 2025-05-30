<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
}
// Database connection configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "BankSystem";

// Create a connection
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$existingDetails = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['fetchDetails'])) {
        // Fetch existing details based on Account Number
        $accountNumber = $_POST['accountNumber'];
        $sql = "SELECT u.MobileNumber, u.Email, u.IFSCCode, u.PANCardNumber, u.AadhaarNumber, u.Address, u.City, u.PinCode
                FROM Users u
                INNER JOIN Accounts a ON u.UserID = a.UserID
                WHERE a.AccountNumber = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $accountNumber);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $existingDetails = $result->fetch_assoc();
        } else {
            echo "<script>
                    alert('No customer found with the provided Account Number.');
                    window.history.back();
                  </script>";
            exit;
        }
        $stmt->close();
    } elseif (isset($_POST['updateDetails'])) {
        // Update customer details
        $accountNumber = $_POST['accountNumber'];
        $mobileNumber = $_POST['mobileNumber'];
        $email = $_POST['email'];
        $ifscCode = $_POST['ifscCode'];
        $panCardNumber = $_POST['panCardNumber'];
        $aadhaarNumber = $_POST['aadhaarNumber'];
        $address = $_POST['address'];
        $city = $_POST['city'];
        $pinCode = $_POST['pinCode'];

        // Input validation with error and redirect
        if (!preg_match('/^\d{10}$/', $mobileNumber)) {
            echo "<script>
                    alert('Invalid Mobile Number. It must be 10 digits.');
                    window.history.back();
                  </script>";
            exit;
        }
        if (!preg_match('/^\d{12}$/', $aadhaarNumber)) {
            echo "<script>
                    alert('Invalid Aadhaar Number. It must be 12 digits.');
                    window.history.back();
                  </script>";
            exit;
        }
        if (!preg_match('/^[A-Z0-9]{11}$/', $ifscCode)) {
            echo "<script>
                    alert('Invalid IFSC Code. It must be 11 characters.');
                    window.history.back();
                  </script>";
            exit;
        }
        if (!preg_match('/^\d{6}$/', $pinCode)) {
            echo "<script>
                    alert('Invalid Pin Code. It must be 6 digits.');
                    window.history.back();
                  </script>";
            exit;
        }
        if (!preg_match('/^[A-Z0-9]{11}$/', $panCardNumber)) {
            echo "<script>
                    alert('Invalid PAN Card Number. It must be 11 characters.');
                    window.history.back();
                  </script>";
            exit;
        }

        // Update query
        $sql = "UPDATE Users u
                INNER JOIN Accounts a ON u.UserID = a.UserID
                SET u.MobileNumber = ?, u.Email = ?, u.IFSCCode = ?, u.PANCardNumber = ?, u.AadhaarNumber = ?, u.Address = ?, u.City = ?, u.PinCode = ?
                WHERE a.AccountNumber = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssss", $mobileNumber, $email, $ifscCode, $panCardNumber, $aadhaarNumber, $address, $city, $pinCode, $accountNumber);

        if ($stmt->execute()) {
            echo "<script>alert('Customer details updated successfully.');</script>";
        } else {
            echo "<script>
                    alert('Error updating record: " . $stmt->error . "');
                    window.history.back();
                  </script>";
        }

        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Customer Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 flex items-center justify-center">
    <div class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-xl m-4">
        <h2 class="text-2xl font-bold text-blue-800 text-center mb-6">Update Customer Details</h2>

        <!-- Form to fetch existing details -->
        <form method="POST" action="" class="space-y-4">
            <div>
                <label for="accountNumber" class="block text-sm font-medium text-gray-700">Account Number</label>
                <input
                    type="text"
                    id="accountNumber"
                    name="accountNumber"
                    maxlength="12"
                    value="<?= htmlspecialchars($_POST['accountNumber'] ?? '') ?>"
                    required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-700">
            </div>
            <button
                type="submit"
                name="fetchDetails"
                class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-2 px-4 rounded-lg shadow-md hover:from-blue-700 hover:to-purple-700">
                Fetch Details
            </button>
            <div class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-2 px-4 rounded-lg shadow-md hover:from-blue-700 hover:to-purple-700 text-center">
                <a href="admin_dashboard.php">Back to Dashboard</a>
            </div>
        </form>


        <!-- Form to update details, pre-filled with existing details -->
        <?php if ($existingDetails): ?>
            <form method="POST" action="" class="mt-6 space-y-4">
                <input type="hidden" name="accountNumber" value="<?= htmlspecialchars($_POST['accountNumber'] ?? '') ?>">

                <div>
                    <label for="mobileNumber" class="block text-sm font-medium text-gray-700">Mobile Number</label>
                    <input
                        type="text"
                        id="mobileNumber"
                        name="mobileNumber"
                        maxlength="10"
                        value="<?= htmlspecialchars($existingDetails['MobileNumber']) ?>"
                        required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-700">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars($existingDetails['Email']) ?>"
                        required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-700">
                </div>

                <div>
                    <label for="ifscCode" class="block text-sm font-medium text-gray-700">IFSC Code</label>
                    <input
                        type="text"
                        id="ifscCode"
                        name="ifscCode"
                        maxlength="11"
                        value="<?= htmlspecialchars($existingDetails['IFSCCode']) ?>"
                        required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-700">
                </div>

                <div>
                    <label for="panCardNumber" class="block text-sm font-medium text-gray-700">PAN Card Number</label>
                    <input
                        type="text"
                        id="panCardNumber"
                        name="panCardNumber"
                        maxlength="11"
                        value="<?= htmlspecialchars($existingDetails['PANCardNumber']) ?>"
                        required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-700">
                </div>

                <div>
                    <label for="aadhaarNumber" class="block text-sm font-medium text-gray-700">Aadhaar Number</label>
                    <input
                        type="text"
                        id="aadhaarNumber"
                        name="aadhaarNumber"
                        maxlength="12"
                        value="<?= htmlspecialchars($existingDetails['AadhaarNumber']) ?>"
                        required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-700">
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                    <input
                        type="text"
                        id="address"
                        name="address"
                        value="<?= htmlspecialchars($existingDetails['Address']) ?>"
                        required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-700">
                </div>

                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                    <input
                        type="text"
                        id="city"
                        name="city"
                        value="<?= htmlspecialchars($existingDetails['City']) ?>"
                        required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-700">
                </div>

                <div>
                    <label for="pinCode" class="block text-sm font-medium text-gray-700">Pin Code</label>
                    <input
                        type="text"
                        id="pinCode"
                        name="pinCode"
                        maxlength="6"
                        value="<?= htmlspecialchars($existingDetails['PinCode']) ?>"
                        required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-700">
                </div>

                <button
                    type="submit"
                    name="updateDetails"
                    class="w-full bg-gradient-to-r from-green-600 to-teal-600 text-white py-2 px-4 rounded-lg shadow-md hover:from-green-700 hover:to-teal-700">
                    Update Details
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>