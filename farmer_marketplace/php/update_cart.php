<?php
session_start();
require_once '../includes/db_connect.php';


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to update cart']);
    exit;
}


if (!isset($_POST['cart_id']) || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$cart_id = $_POST['cart_id'];
$action = $_POST['action'];
$user_id = $_SESSION['user_id'];

try {

    $stmt = $pdo->prepare("
        SELECT c.*, p.stock 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.id = ? AND c.user_id = ?
    ");
    $stmt->execute([$cart_id, $user_id]);
    $cart_item = $stmt->fetch();

    if (!$cart_item) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
        exit;
    }

    $new_quantity = $cart_item['quantity'];
    if ($action === 'increase') {
        if ($new_quantity >= $cart_item['stock']) {
            echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
            exit;
        }
        $new_quantity++;
    } else if ($action === 'decrease') {
        if ($new_quantity <= 1) {

            $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->execute([$cart_id, $user_id]);
            echo json_encode(['success' => true]);
            exit;
        }
        $new_quantity--;
    }


    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$new_quantity, $cart_id, $user_id]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error updating cart']);
}
?>