<?php
session_start();
require_once '../includes/db_connect.php';


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to use this feature']);
    exit;
}


$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['product_id'])) {
        echo json_encode(['success' => false, 'message' => 'Product ID is required']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $product_id = $data['product_id'];

    try {

        $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$product_id]);

        if ($stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }


        $stmt = $pdo->prepare("SELECT id FROM product_comparison WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Product is already in your comparison list']);
            exit;
        }


        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM product_comparison WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row['count'] >= 4) {
            echo json_encode(['success' => false, 'message' => 'You can compare up to 4 products at a time']);
            exit;
        }


        $stmt = $pdo->prepare("INSERT INTO product_comparison (user_id, product_id, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$user_id, $product_id]);

        echo json_encode(['success' => true, 'message' => 'Product added to comparison list']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error adding product to comparison list']);
    }
} elseif ($method === 'DELETE') {

    $product_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $user_id = $_SESSION['user_id'];

    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    }

    try {

        $stmt = $pdo->prepare("DELETE FROM product_comparison WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);

        echo json_encode(['success' => true, 'message' => 'Product removed from comparison list']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error removing product from comparison list']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>