<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}


$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();


$order_number = 'FM' . date('Ymd') . rand(1000, 9999);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Farmer Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">

            <div class="bg-white rounded-lg shadow-md p-8 text-center mb-8">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check text-3xl text-green-600"></i>
                </div>
                <h1 class="text-2xl font-bold text-green-600 mb-2">Order Placed Successfully!</h1>
                <p class="text-gray-600 mb-4">Thank you for shopping with us.</p>
                <p class="text-lg font-semibold mb-2">Order Number: <?php echo $order_number; ?></p>
                <p class="text-sm text-gray-500">Please save this number for future reference</p>
            </div>


            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-bold mb-4">Order Details</h2>
                <div class="space-y-4">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Order Status</span>
                        <span class="font-semibold text-green-600">Confirmed</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Expected Delivery</span>
                        <span class="font-semibold"><?php echo date('d M Y', strtotime('+3 days')); ?></span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Payment Method</span>
                        <span class="font-semibold">
                            <?php echo isset($_SESSION['payment_method']) && $_SESSION['payment_method'] === 'cod' ? 'Cash on Delivery' : 'Online Payment'; ?>
                        </span>
                    </div>
                </div>
            </div>


            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-bold mb-4">What's Next?</h2>
                <div class="space-y-4">
                    <div class="flex items-start space-x-4">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-envelope text-green-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold">Order Confirmation Email</h3>
                            <p class="text-gray-600">We've sent a confirmation email to your registered email address.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-4">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-truck text-green-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold">Track Your Order</h3>
                            <p class="text-gray-600">You'll receive shipping updates via email and SMS.</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-4">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-phone text-green-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold">Need Help?</h3>
                            <p class="text-gray-600">Contact our support team at support@farmermarket.com</p>
                        </div>
                    </div>
                </div>
            </div>


            <div class="flex space-x-4">
                <a href="products.php"
                    class="flex-1 bg-gray-500 text-white py-3 rounded-lg font-bold text-center hover:bg-gray-600">
                    Continue Shopping
                </a>
                <a href="#" onclick="trackOrder('<?php echo $order_number; ?>')"
                    class="flex-1 bg-green-600 text-white py-3 rounded-lg font-bold text-center hover:bg-green-700">
                    Track Order
                </a>
            </div>
        </div>
    </div>

    <script>
        function trackOrder(orderNumber) {

            alert(`Tracking order: ${orderNumber}\nThis feature will be available soon!`);
        }
    </script>
</body>

</html>