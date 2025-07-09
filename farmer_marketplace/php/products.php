<?php
session_start();
require_once '../includes/db_connect.php';


$category = isset($_GET['category']) ? $_GET['category'] : '';
$min_price = isset($_GET['min_price']) ? (float) $_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float) $_GET['max_price'] : 1000000;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';


$query = "SELECT p.*, COALESCE(AVG(pr.rating), 0) as avg_rating, COUNT(pr.rating) as rating_count 
          FROM products p 
          LEFT JOIN product_reviews pr ON p.id = pr.product_id 
          WHERE p.price >= ? AND price <= ?";
$params = [$min_price, $max_price];

if (!empty($category)) {
    $query .= " AND p.category = ?";
    $params[] = $category;
}


$query .= " GROUP BY p.id";


switch ($sort) {
    case 'price_asc':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'name_desc':
        $query .= " ORDER BY p.name DESC";
        break;
    case 'rating_desc':
        $query .= " ORDER BY avg_rating DESC";
        break;
    default:
        $query .= " ORDER BY p.name ASC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();


$stmt = $pdo->query("SELECT DISTINCT category FROM products");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Farmer's Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .product-card:hover .product-image {
            transform: scale(1.05);
        }
        .rating-stars {
            color: #FDB813;
        }
        .filter-section {
            background: linear-gradient(to right, #f7f7f7, #ffffff);
        }
        .custom-button {
            transition: all 0.3s ease;
        }
        .custom-button:hover {
            transform: translateY(-2px);
        }
        .badge {
            position: absolute;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.875rem;
            z-index: 10;
        }
        .stock-badge {
            top: 1rem;
            right: 1rem;
        }
        .category-badge {
            top: 1rem;
            left: 1rem;
            background-color: rgba(255, 255, 255, 0.9);
            color: #2F855A;
        }
        .star-rating-container {
            position: relative;
        }
        .rating-form {
            min-width: 300px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .interactive-stars i {
            transition: color 0.2s ease;
            cursor: pointer;
        }
        .interactive-stars i.active,
        .interactive-stars i:hover,
        .interactive-stars i:hover ~ i {
            color: #FDB813;
        }
        .star-icon {
            transition: transform 0.2s ease, color 0.2s ease;
        }
        .star-icon:hover {
            transform: scale(1.2);
        }
        .submit-rating, .cancel-rating {
            transition: all 0.3s ease;
        }
        .submit-rating:hover {
            transform: translateY(-1px);
        }
        .rating-form {
            animation: slideDown 0.3s ease;
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .rating-form .flex.justify-end {
            position: sticky;
            bottom: 0;
            background: white;
            padding-top: 0.5rem;
            border-top: 1px solid #e5e7eb;
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
        <!-- Filters -->
        <div class="filter-section rounded-xl shadow-md mb-8 overflow-hidden">
            <div class="bg-white p-6">
                <h2 class="text-xl font-bold mb-6 text-gray-800 flex items-center">
                    <i class="fas fa-filter mr-2 text-green-600"></i>Filter Products
                </h2>
                <form method="GET" action="products.php" class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-semibold" for="category">
                            Category
                        </label>
                        <select name="category" id="category" 
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" 
                                        <?php echo $category === $cat ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(ucfirst($cat)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-semibold" for="min_price">
                            Minimum Price
                        </label>
                        <input type="number" name="min_price" id="min_price" value="<?php echo $min_price; ?>"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-semibold" for="max_price">
                            Maximum Price
                        </label>
                        <input type="number" name="max_price" id="max_price" value="<?php echo $max_price; ?>"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-semibold" for="sort">
                            Sort By
                        </label>
                        <select name="sort" id="sort" 
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                            <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                            <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                            <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                            <option value="rating_desc" <?php echo $sort === 'rating_desc' ? 'selected' : ''; ?>>Highest Rated</option>
                        </select>
                    </div>
                    <div class="md:col-span-4 flex justify-center">
                        <button type="submit" 
                                class="custom-button bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg shadow-md hover:shadow-lg flex items-center">
                            <i class="fas fa-search mr-2"></i>
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>


        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            <?php foreach ($products as $product): ?>
                <div class="product-card bg-white rounded-xl shadow-md overflow-hidden" data-product-id="<?php echo $product['id']; ?>">
                    <div class="relative h-56">
                        <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" 
                             onerror="this.src='../images/products/default.jpg'"
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             class="product-image">
                        <?php if ($product['stock'] <= 0): ?>
                            <div class="badge stock-badge bg-red-500 text-white">
                                Out of Stock
                            </div>
                        <?php elseif ($product['stock'] < 10): ?>
                            <div class="badge stock-badge bg-yellow-500 text-white">
                                Low Stock
                            </div>
                        <?php endif; ?>
                        <div class="badge category-badge">
                            <?php echo htmlspecialchars(ucfirst($product['category'])); ?>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-2 hover:text-green-600 transition">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </h3>
                        <div class="flex items-center justify-between mb-4">
                            <p class="text-2xl font-bold text-green-600">
                                ₹<?php echo number_format($product['price'], 2); ?>
                            </p>
                            <div class="flex items-center">
                                <div class="star-rating-container relative" data-product-id="<?php echo $product['id']; ?>">
                                    <div class="rating-display flex items-center">
                                        <div class="rating-stars flex mr-2">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="star-icon <?php echo ($i <= round($product['avg_rating'])) ? 'fas' : 'far'; ?> fa-star cursor-pointer hover:text-yellow-400 transition-colors"
                                                   data-rating="<?php echo $i; ?>"
                                                   title="Rate <?php echo $i; ?> star<?php echo $i !== 1 ? 's' : ''; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="text-sm text-gray-600">
                                            <?php if ($product['rating_count'] > 0): ?>
                                                (<?php echo number_format($product['avg_rating'], 1); ?>) 
                                                <?php echo $product['rating_count']; ?> review<?php echo $product['rating_count'] != 1 ? 's' : ''; ?>
                                            <?php else: ?>
                                                No reviews
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                    <div class="rating-form hidden absolute right-0 bg-white p-4 rounded-lg shadow-lg z-50 mt-2 border border-gray-200 w-72">
                                        <h4 class="font-semibold mb-2">Rate this product</h4>
                                        <form onsubmit="submitProductReview(event, <?php echo $product['id']; ?>)" class="space-y-4">
                                            <div class="interactive-stars flex justify-center mb-3 text-2xl">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star cursor-pointer text-gray-300 hover:text-yellow-400 transition-colors px-1"
                                                       data-rating="<?php echo $i; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <div>
                                                <textarea name="review_text" 
                                                          class="review-text w-full border rounded p-2 focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                                                          rows="3"
                                                          placeholder="Write your review (optional)"></textarea>
                                            </div>
                                            <div class="flex justify-end space-x-2">
                                                <button type="button"
                                                        class="cancel-rating px-4 py-2 text-gray-600 hover:text-gray-800 rounded border border-gray-300 hover:bg-gray-50">
                                                    Cancel
                                                </button>
                                                <button type="submit"
                                                        class="submit-rating bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition-colors">
                                                    Submit Review
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-1">
                                    <label for="quantity-<?php echo $product['id']; ?>" class="block text-sm font-medium text-gray-700 mb-1">
                                        Quantity
                                    </label>
                                    <input type="number" id="quantity-<?php echo $product['id']; ?>" 
                                           value="1" min="1" max="<?php echo $product['stock']; ?>" 
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Stock
                                    </label>
                                    <p class="text-gray-600"><?php echo $product['stock']; ?> available</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button onclick="addToCart(<?php echo $product['id']; ?>)" 
                                        class="custom-button flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center justify-center"
                                        <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                                    <i class="fas fa-shopping-cart mr-2"></i>
                                    Add to Cart
                                </button>
                                <button onclick="addToComparison(<?php echo $product['id']; ?>)" 
                                        class="custom-button w-12 h-12 flex items-center justify-center border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50">
                                    <i class="fas fa-balance-scale"></i>
                                </button>
                                <a href="./product_details.php?id=<?php echo $product['id']; ?>" 
                                   class="custom-button w-12 h-12 flex items-center justify-center border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 hover:text-green-600 hover:border-green-600 transition-colors">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
        function submitProductReview(event, productId) {
            event.preventDefault();
            const form = event.target;
            const stars = form.querySelectorAll('.interactive-stars i.text-yellow-400');
            const rating = stars.length;
            const reviewText = form.querySelector('.review-text').value;
            const submitButton = form.querySelector('.submit-rating');

            if (!rating) {
                showNotification('<i class="fas fa-exclamation-circle mr-2"></i>Please select a rating', false);
                return;
            }

            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Submitting...';

            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('rating', rating);
            formData.append('review_text', reviewText || '');

            fetch('./product_reviews.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showNotification('<i class="fas fa-check-circle mr-2"></i>' + data.message);

                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    throw new Error(data.error || 'Error submitting review');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('<i class="fas fa-exclamation-circle mr-2"></i>' + error.message, false);
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = 'Submit Review';
            });
        }


        document.addEventListener('DOMContentLoaded', function() {

            document.querySelectorAll('.interactive-stars i').forEach((star, index) => {
                star.addEventListener('click', function() {
                    const stars = this.parentElement.querySelectorAll('i');
                    stars.forEach((s, i) => {
                        s.classList.toggle('text-yellow-400', i <= index);
                        s.classList.toggle('text-gray-300', i > index);
                    });
                });
            });


            document.querySelectorAll('.rating-stars .star-icon').forEach(star => {
                star.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        window.location.href = 'login.php';
                        return;
                    <?php endif; ?>

                    const container = this.closest('.star-rating-container');
                    const form = container.querySelector('.rating-form');
                    

                    document.querySelectorAll('.rating-form').forEach(f => {
                        if (f !== form) f.classList.add('hidden');
                    });
                    

                    form.classList.toggle('hidden');
                });
            });


            document.querySelectorAll('.cancel-rating').forEach(button => {
                button.addEventListener('click', function() {
                    const form = this.closest('.rating-form');
                    form.classList.add('hidden');
                    

                    const stars = form.querySelectorAll('.interactive-stars i');
                    stars.forEach(s => {
                        s.classList.remove('text-yellow-400');
                        s.classList.add('text-gray-300');
                    });
                    form.querySelector('.review-text').value = '';
                });
            });


            document.addEventListener('click', function(e) {
                if (!e.target.closest('.star-rating-container')) {
                    document.querySelectorAll('.rating-form').forEach(form => {
                        form.classList.add('hidden');
                    });
                }
            });
        });

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
            const quantity = document.getElementById(`quantity-${productId}`).value;

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
    </script>
</body>

</html>