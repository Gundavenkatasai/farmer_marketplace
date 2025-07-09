<?php
session_start();
require_once '../includes/db_connect.php';


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to manage your wishlist']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_SERVER['REQUEST_METHOD'];

try {
    switch ($action) {
        case 'GET':

            $stmt = $pdo->prepare("
                SELECT p.*, w.created_at as added_to_wishlist
                FROM wishlist w
                JOIN products p ON w.product_id = p.id
                WHERE w.user_id = ?
                ORDER BY w.created_at DESC
            ");
            $stmt->execute([$user_id]);
            $wishlist = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'wishlist' => $wishlist]);
            break;

        case 'POST':

            $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);

            if (!$product_id) {
                echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
                exit;
            }

            $stmt = $pdo->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $product_id]);

            echo json_encode(['success' => true, 'message' => 'Product added to wishlist']);
            break;

        case 'DELETE':

            $product_id = filter_input(INPUT_GET, 'product_id', FILTER_SANITIZE_NUMBER_INT);

            if (!$product_id) {
                echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);

            echo json_encode(['success' => true, 'message' => 'Product removed from wishlist']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
}
?>