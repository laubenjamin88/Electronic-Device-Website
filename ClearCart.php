<?php
session_start();
$response = array('status' => 'error', 'message' => 'Failed to clear cart');

if (isset($_SESSION['session_id'])) {
    $pineapple_id = $_SESSION['session_id'];

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "pineappleusers";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $query = "DELETE FROM orders_in_cart WHERE pineapple_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $pineapple_id);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Cart cleared successfully!';
    } else {
        $response['message'] = "Error clearing cart: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}

echo json_encode($response);
?>
