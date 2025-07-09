<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.image_url, (p.price * c.quantity) as total_price 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['total_price'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Farmer Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Shopping Cart</h1>

        <?php if (empty($cart_items)): ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <p class="text-gray-600">Your cart is empty</p>
                <a href="products.php" class="btn-primary inline-block mt-4">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 gap-6">
                <?php foreach ($cart_items as $item): ?>
                    <div class="bg-white rounded-lg shadow-md p-6 flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <img src="../<?php echo htmlspecialchars($item['image_url']); ?>"
                                alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-24 h-24 object-cover rounded">
                            <div>
                                <h2 class="text-xl font-semibold"><?php echo htmlspecialchars($item['name']); ?></h2>
                                <p class="text-gray-600">Price: ₹<?php echo number_format($item['price'], 2); ?></p>
                                <div class="flex items-center mt-2">
                                    <button onclick="updateQuantity(<?php echo $item['id']; ?>, 'decrease')"
                                        class="bg-gray-200 px-3 py-1 rounded-l">-</button>
                                    <span class="bg-gray-100 px-4 py-1"><?php echo $item['quantity']; ?></span>
                                    <button onclick="updateQuantity(<?php echo $item['id']; ?>, 'increase')"
                                        class="bg-gray-200 px-3 py-1 rounded-r">+</button>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-semibold">₹<?php echo number_format($item['total_price'], 2); ?></p>
                            <button onclick="removeItem(<?php echo $item['id']; ?>)"
                                class="text-red-500 hover:text-red-700 mt-2">Remove</button>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="bg-white rounded-lg shadow-md p-6 mt-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-semibold">Total:</h3>
                        <p class="text-2xl font-bold">₹<?php echo number_format($total, 2); ?></p>
                    </div>
                    <div class="mt-6 flex justify-between">
                        <a href="products.php" class="btn-primary bg-gray-500 hover:bg-gray-600">Continue Shopping</a>
                        <button onclick="checkout()" class="btn-primary">Proceed to Checkout</button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function updateQuantity(cartId, action) {
            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `cart_id=${cartId}&action=${action}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                });
        }

        function removeItem(cartId) {
            if (confirm('Are you sure you want to remove this item?')) {
                fetch('remove_from_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `cart_id=${cartId}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message);
                        }
                    });
            }
        }

        function checkout() {
            window.location.href = 'checkout.php';
        }
    </script>
</body>

</html>