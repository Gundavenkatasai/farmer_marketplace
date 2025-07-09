<?php
session_start();
require_once '../includes/db_connect.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];


$stmt = $pdo->prepare("
    SELECT p.*, pc.created_at as added_date 
    FROM product_comparison pc 
    JOIN products p ON pc.product_id = p.id 
    WHERE pc.user_id = ? 
    ORDER BY pc.created_at DESC
");
$stmt->execute([$user_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);


$stmt = $pdo->prepare("SELECT DISTINCT category FROM products ORDER BY category");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compare Products - Farmer Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .product-card {
            height: 100%;
            transition: transform 0.2s;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .comparison-table th {
            background-color: #f8f9fa;
        }

        .remove-btn {
            color: #dc3545;
            cursor: pointer;
        }

        .remove-btn:hover {
            color: #c82333;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="container my-5">
        <h1 class="mb-4">Compare Products</h1>

        <?php if (empty($products)): ?>
            <div class="alert alert-info">
                <p>You haven't added any products to compare yet.</p>
                <a href="products.php" class="btn btn-primary">Browse Products</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered comparison-table">
                    <thead>
                        <tr>
                            <th>Features</th>
                            <?php foreach ($products as $product): ?>
                                <th class="text-center">
                                    <div class="position-relative">
                                        <button class="btn btn-link remove-btn position-absolute top-0 end-0"
                                            onclick="removeFromComparison(<?php echo $product['id']; ?>)">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>"
                                            alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid mb-2"
                                            style="max-height: 150px;">
                                        <h5><?php echo htmlspecialchars($product['name']); ?></h5>
                                        <p class="text-primary mb-0">$<?php echo number_format($product['price'], 2); ?></p>
                                    </div>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>Category</th>
                            <?php foreach ($products as $product): ?>
                                <td class="text-center"><?php echo htmlspecialchars($product['category']); ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <?php foreach ($products as $product): ?>
                                <td><?php echo htmlspecialchars($product['description']); ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th>Stock</th>
                            <?php foreach ($products as $product): ?>
                                <td class="text-center">
                                    <?php if ($product['stock'] > 0): ?>
                                        <span class="text-success">In Stock (<?php echo $product['stock']; ?>)</span>
                                    <?php else: ?>
                                        <span class="text-danger">Out of Stock</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th>Rating</th>
                            <?php foreach ($products as $product): ?>
                                <td class="text-center">
                                    <?php
                                    $rating = $product['rating'] ?? 0;
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $rating) {
                                            echo '<i class="fas fa-star text-warning"></i>';
                                        } else {
                                            echo '<i class="far fa-star text-warning"></i>';
                                        }
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function removeFromComparison(productId) {
            if (confirm('Are you sure you want to remove this product from comparison?')) {
                fetch(`product_comparison.php?id=${productId}`, {
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
    </script>
</body>

</html>