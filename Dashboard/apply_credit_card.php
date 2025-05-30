<?php
session_start();
include('../db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to apply for a credit card.";
    exit();
}

$user_id = $_SESSION['user_id']; // Logged-in user ID

// Check if the user already has a credit card application
$query = "SELECT Status FROM CreditCards WHERE UserID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $status = $row['Status'];

    if ($status === 'Pending') {
        echo "Your credit card application is still under review.";
        exit();
    } elseif ($status === 'Approved') {
        echo "You already have an approved credit card.";
        exit();
    }
    // If rejected, allow reapplication
}

// Process the credit card application
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Generate card details
    $card_number = generateCardNumber(); // Generate a 12-digit card number
    $expiry_date = date('Y-m-d', strtotime('+3 years')); // Card expires in 3 years
    $cvv = rand(100, 999); // Generate a random 3-digit CVV

    // Insert or update the application in the CreditCards table
    $insert_query = "REPLACE INTO CreditCards (UserID, CardNumber, ExpiryDate, CVV, Status) 
                     VALUES (?, ?, ?, ?, 'Pending')";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("isss", $user_id, $card_number, $expiry_date, $cvv);

    if ($stmt->execute()) {
        echo "Your credit card application has been submitted.";
    } else {
        echo "Error: Unable to process your request. Please try again.";
    }
}

// Function to generate a random 12-digit card number
function generateCardNumber() {
    return str_pad(rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
}
?>
