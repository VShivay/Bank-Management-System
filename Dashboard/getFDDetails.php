<?php
session_start();  // Start the session to access session data
header('Content-Type: application/json');  // Set response format to JSON

include('../db.php');  // Include database connection

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $userID = $_SESSION['user_id'];  // Get user ID from session

    // Query to fetch Fixed Deposit details
    $query = "SELECT FDID, Amount, InterestRate, TenureMonths, MaturityAmount, StartDate, MaturityDate, Status 
              FROM FixedDeposits 
              WHERE UserID = ?";

    // Prepare and execute the query
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result();

        $fdDetails = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $fdDetails[] = $row;
            }
        }

        // Return FD details as JSON
        echo json_encode($fdDetails);

        // Close statement
        $stmt->close();
    } else {
        echo json_encode(["error" => "Unable to prepare query"]);
    }
} else {
    echo json_encode(["error" => "User not logged in"]);
}

// Close database connection
$conn->close();
?>
