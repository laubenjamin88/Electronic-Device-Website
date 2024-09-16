<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/Navigationbar.css">
    <link rel="stylesheet" href="../css/Checkout.css">
    <link rel="stylesheet" href="../css/Popup.css">
    <link rel="icon" href="../image/logo-image.png" type="image/png">
    <title>Checkout - Order Summary and Multi-Step Form</title>
</head>

<body>
    <?php include('../includes/navigationHeader.php'); ?>

    <div class="container">
        <!-- Multi-Step Form -->
        <div class="form-section">
            <!-- Shipping Details Form -->
            <div id="form1">
                <h2>Shipping Details</h2>
                <form id="shippingForm">
                    <label for="email">Email*</label>
                    <input type="email" id="email" name="email" required>

                    <label for="firstName">First Name*</label>
                    <input type="text" id="firstName" name="firstName" required>

                    <label for="lastName">Last Name*</label>
                    <input type="text" id="lastName" name="lastName" required>

                    <label for="address">Address*</label>
                    <input type="text" id="address" name="address" required>

                    <label for="city">City*</label>
                    <input type="text" id="city" name="city" required>

                    <label for="country">Country*</label>
                    <select id="country" name="country" required>
                        <option value="">Select Country</option>
                        <option value="us">United States</option>
                        <option value="ca">Canada</option>
                        <option value="uk">United Kingdom</option>
                    </select>

                    <label for="region">Region/State*</label>
                    <select id="region" name="region" required>
                        <option value="">Select Region</option>
                        <option value="ny">New York</option>
                        <option value="ca">California</option>
                        <option value="tx">Texas</option>
                    </select>

                    <label for="phone">Phone Number*</label>
                    <input type="text" id="phone" name="phone" required>

                    <label for="zip">Zip Code*</label>
                    <input type="text" id="zip" name="zip" required>

                    <button type="button" id="continueToPayment">Continue to Payment</button>
                    <div class="error" id="shippingError"></div>
                </form>
            </div>

            <!-- Payment Details Form -->
            <div id="form2" style="display: none;">
                <h2>Payment Details</h2>
                <form id="paymentForm">
                    <label for="cname">Name on Card*</label>
                    <input type="text" id="cname" name="cardname" required>

                    <label for="ccnum">Credit Card Number*</label>
                    <input type="text" id="ccnum" name="cardnumber" required>

                    <label for="expmonth">Exp Month*</label>
                    <input type="text" id="expmonth" name="expmonth" required>

                    <label for="expyear">Exp Year*</label>
                    <input type="text" id="expyear" name="expyear" required>

                    <label for="cvv">CVV*</label>
                    <input type="text" id="cvv" name="cvv" required>

                    <button type="button" id="submitPayment">Pay</button>
                    <div class="error" id="paymentError"></div>
                </form>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="order-summary">
            <h3>Order Summary</h3>
            <div id="checkoutItems"></div>
            <div class="price-details">
                <hr>
                <p>Subtotal: $<span id="subtotal"></span></p>
                <p>Shipping: $<span id="shippingCost"></span></p>
                <p>Sales Tax (6%): $<span id="salesTax"></span></p>
                <hr>
                <p class="total">Total: $<span id="total"></span></p>
            </div>
        </div>
    </div>

    <!-- Popup -->
    <div class="overlay" id="overlay"></div>
    <div class="popup" id="popup">
        <div class="success-icon">✔️</div>
        <h2>Payment Successful!</h2>
        <p id="popupContent"></p>
        <h4>Redirecting to the home page...</h4>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cartData = JSON.parse(sessionStorage.getItem('cartData')) || [];
            const checkoutItems = document.getElementById('checkoutItems');
            let subtotal = 0;

            // Check if cartData has items
            if (cartData.length > 0) {
                cartData.forEach(item => {
                    const itemElement = document.createElement('div');
                    itemElement.classList.add('order-item');
                    itemElement.innerHTML = `
                <img src="../image/${item.name.split(' ')[0]}/${item.name} - 2.png" alt="Product Image">
                <div class="item-details">
                    <p class="item-name">${item.name}</p>
                    <p>Storage: ${item.storage}</p>
                    <p>Color: ${item.color}</p>
                    <p>Qty: ${item.quantity}</p>
                    <p>Price: $${item.price}</p>
                </div>
            `;
                    checkoutItems.appendChild(itemElement);
                    subtotal += parseFloat(item.price) * parseInt(item.quantity, 10);
                });

                const shippingCost = 20.00;
                const taxCost = subtotal * 0.06;

                document.getElementById('subtotal').textContent = subtotal.toFixed(2);
                document.getElementById('shippingCost').textContent = shippingCost.toFixed(2);
                document.getElementById('salesTax').textContent = taxCost.toFixed(2);
                document.getElementById('total').textContent = (subtotal + shippingCost + taxCost).toFixed(2);
            }
        });

        function showPurchasePopup(purchasedItems) {
            const popup = document.getElementById('popup');
            const overlay = document.getElementById('overlay');
            const popupContent = document.getElementById('popupContent');

            popup.style.display = "block";
            overlay.style.display = "block";
            popupContent.innerHTML = purchasedItems;
            popup.classList.add('show-popup');

            console.log("Popup shown. Redirecting in 5 seconds..."); // Debugging log

            setTimeout(function () {
                console.log("Redirecting now..."); // Debugging log

                // Clear cart data from sessionStorage
                sessionStorage.removeItem('cartData');
                
                // Clear cart data from database
                fetch('ClearCart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        action: 'clear_cart'
                    })
                });

                window.location.href = '/pineApple/Index.php'; // Redirect after 5 seconds
            }, 5000); // 5000 ms = 5 seconds
        }

        document.getElementById('submitPayment').addEventListener('click', function (event) {
            if (validatePaymentForm()) {
                event.preventDefault(); // Prevent default form submission

                // Show popup with purchase details
                const cartData = JSON.parse(sessionStorage.getItem('cartData')) || [];
                let purchasedItems = "";

                cartData.forEach(item => {
                    purchasedItems += item.name + "<br>"; // Add item names for popup display
                });

                showPurchasePopup(purchasedItems);  // Show the popup after payment

                // Remove the following line if you don't want the form to be submitted automatically
                // document.getElementById('shippingForm').submit();
            }
        });

        function validateShippingForm() {
            let email = document.getElementById('email').value;
            let phone = document.getElementById('phone').value;
            let firstName = document.getElementById('firstName').value;
            let lastName = document.getElementById('lastName').value;
            let address = document.getElementById('address').value;
            let city = document.getElementById('city').value;
            let country = document.getElementById('country').value;
            let region = document.getElementById('region').value;
            let zip = document.getElementById('zip').value;
            let error = document.getElementById('shippingError');

            // Regular expressions for validation
            let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            let phonePattern = /^\d{7,15}$/;
            let zipPattern = /^\d{5}(-\d{4})?$/;
            let namePattern = /^[A-Za-z\s]+$/;
            let cityRegionPattern = /^[A-Za-z\s]+$/;

            error.textContent = ""; // Clear errors

            if (!email || !firstName || !lastName || !address || !city || !country || !region || !zip) {
                error.textContent = "Please fill out all required fields.";
                return false;
            }
            if (!emailPattern.test(email)) {
                error.textContent = "Please enter a valid email address.";
                return false;
            }
            if (!phonePattern.test(phone)) {
                error.textContent = "Please enter a valid phone number (7 to 15 digits).";
                return false;
            }
            if (!zipPattern.test(zip)) {
                error.textContent = "Please enter a valid zip code.";
                return false;
            }
            if (!cityRegionPattern.test(city)) {
                error.textContent = "City should only contain letters.";
                return false;
            }
            if (!namePattern.test(firstName)) {
                error.textContent = "First name should only contain letters.";
                return false;
            }
            if (!namePattern.test(lastName)) {
                error.textContent = "Last name should only contain letters.";
                return false;
            }

            return true;
        }

        function validatePaymentForm() {
            let cardname = document.getElementById('cname').value;
            let cardnumber = document.getElementById('ccnum').value;
            let expmonth = document.getElementById('expmonth').value;
            let expyear = document.getElementById('expyear').value;
            let cvv = document.getElementById('cvv').value;
            let error = document.getElementById('paymentError');

            error.textContent = ""; // Clear errors

            if (!cardname || !cardnumber || !expmonth || !expyear || !cvv) {
                error.textContent = "Please fill out all required fields.";
                return false;
            }
            if (!/^\d{16}$/.test(cardnumber)) {
                error.textContent = "Please enter a valid 16-digit credit card number.";
                return false;
            }
            if (!/^\d{2}$/.test(expmonth) || expmonth < 1 || expmonth > 12) {
                error.textContent = "Please enter a valid expiration month (MM).";
                return false;
            }
            if (!/^\d{2}$/.test(expyear) || expyear < new Date().getFullYear() % 100) {
                error.textContent = "Please enter a valid expiration year (YY).";
                return false;
            }
            if (!/^\d{3,4}$/.test(cvv)) {
                error.textContent = "Please enter a valid CVV.";
                return false;
            }

            return true;
        }

        document.getElementById('continueToPayment').addEventListener('click', function () {
            if (validateShippingForm()) {
                document.getElementById('form1').style.display = 'none';
                document.getElementById('form2').style.display = 'block';
            }
        });

    </script>
</body>

</html>