<?php
session_start();
require_once '../includes/db_connect.php';


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
    exit;
}


if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];
$quantity = (int) $_POST['quantity'];

try {

    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND stock >= ?");
    $stmt->execute([$product_id, $quantity]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not available in requested quantity']);
        exit;
    }


    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $cart_item = $stmt->fetch();

    if ($cart_item) {

        $new_quantity = $cart_item['quantity'] + $quantity;
        if ($new_quantity > $product['stock']) {
            echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$new_quantity, $cart_item['id']]);
    } else {

        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity]);
    }

    echo json_encode(['success' => true, 'message' => 'Product added to cart successfully']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error adding product to cart']);
}
?>