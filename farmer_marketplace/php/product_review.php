<?php
session_start();
require_once '../includes/db_connect.php';


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to leave a review']);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}


$product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
$rating = filter_input(INPUT_POST, 'rating', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$review_text = filter_input(INPUT_POST, 'review_text', FILTER_SANITIZE_STRING);
$user_id = $_SESSION['user_id'];


if (!$product_id || !$rating || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

try {

    $stmt = $pdo->prepare("SELECT id FROM product_reviews WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);

    if ($stmt->rowCount() > 0) {

        $stmt = $pdo->prepare("UPDATE product_reviews SET rating = ?, review_text = ? WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$rating, $review_text, $user_id, $product_id]);
        $message = 'Review updated successfully';
    } else {

        $stmt = $pdo->prepare("INSERT INTO product_reviews (user_id, product_id, rating, review_text) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $rating, $review_text]);
        $message = 'Review added successfully';
    }


    $stmt = $pdo->prepare("
        UPDATE products p 
        SET rating = (
            SELECT AVG(rating) 
            FROM product_reviews 
            WHERE product_id = p.id
        )
        WHERE id = ?
    ");
    $stmt->execute([$product_id]);

    echo json_encode(['success' => true, 'message' => $message]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
}
?>