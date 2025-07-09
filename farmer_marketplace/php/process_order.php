<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Please login to place an order']);
    exit;
}


$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (empty($data)) {
    $data = $_POST;
}


if (!isset($data['address']) || !isset($data['city']) || !isset($data['pincode']) || !isset($data['payment_method'])) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

try {

    $pdo->beginTransaction();


    $stmt = $pdo->prepare("
        SELECT c.*, p.stock, p.name, p.price 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_items = $stmt->fetchAll();

    if (empty($cart_items)) {
        throw new Exception('Your cart is empty');
    }


    foreach ($cart_items as $item) {
        if ($item['quantity'] > $item['stock']) {
            throw new Exception("Not enough stock for {$item['name']}. Available: {$item['stock']}");
        }
    }


    $total_amount = 0;
    foreach ($cart_items as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }


    if (isset($data['discount']) && is_numeric($data['discount'])) {
        $total_amount -= floatval($data['discount']);
    }


    $stmt = $pdo->prepare("
        INSERT INTO orders (
            user_id, 
            total_amount,
            shipping_address,
            shipping_city,
            shipping_pincode,
            payment_method,
            order_status,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, 'pending', CURRENT_TIMESTAMP)
    ");

    $stmt->execute([
        $_SESSION['user_id'],
        $total_amount,
        $data['address'],
        $data['city'],
        $data['pincode'],
        $data['payment_method']
    ]);

    $order_id = $pdo->lastInsertId();


    $stmt = $pdo->prepare("
        INSERT INTO order_items (
            order_id,
            product_id,
            quantity,
            price_per_unit
        ) VALUES (?, ?, ?, ?)
    ");

    $stmt_update_stock = $pdo->prepare("
        UPDATE products 
        SET stock = stock - ? 
        WHERE id = ?
    ");

    foreach ($cart_items as $item) {

        $stmt->execute([
            $order_id,
            $item['product_id'],
            $item['quantity'],
            $item['price']
        ]);


        $stmt_update_stock->execute([
            $item['quantity'],
            $item['product_id']
        ]);
    }


    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);


    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully!',
        'order_id' => $order_id
    ]);

} catch (Exception $e) {

    $pdo->rollBack();
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}