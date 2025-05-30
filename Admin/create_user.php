<?php
session_start();
include('../db.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = strtoupper($_POST['name']); // Convert name to uppercase
    $mobile_number = $_POST['mobile_number'];
    $email = $_POST['email'];
    
    $DOB = $_POST['DOB'];
    $pan_card_number = $_POST['pan_card_number'];
    $aadhaar_number = $_POST['aadhaar_number'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $pin_code = $_POST['pin_code'];
    $account_type = $_POST['account_type']; // Account type (Saving/Current)
    $ifsc_code = "IGHJK3002LK";

    // Validate inputs
    if (!preg_match('/^\d{10}$/', $mobile_number)) {
        echo "<script>alert('Invalid Mobile Number. It must be 10 digits.'); window.history.back();</script>";
        exit();
    }
    if (!preg_match('/^[A-Z0-9]{11}$/', $ifsc_code)) {
        echo "<script>alert('Invalid IFSC Code. It must be 11 characters.'); window.history.back();</script>";
        exit();
    }
    if (!preg_match('/^[A-Z0-9]{11}$/', $pan_card_number)) {
        echo "<script>alert('Invalid PAN Card Number. It must be 10 characters.'); window.history.back();</script>";
        exit();
    }
    if (!preg_match('/^\d{12}$/', $aadhaar_number)) {
        echo "<script>alert('Invalid Aadhaar Number. It must be 12 digits.'); window.history.back();</script>";
        exit();
    }

    // Generate a secure password
    $last_four_mobile = substr($mobile_number, -4);
    $dob_formatted = date("dmY", strtotime($DOB)); // Format DOB as DDMMYYYY
    $generated_password = $last_four_mobile . "@" . $dob_formatted;
    $password = password_hash($generated_password, PASSWORD_BCRYPT);
    

    // Insert new user into the Users table
    $sql = "INSERT INTO Users (Name, MobileNumber, Email, IFSCCode, PANCardNumber, AadhaarNumber, Address, City, PinCode, Password, DOB)
            VALUES ('$name', '$mobile_number', '$email', '$ifsc_code', '$pan_card_number', '$aadhaar_number', '$address', '$city', '$pin_code', '$password', '$DOB')";
    
    if ($conn->query($sql) === TRUE) {
        $user_id = $conn->insert_id;

        // Generate a unique 12-digit Account Number with first 6 digits as '602323'
        do {
            $account_number = '602323' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $check_sql = "SELECT AccountNumber FROM Accounts WHERE AccountNumber = '$account_number'";
            $result = $conn->query($check_sql);
        } while ($result->num_rows > 0);

        // Create an associated account with balance = 0, AccountNumber, and account type
        $sql = "INSERT INTO Accounts (UserID, AccountNumber, Balance, Acc_type) 
                VALUES ($user_id, '$account_number', 0.00, '$account_type')";
        if ($conn->query($sql) === TRUE) {
            echo "<script>
                    alert('User account created successfully! Account Number: $account_number. Default Password: $generated_password');
                    window.location.href = 'admin_dashboard.php';
                  </script>";
        } else {
            echo "<script>alert('Error creating account: " . addslashes($conn->error) . "');</script>";
        }
    } else {
        echo "<script>alert('Error: " . addslashes($conn->error) . "'); window.history.back();</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-lg rounded-lg w-full max-w-2xl p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6 text-center">Create User Account</h1>
        <form method="POST" action="create_user.php" class="space-y-4">
            <div>
                <label for="name" class="block text-gray-700 font-medium mb-2">Name (Uppercase):</label>
                <input type="text" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
            </div>

            <div>
                <label for="mobile_number" class="block text-gray-700 font-medium mb-2">Mobile Number (10 Digits):</label>
                <input type="text" name="mobile_number" maxlength="10" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
            </div>

            <div>
                <label for="email" class="block text-gray-700 font-medium mb-2">Email:</label>
                <input type="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
            </div>

            <div>
                <label for="DOB" class="block text-gray-700 font-medium mb-2">Date OF Birth:</label>
                <input type="date" name="DOB" maxlength="6" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
            </div>

            <div>
                <label for="pan_card_number" class="block text-gray-700 font-medium mb-2">PAN Card Number:</label>
                <input type="text" name="pan_card_number" maxlength="11" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
            </div>

            <div>
                <label for="aadhaar_number" class="block text-gray-700 font-medium mb-2">Aadhaar Number:</label>
                <input type="text" name="aadhaar_number" maxlength="12" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
            </div>

            <div>
                <label for="address" class="block text-gray-700 font-medium mb-2">Address:</label>
                <input type="text" name="address" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
            </div>

            <div>
                <label for="city" class="block text-gray-700 font-medium mb-2">City:</label>
                <input type="text" name="city" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
            </div>

            <div>
                <label for="pin_code" class="block text-gray-700 font-medium mb-2">Pin Code:</label>
                <input type="text" name="pin_code" maxlength="6" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
            </div>

            <div>
                <label for="account_type" class="block text-gray-700 font-medium mb-2">Account Type:</label>
                <select name="account_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                    <option value="Saving">Saving</option>
                    <option value="Current">Current</option>
                </select>
            </div>

            <div>
                <button type="submit" class="w-full bg-blue-500 text-white font-medium py-2 px-4 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Create User</button>
            </div>
        </form>

        <p class="text-center text-gray-600 mt-6">
            <a href="admin_dashboard.php" class="text-blue-500 hover:underline">Back to Dashboard</a>
        </p>
    </div>
</body>

</html>