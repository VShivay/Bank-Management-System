<?php
session_start();
include('../db_connection.php');

// Check if the user is an admin
if (!isset($_SESSION['admin_id'])) {
    echo "You must be an admin to perform this action.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $credit_card_id = $_POST['credit_card_id'];
    $action = $_POST['action'];

    if ($action === 'Approve') {
        if (!isset($_POST['card_limit']) || !is_numeric($_POST['card_limit'])) {
            echo "Card limit is required.";
            exit();
        }

        $card_limit = (float)$_POST['card_limit'];
        $status = 'Approved';

        $update_query = "UPDATE CreditCards SET Status = ?, CardLimit = ? WHERE CreditCardID = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sdi", $status, $card_limit, $credit_card_id);

        if ($stmt->execute()) {
            echo "Credit card request approved.";
        } else {
            echo "Error updating the request.";
        }
    } elseif ($action === 'Reject') {
        $status = 'Rejected';

        $update_query = "UPDATE CreditCards SET Status = ? WHERE CreditCardID = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $status, $credit_card_id);

        if ($stmt->execute()) {
            echo "Credit card request rejected.";
        } else {
            echo "Error updating the request.";
        }
    }
}
