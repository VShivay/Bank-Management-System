<?php
// Database connection configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "banksystem";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $cashier_id = $_POST['cashier_id'];
    $name = strtoupper(trim($_POST['name']));
    $password = $_POST['password'];

    // Validate inputs
    $errors = [];

    if (!preg_match('/^\d{6}$/', $cashier_id)) {
        $errors[] = "Cashier ID must be exactly 6 digits.";
    }

    if (empty($name)) {
        $errors[] = "Name is required.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    // Check if Cashier ID already exists
    $check_query = "SELECT * FROM cashiers WHERE CashierID = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $cashier_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $errors[] = "Cashier ID already exists.";
    }

    if (empty($errors)) {
        // Insert cashier details into the database
        $insert_query = "INSERT INTO cashiers (CashierID, Name, Password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt->bind_param("sss", $cashier_id, $name, $hashed_password);

        if ($stmt->execute()) {
            echo "<script>
                    alert('Cashier account created successfully.');
                    window.location.href = 'admin_dashboard.php'; // Redirect after success
                  </script>";
        } else {
            echo "<script>
                    alert('Error: " . addslashes($stmt->error) . "');
                  </script>";
        }

        $stmt->close();
    } else {
        // Display errors
        foreach ($errors as $error) {
            echo "<script>
                    alert('" . addslashes($error) . "');
                  </script>";
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
    <title>Create Cashier Account</title>
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
        }

        h1 {
            text-align: center;
            color: #4CAF50;
            font-size: 28px;
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 16px;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            font-size: 18px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button[type="submit"]:hover {
            background-color: #45a049;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            text-decoration: none;
            color: #007bff;
            font-size: 16px;
        }

        .back-link a:hover {
            color: #0056b3;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .container {
                padding: 20px;
                width: 90%;
            }

            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create Cashier Account</h1>
        <form method="POST" action="">
            <label for="cashier_id">Cashier ID (6 digits):</label>
            <input type="text" id="cashier_id" name="cashier_id" maxlength="6" required>

            <label for="name">Name (Uppercase):</label>
            <input type="text" id="name" name="name" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Create Cashier</button>
        </form>

        <div class="back-link">
            <a href="admin_dashboard.php">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
