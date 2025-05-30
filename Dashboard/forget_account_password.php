<?php
// Database connection
$servername = "localhost"; // replace with your server
$username = "root";        // replace with your database username
$password = "";            // replace with your database password
$dbname = "BankSystem"; // replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session to store user ID after successful verification
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Step 1: Check if it's the verification form (user entering account details)
    if (isset($_POST['accountNumber']) && isset($_POST['mobileNumber']) && isset($_POST['dob']) && isset($_POST['aadhaarNumber'])) {
        // Collect and sanitize input values for verification
        $accountNumber = mysqli_real_escape_string($conn, $_POST['accountNumber']);
        $mobileNumber = mysqli_real_escape_string($conn, $_POST['mobileNumber']);
        $dob = mysqli_real_escape_string($conn, $_POST['dob']);
        $aadhaarNumber = mysqli_real_escape_string($conn, $_POST['aadhaarNumber']);

        // Prepare SQL query to find matching user data
        $sql = "SELECT u.UserID, u.Name, a.AccountID 
                FROM Users u 
                JOIN Accounts a ON u.UserID = a.UserID 
                WHERE a.AccountNumber = '$accountNumber' 
                AND u.MobileNumber = '$mobileNumber' 
                AND u.DOB = '$dob' 
                AND u.AadhaarNumber = '$aadhaarNumber'";

        // Execute the query
        $result = $conn->query($sql);

        // Check if any user matches the given data
        if ($result->num_rows > 0) {
            // User exists, proceed to show the reset password form
            $user = $result->fetch_assoc();
            $_SESSION['userID'] = $user['UserID'];  // Store UserID in session for password update
            echo "<h2>Reset Your Password</h2>";
            echo '<form action="forget_account_password.php" method="POST" onsubmit="return validatePassword()">
        <label for="newPassword">New Password:</label>
        <input type="password" id="newPassword" name="newPassword" required><br><br>
        <label for="confirmPassword">Confirm Password:</label>
        <input type="password" id="confirmPassword" name="confirmPassword" required><br><br>
        <input type="submit" value="Submit">
        <input type="hidden" name="resetPassword" value="1">
    </form>
    <script>
        function validatePassword() {
            const password = document.getElementById("newPassword").value;
            const confirmPassword = document.getElementById("confirmPassword").value;
            const pattern = /^(?=.*[A-Z])(?=.*\\d)(?=.*[\\W_]).{8,}$/;

            if (password !== confirmPassword) {
                alert("Passwords do not match.");
                return false;
            }
            if (!pattern.test(password)) {
                alert("Password must be at least 8 characters long and include at least one uppercase letter, one number, and one special character.");
                return false;
            }
            return true;
        }
    </script>';
        } else {
            echo "No matching account found. Please check your details and try again.";
        }
    }

    // Step 2: Check if the form is for resetting the password (when user has verified their details)
    elseif (isset($_POST['resetPassword']) && $_POST['resetPassword'] == 1) {
        // Collect new password and confirm password
        $newPassword = mysqli_real_escape_string($conn, $_POST['newPassword']);
        $confirmPassword = mysqli_real_escape_string($conn, $_POST['confirmPassword']);

        // Check if the new password and confirm password match
        if ($newPassword === $confirmPassword) {
            // Password must contain at least one uppercase, one number, one special character, and be at least 8 characters long
            if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $newPassword)) {
                echo "Password must be at least 8 characters long and include at least one uppercase letter, one number, and one special character.";
            } else {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $userID = $_SESSION['userID'];
                $updateSql = "UPDATE Users SET Password = '$hashedPassword' WHERE UserID = '$userID'";
                if ($conn->query($updateSql) === TRUE) {
                    echo "Your password has been reset successfully.";
                } else {
                    echo "Error updating password: " . $conn->error;
                }
            }
        } else {
            echo "Passwords do not match. Please try again.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forget Password</title>
</head>

<body>
    <h2>Forget Account Password</h2>
    <form action="forget_account_password.php" method="POST">
        <label for="accountNumber">Account Number:</label>
        <input type="text" id="accountNumber" name="accountNumber" required><br><br>

        <label for="mobileNumber">Mobile Number:</label>
        <input type="text" id="mobileNumber" name="mobileNumber" required><br><br>

        <label for="dob">Date of Birth (YYYY-MM-DD):</label>
        <input type="date" id="dob" name="dob" required><br><br>

        <label for="aadhaarNumber">Aadhaar Number:</label>
        <input type="text" id="aadhaarNumber" name="aadhaarNumber" required><br><br>

        <input type="submit" value="Verify">
    </form>
</body>

</html>