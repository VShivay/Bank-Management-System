<?php
$servername = "localhost"; // Update with your database host
$username = "root"; // Update with your database username
$password = ""; // Update with your database password
$dbname = "BankSystem"; // Update with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Capture the search parameters
$name = isset($_GET['name']) ? $_GET['name'] : '';
$mobileNumber = isset($_GET['mobileNumber']) ? $_GET['mobileNumber'] : '';
$city = isset($_GET['city']) ? $_GET['city'] : '';
$address = isset($_GET['address']) ? $_GET['address'] : '';
$accountNumber = isset($_GET['accountNumber']) ? $_GET['accountNumber'] : '';

// Construct the query
$query = "SELECT u.UserID, u.Name, u.MobileNumber, u.Email, u.Address, u.City, u.PINCode,u.DOB, 
          a.AccountNumber, a.Acc_type, a.Balance 
          FROM Users u 
          JOIN Accounts a ON u.UserID = a.UserID 
          WHERE 1=1";

// Add conditions based on the search parameters
if ($accountNumber) {
    $query .= " AND a.AccountNumber LIKE '%" . $conn->real_escape_string($accountNumber) . "%'";
}
if ($mobileNumber) {
    $query .= " AND u.MobileNumber LIKE '%" . $conn->real_escape_string($mobileNumber) . "%'";
}
if ($city) {
    $query .= " AND u.City LIKE '%" . $conn->real_escape_string($city) . "%'";
}
if ($address) {
    $query .= " AND u.Address LIKE '%" . $conn->real_escape_string($address) . "%'";
}
if ($name) {
    $query .= " AND u.Name LIKE '%" . $conn->real_escape_string($name) . "%'";
}

// Execute the query
$result = $conn->query($query);

// Check for results
if ($result->num_rows > 0) {
    $users = [];
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode($users);
} else {
    echo json_encode([]);
}

// Close connection
$conn->close();
?>
