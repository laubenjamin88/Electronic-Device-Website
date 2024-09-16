
<?php

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pineappleUsers";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$pineapple_id = $_SESSION['session_id'];

// Function to format the product name for the image path
function formatProductName($name) {
    $parts = explode(' ', $name);
    return $parts[0]; // Return the base part of the name
}

// Fetch items from orders_in_cart
$query = "SELECT id, product_name, storage, color, price, quantity FROM orders_in_cart WHERE pineapple_id = ? ORDER BY id";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $pineapple_id);
$stmt->execute();
$result = $stmt->get_result();

// Initialize cart data array
$cartData = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $formattedProductName = formatProductName($row['product_name']);
        echo '<div class="cart-item" data-item-id="' . htmlspecialchars($row['id']) . '">';
        echo '<img src="../image/' . htmlspecialchars($formattedProductName) . '/' . htmlspecialchars($row['product_name']) . ' - 2.png" alt="Product Image">';
        echo '<div class="cart-item-details">';
        echo '<h3>' . htmlspecialchars($row['product_name']) . '</h3>';
        echo '<p>Storage: ' . htmlspecialchars($row['storage']) . '</p>';
        echo '<p>Color: ' . htmlspecialchars($row['color']) . '</p>';
        echo '<h3 class="price">Price: $' . htmlspecialchars($row['price']) . '</h3>';
        echo '</div>';
        echo '<div class="cart-controls">';
        echo '<div class="quantity-controls">';
        echo '<button class = "leftButton" onclick="changeQuantity(event, -1)">-</button>';
        echo '<input type="text" value="' . htmlspecialchars($row['quantity']) . '" readonly>';
        echo '<button class = "rightButton" onclick="changeQuantity(event, 1)">+</button>';
        echo '<a id= "deleteIcon" class="fa fa-trash" onclick="removeItem(event)"></a>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        // Store item in session cart data
        $cartData[] = [
            'id' => $row['id'],
            'product_name' => $row['product_name'],
            'storage' => $row['storage'],
            'color' => $row['color'],
            'price' => $row['price'],
            'quantity' => $row['quantity']
        ];
    }

    // Store cart data in session
    $_SESSION['cartData'] = $cartData;
} else {
    echo "Your cart is empty.";
}

$stmt->close();
$conn->close();
?>
