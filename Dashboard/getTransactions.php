<?php
session_start();  // Start the session to access session data
header('Content-Type: application/json');  // Set the response format to JSON

include('../db.php');  // Include database connection file

// Check if the user is logged in by checking if the session contains the 'user_id'
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];  // Retrieve the user ID from session

    // SQL query to fetch the last 5 transactions for the logged-in user
    $sql = "SELECT t.TransactionID, t.Amount, t.TransactionType, t.Timestamp, a.AccountNumber, t.RecipientAccountID
            FROM Transactions t
            JOIN Accounts a ON a.AccountID = t.AccountID
            WHERE a.UserID = ?
            ORDER BY t.Timestamp DESC
            LIMIT 5";

    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);  // Bind the user_id as an integer
        $stmt->execute();
        
        // Fetch the results
        $result = $stmt->get_result();
        
        // Store transactions in an array
        $transactions = array();
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }

        // Return results as JSON
        echo json_encode($transactions);
        
        // Close the statement
        $stmt->close();
    } else {
        echo json_encode(["error" => "Unable to prepare query"]);
    }
} else {
    echo json_encode(["error" => "User not logged in"]);
}

// Close the connection
$conn->close();
?>
