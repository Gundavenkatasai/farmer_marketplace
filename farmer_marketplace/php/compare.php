<?php
session_start();
require_once '../includes/db_connect.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}


$stmt = $pdo->prepare("
    SELECT p.*, pc.created_at as added_to_comparison
    FROM product_comparison pc
    JOIN products p ON pc.product_id = p.id
    WHERE pc.user_id = ?
    ORDER BY pc.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$comparison_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Comparison - Farmer Marketplace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gray-50">
    <?php include 'includes/header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Product Comparison</h1>
            <a href="products.php" class="text-green-600 hover:text-green-700">
                <i class="fas fa-arrow-left mr-2"></i>Back to Products
            </a>
        </div>

        <?php if (empty($comparison_list)): ?>
            <div class="text-center py-12">
                <i class="fas fa-balance-scale text-6xl text-gray-300 mb-4"></i>
                <h2 class="text-xl font-semibold text-gray-600 mb-2">No Products to Compare</h2>
                <p class="text-gray-500 mb-4">Add products to your comparison list to see them side by side.</p>
                <a href="products.php"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    <i class="fas fa-shopping-bag mr-2"></i>Browse Products
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded-lg overflow-hidden shadow-lg">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Features</th>
                            <?php foreach ($comparison_list as $product): ?>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex flex-col items-center">
                                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>"
                                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                                            class="w-32 h-32 object-cover rounded-lg mb-2">
                                        <h3 class="font-semibold text-gray-800">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </h3>
                                        <p class="text-green-600 font-medium">
                                            $<?php echo number_format($product['price'], 2); ?></p>
                                        <button onclick="removeFromComparison(<?php echo $product['id']; ?>)"
                                            class="mt-2 text-red-600 hover:text-red-700">
                                            <i class="fas fa-times"></i> Remove
                                        </button>
                                    </div>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Category</td>
                            <?php foreach ($comparison_list as $product): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($product['category']); ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Description</td>
                            <?php foreach ($comparison_list as $product): ?>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?php echo htmlspecialchars($product['description']); ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Rating</td>
                            <?php foreach ($comparison_list as $product): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <?php
                                        $rating = round($product['rating']);
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $rating) {
                                                echo '<i class="fas fa-star text-yellow-400"></i>';
                                            } else {
                                                echo '<i class="far fa-star text-yellow-400"></i>';
                                            }
                                        }
                                        ?>
                                        <span class="ml-2">(<?php echo number_format($product['rating'], 1); ?>)</span>
                                    </div>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Stock</td>
                            <?php foreach ($comparison_list as $product): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $product['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Actions</td>
                            <?php foreach ($comparison_list as $product): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <a href="product_details.php?id=<?php echo $product['id']; ?>"
                                        class="text-green-600 hover:text-green-700 mr-4">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                    <button onclick="addToCart(<?php echo $product['id']; ?>)"
                                        class="text-blue-600 hover:text-blue-700">
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </button>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        function removeFromComparison(productId) {
            if (confirm('Are you sure you want to remove this product from comparison?')) {
                fetch(`product_comparison.php?product_id=${productId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while removing the product');
                    });
            }
        }

        function addToCart(productId) {

            alert('Add to cart functionality will be implemented here');
        }
    </script>
</body>

</html>