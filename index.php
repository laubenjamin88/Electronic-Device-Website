<?php
session_start();
// Check if the user is logged in by checking the session
if (!isset($_SESSION['session_id'])) {
    // If not logged in, redirect to the login page with a message
    header("Location: /pineApple/Login.php?message=login_required");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/Navigationbar.css">
    <link rel="stylesheet" href="../css/CartList.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="icon" href="../image/logo-image.png" type="image/png">
    <title>Cart - Pineapple</title>
</head>

<body>

    <?php include('../includes/navigationHeader.php'); ?>
    <div id="cart_wrapper">
        <h2>Your Cart</h2>
        <div id="cartItems">
            <?php include('FetchInCart.php'); ?>
        </div>

        <div class="total-price">
            Total Price: $<span id="totalPrice"></span>
        </div>

        <button class="btn-payment" onclick="storeCartDataAndNavigate()">Proceed to Payment</button>
    </div>

    <script>
        function changeQuantity(event, change) {
            const itemElement = event.target.closest('.cart-item');
            const itemId = itemElement.getAttribute('data-item-id');
            const quantityInput = itemElement.querySelector('input[type="text"]');
            let quantity = parseInt(quantityInput.value, 10);
            quantity += change;
            if (quantity < 1) quantity = 1;
            quantityInput.value = quantity;

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "updateQuantity.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if (xhr.status == 200) {
                    updateTotalPrice();
                }
            };
            xhr.send(`item_id=${itemId}&quantity=${quantity}`);
        }

        function removeItem(event) {
            const itemElement = event.target.closest('.cart-item');
            const itemId = itemElement.getAttribute('data-item-id');

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "removeItem.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if (xhr.status == 200) {
                    itemElement.remove();
                    updateTotalPrice();
                }
            };
            xhr.send(`item_id=${itemId}`);
        }

        function updateTotalPrice() {
            let totalPrice = 0;
            const cartItems = document.querySelectorAll('.cart-item');

            cartItems.forEach(item => {
                const priceElement = item.querySelector('.cart-item-details h3.price');
                if (priceElement) {
                    const priceText = priceElement.textContent.replace('Price: $', '');
                    const price = parseFloat(priceText);
                    if (isNaN(price)) return;

                    const quantity = parseInt(item.querySelector('input[type="text"]').value, 10);
                    if (isNaN(quantity) || quantity < 1) return;

                    totalPrice += price * quantity;
                }
            });

            document.getElementById('totalPrice').textContent = totalPrice.toFixed(2);
        }

        function storeCartDataAndNavigate() {
            const cartItems = [];
            document.querySelectorAll('.cart-item').forEach(item => {
                cartItems.push({
                    id: item.getAttribute('data-item-id'),
                    name: item.querySelector('.cart-item-details h3').textContent,
                    storage: item.querySelector('.cart-item-details p:nth-of-type(1)').textContent.replace('Storage: ', ''),
                    color: item.querySelector('.cart-item-details p:nth-of-type(2)').textContent.replace('Color: ', ''),
                    price: item.querySelector('.cart-item-details h3.price').textContent.replace('Price: $', ''),
                    quantity: item.querySelector('input[type="text"]').value
                });
            });
            sessionStorage.setItem('cartData', JSON.stringify(cartItems));
            window.location.href = 'checkout.php';
        }

        document.addEventListener('DOMContentLoaded', function () {
            updateTotalPrice();
        });
    </script>

    <?php include('../includes/footer.php'); ?>

</body>

</html>