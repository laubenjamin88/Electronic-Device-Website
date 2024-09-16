
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
    $quantity = $_POST['quantity'];

    if ($quantity < 1) {
        $quantity = 1;
    }

    $query = "UPDATE orders_in_cart SET quantity = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $quantity, $item_id);

    if ($stmt->execute()) {
        echo "Quantity updated successfully!";
    } else {
        echo "Error updating quantity: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
