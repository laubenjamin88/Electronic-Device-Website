
<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pineappleusers";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['item_id'];

    $query = "DELETE FROM orders_in_cart WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $item_id);

    if ($stmt->execute()) {
        echo "Item removed successfully!";
    } else {
        echo "Error removing item: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
