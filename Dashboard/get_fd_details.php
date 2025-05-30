<?php
session_start();
include '../db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$userID = $_SESSION['user_id'];

// Query to fetch Fixed Deposit (FD) details for the logged-in user
$query = "SELECT FDID, Amount, InterestRate, TenureMonths, MaturityAmount, StartDate, MaturityDate, Status 
          FROM FixedDeposits 
          WHERE UserID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

$fdDetails = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $fdDetails[] = $row;
    }
}

echo json_encode($fdDetails);
