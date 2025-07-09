<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$product_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if (!$product_id) {
    header('Location: products.php');
    exit;
}


$stmt = $pdo->prepare("
    SELECT p.*, 
           COALESCE(AVG(pr.rating), 0) as avg_rating,
           COUNT(pr.rating) as rating_count
    FROM products p
    LEFT JOIN product_reviews pr ON p.id = pr.product_id
    WHERE p.id = ?
    GROUP BY p.id
");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: products.php');
    exit;
}


$userReview = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        SELECT rating, review_text, created_at
        FROM product_reviews
        WHERE product_id = ? AND user_id = ?
    ");
    $stmt->execute([$product_id, $_SESSION['user_id']]);
    $userReview = $stmt->fetch(PDO::FETCH_ASSOC);
}


$stmt = $pdo->prepare("
    SELECT pr.rating, pr.review_text, pr.created_at,
           pr.user_id
    FROM product_reviews pr
    WHERE pr.product_id = ?
    ORDER BY pr.created_at DESC
");
$stmt->execute([$product_id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Farmer's Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .rating-stars {
            color: #FDB813;
        }
        .star-rating {
            display: inline-flex;
            flex-direction: row-reverse;
            gap: 0.5rem;
        }
        .star-rating input {
            display: none;
        }
        .star-rating label {
            cursor: pointer;
            color: #ccc;
            font-size: 1.5rem;
            transition: color 0.2s;
        }
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label {
            color: #FDB813;
        }
        .review-card {
            transition: transform 0.3s ease;
        }
        .review-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gray-50">

    <header class="bg-gradient-to-r from-green-600 to-green-700 text-white shadow-lg sticky top-0 z-50">
        <nav class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-8">
                    <a href="../index.php" class="text-2xl font-bold tracking-tight hover:text-green-100 transition">
                        <i class="fas fa-leaf mr-2"></i>Farmer's Marketplace
                    </a>
                    <div class="hidden md:flex space-x-6">
                        <a href="../index.php" class="hover:text-green-200 transition">Home</a>
                        <a href="products.php" class="hover:text-green-200 transition">Products</a>
                    </div>
                </div>
                <div class="flex items-center space-x-6">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="cart.php" class="flex items-center hover:text-green-200 transition">
                            <i class="fas fa-shopping-cart mr-2"></i>
                            <span class="hidden md:inline">Cart</span>
                        </a>
                        <a href="logout.php" class="hover:text-green-200 transition">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            <span class="hidden md:inline">Logout</span>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="hover:text-green-200 transition">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            <span class="hidden md:inline">Login</span>
                        </a>
                        <a href="register.php" class="bg-white text-green-600 px-4 py-2 rounded-lg hover:bg-green-50 transition">
                            Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-4 py-8">

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="md:flex">
                <div class="md:flex-shrink-0 md:w-1/2">
                    <img class="h-96 w-full object-cover md:h-full" 
                         src="../<?php echo htmlspecialchars($product['image_url']); ?>"
                         onerror="this.src='../images/products/default.jpg'"
                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <div class="p-8 md:w-1/2">
                    <div class="flex justify-between items-start">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </h1>
                            <p class="text-sm text-gray-600 mb-4">
                                Category: <?php echo htmlspecialchars(ucfirst($product['category'])); ?>
                            </p>
                        </div>
                        <p class="text-3xl font-bold text-green-600">
                            ₹<?php echo number_format($product['price'], 2); ?>
                        </p>
                    </div>

                    <div class="flex items-center mb-6">
                        <div class="rating-stars flex mr-2">
                            <?php
                            $rating = round($product['avg_rating']);
                            for ($i = 1; $i <= 5; $i++) {
                                echo '<i class="' . ($i <= $rating ? 'fas' : 'far') . ' fa-star"></i>';
                            }
                            ?>
                        </div>
                        <span class="text-gray-600">
                            (<?php echo number_format($product['avg_rating'], 1); ?>) 
                            <?php echo $product['rating_count']; ?> review<?php echo $product['rating_count'] != 1 ? 's' : ''; ?>
                        </span>
                    </div>

                    <p class="text-gray-700 mb-6">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </p>

                    <div class="flex items-center space-x-4 mb-6">
                        <div class="flex-1">
                            <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">
                                Quantity
                            </label>
                            <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Stock Status
                            </label>
                            <?php if ($product['stock'] <= 0): ?>
                                <p class="text-red-600">Out of Stock</p>
                            <?php elseif ($product['stock'] < 10): ?>
                                <p class="text-yellow-600">Low Stock (<?php echo $product['stock']; ?> left)</p>
                            <?php else: ?>
                                <p class="text-green-600">In Stock (<?php echo $product['stock']; ?> available)</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex space-x-4">
                        <button onclick="addToCart(<?php echo $product['id']; ?>)"
                                class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 flex items-center justify-center"
                                <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-cart mr-2"></i>
                            Add to Cart
                        </button>
                        <button onclick="addToComparison(<?php echo $product['id']; ?>)"
                                class="w-12 h-12 flex items-center justify-center border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 transition duration-300">
                            <i class="fas fa-balance-scale"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <div class="mt-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-8">Customer Reviews</h2>

            <?php if (isset($_SESSION['user_id'])): ?>

                <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">
                        <?php echo $userReview ? 'Update Your Review' : 'Write a Review'; ?>
                    </h3>
                    <form id="reviewForm" class="space-y-6">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Rating</label>
                            <div class="star-rating">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>"
                                           <?php echo ($userReview && $userReview['rating'] == $i) ? 'checked' : ''; ?>>
                                    <label for="star<?php echo $i; ?>">
                                        <i class="fas fa-star"></i>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div>
                            <label for="review_text" class="block text-gray-700 font-medium mb-2">Your Review</label>
                            <textarea id="review_text" name="review_text" rows="4"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    placeholder="Share your thoughts about this product..."><?php echo $userReview ? htmlspecialchars($userReview['review_text']) : ''; ?></textarea>
                        </div>

                        <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition duration-300">
                            <?php echo $userReview ? 'Update Review' : 'Submit Review'; ?>
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
                    <p class="text-blue-700">
                        Please <a href="login.php" class="font-semibold underline">login</a> to write a review.
                    </p>
                </div>
            <?php endif; ?>


            <div class="grid gap-6">
                <?php if (empty($reviews)): ?>
                    <p class="text-gray-600 text-center py-8">No reviews yet. Be the first to review this product!</p>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="rating-stars flex mr-2">
                                        <?php
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo '<i class="' . ($i <= $review['rating'] ? 'fas' : 'far') . ' fa-star"></i>';
                                        }
                                        ?>
                                    </div>
                                    <span class="font-medium text-gray-800">
                                        User #<?php echo htmlspecialchars($review['user_id']); ?>
                                    </span>
                                </div>
                                <span class="text-sm text-gray-500">
                                    <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                                </span>
                            </div>
                            <?php if (!empty($review['review_text'])): ?>
                                <p class="text-gray-700">
                                    <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        function showNotification(message, isSuccess = true) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg ${isSuccess ? 'bg-green-500' : 'bg-red-500'} text-white z-50 transform transition-transform duration-300 translate-y-0`;
            notification.innerHTML = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.transform = 'translateY(-150%)';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        function addToCart(productId) {
            const quantity = document.getElementById('quantity').value;
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('<i class="fas fa-check-circle mr-2"></i>Product added to cart successfully!');
                } else {
                    showNotification(data.message || 'Error adding product to cart', false);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('<i class="fas fa-exclamation-circle mr-2"></i>Error adding product to cart', false);
            });
        }

        function addToComparison(productId) {
            fetch('product_comparison.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ product_id: productId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('<i class="fas fa-check-circle mr-2"></i>Product added to comparison list');
                } else {
                    showNotification(data.message, false);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('<i class="fas fa-exclamation-circle mr-2"></i>Error adding product to comparison', false);
            });
        }


        document.getElementById('reviewForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('product_reviews.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    product_id: formData.get('product_id'),
                    rating: formData.get('rating'),
                    review_text: formData.get('review_text')
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success || data.message) {
                    showNotification('<i class="fas fa-check-circle mr-2"></i>' + (data.message || 'Review submitted successfully'));
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showNotification(data.error || 'Error submitting review', false);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('<i class="fas fa-exclamation-circle mr-2"></i>Error submitting review', false);
            });
        });
    </script>
</body>
</html> 