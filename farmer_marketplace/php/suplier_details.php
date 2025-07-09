<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_GET['id'])) {
    header('Location: suppliers.php');
    exit;
}

$supplier_id = $_GET['id'];


$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
$stmt->execute([$supplier_id]);
$supplier = $stmt->fetch();

if (!$supplier) {
    header('Location: suppliers.php');
    exit;
}


$stmt = $pdo->prepare("SELECT * FROM products WHERE supplier_id = ?");
$stmt->execute([$supplier_id]);
$products = $stmt->fetchAll();


$stmt = $pdo->prepare("
    SELECT r.*, u.name as user_name 
    FROM supplier_reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.supplier_id = ? 
    ORDER BY r.created_at DESC 
    LIMIT 5
");
$stmt->execute([$supplier_id]);
$reviews = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($supplier['name']); ?> - Farmer Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-start space-x-6">
                <img src="../<?php echo htmlspecialchars($supplier['logo_url']); ?>"
                    alt="<?php echo htmlspecialchars($supplier['name']); ?>" class="w-32 h-32 object-cover rounded-lg">
                <div class="flex-grow">
                    <h1 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($supplier['name']); ?></h1>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($supplier['description']); ?></p>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <div class="flex text-yellow-400">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $supplier['rating']): ?>
                                        <i class="fas fa-star"></i>
                                    <?php elseif ($i - 0.5 <= $supplier['rating']): ?>
                                        <i class="fas fa-star-half-alt"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <span class="ml-2 text-gray-600"><?php echo number_format($supplier['rating'], 1); ?>
                                (<?php echo $supplier['reviews_count']; ?> reviews)</span>
                        </div>
                        <span class="text-gray-600">|</span>
                        <span class="text-gray-600">Category:
                            <?php echo ucfirst(htmlspecialchars($supplier['category'])); ?></span>
                    </div>
                </div>
                <button onclick="contactSupplier()"
                    class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700">
                    Contact Supplier
                </button>
            </div>
        </div>


        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-truck text-2xl text-green-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold">Fast Delivery</h3>
                        <p class="text-gray-600">2-3 business days</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-certificate text-2xl text-green-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold">Quality Assured</h3>
                        <p class="text-gray-600">100% genuine products</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-headset text-2xl text-green-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold">24/7 Support</h3>
                        <p class="text-gray-600">Expert assistance</p>
                    </div>
                </div>
            </div>
        </div>


        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4">Products</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($products as $product): ?>
                    <div class="border rounded-lg p-4 hover:shadow-lg transition-shadow">
                        <img src="../<?php echo htmlspecialchars($product['image_url']); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                            class="w-full h-48 object-cover rounded-md mb-4">
                        <h3 class="font-semibold mb-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($product['description']); ?></p>
                        <div class="flex justify-between items-center">
                            <span
                                class="text-xl font-bold text-green-600">₹<?php echo number_format($product['price'], 2); ?></span>
                            <button onclick="addToCart(<?php echo $product['id']; ?>)"
                                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>


        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">Customer Reviews</h2>
                <button onclick="showReviewForm()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Write a Review
                </button>
            </div>
            <div class="space-y-4">
                <?php foreach ($reviews as $review): ?>
                    <div class="border-b pb-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center">
                                    <span class="font-semibold"><?php echo htmlspecialchars($review['user_name']); ?></span>
                                    <span class="mx-2">•</span>
                                    <div class="flex text-yellow-400">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i
                                                class="fas fa-star <?php echo $i <= $review['rating'] ? '' : 'text-gray-300'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($review['comment']); ?></p>
                            </div>
                            <span class="text-gray-500 text-sm">
                                <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>


        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-4">Contact Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold mb-2">Business Hours</h3>
                    <p class="text-gray-600">Monday - Friday: 9:00 AM - 6:00 PM</p>
                    <p class="text-gray-600">Saturday: 9:00 AM - 1:00 PM</p>
                    <p class="text-gray-600">Sunday: Closed</p>
                </div>
                <div>
                    <h3 class="font-semibold mb-2">Contact Details</h3>
                    <p class="text-gray-600">Email:
                        info@<?php echo strtolower(str_replace(' ', '', $supplier['name'])); ?>.com</p>
                    <p class="text-gray-600">Phone: +91-XXXXXXXXXX</p>
                    <p class="text-gray-600">Address: Industrial Area, City Name</p>
                </div>
            </div>
        </div>
    </div>


    <div id="reviewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-xl font-bold mb-4">Write a Review</h3>
            <form id="reviewForm" onsubmit="submitReview(event)">
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Rating</label>
                    <div class="flex space-x-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <button type="button" onclick="setRating(<?php echo $i; ?>)"
                                class="text-2xl text-gray-300 hover:text-yellow-400 rating-star">
                                ★
                            </button>
                        <?php endfor; ?>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Comment</label>
                    <textarea required class="w-full p-2 border rounded" rows="4"></textarea>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="hideReviewForm()" class="px-4 py-2 border rounded hover:bg-gray-100">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        Submit Review
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function contactSupplier() {

            alert('Contact form will be available soon!');
        }

        function addToCart(productId) {
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=1`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Product added to cart successfully!');
                    } else {
                        alert(data.message || 'Error adding product to cart');
                    }
                });
        }

        function showReviewForm() {
            document.getElementById('reviewModal').classList.remove('hidden');
        }

        function hideReviewForm() {
            document.getElementById('reviewModal').classList.add('hidden');
        }

        function setRating(rating) {
            const stars = document.querySelectorAll('.rating-star');
            stars.forEach((star, index) => {
                star.classList.toggle('text-yellow-400', index < rating);
                star.classList.toggle('text-gray-300', index >= rating);
            });
        }

        function submitReview(event) {
            event.preventDefault();

            alert('Review submission will be available soon!');
            hideReviewForm();
        }
    </script>
</body>

</html>