<?php
session_start();
include '../db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$userID = $_SESSION['user_id'];

// Query to fetch credit card data (limit and outstanding)
$query = "SELECT CardLimit, CurrentOutstanding FROM CreditCards WHERE UserID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

$outstanding = 0;
$limit = 0;

if ($result->num_rows > 0) {
    $creditCard = $result->fetch_assoc();
    $outstanding = $creditCard['CurrentOutstanding'];
    $limit = $creditCard['CardLimit'];
}

$remaining = $limit - $outstanding;

echo json_encode([
    'outstanding' => $outstanding,
    'remaining' => $remaining,
    'limit' => $limit
]);
