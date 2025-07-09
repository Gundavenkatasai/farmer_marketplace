<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Please login to submit a review']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);


    if (empty($data)) {
        $data = $_POST;
    }


    if (!isset($data['product_id']) || !isset($data['rating'])) {
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }

    $product_id = filter_var($data['product_id'], FILTER_VALIDATE_INT);
    $rating = filter_var($data['rating'], FILTER_VALIDATE_INT);
    $review_text = isset($data['review_text']) ? trim($data['review_text']) : '';
    $user_id = $_SESSION['user_id'];


    if ($rating < 1 || $rating > 5) {
        echo json_encode(['error' => 'Rating must be between 1 and 5']);
        exit;
    }

    try {

        $stmt = $pdo->prepare("SELECT id FROM product_reviews WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $existing_review = $stmt->fetch();

        if ($existing_review) {

            $stmt = $pdo->prepare("
                UPDATE product_reviews 
                SET rating = ?, 
                    review_text = ?,
                    created_at = CURRENT_TIMESTAMP 
                WHERE user_id = ? AND product_id = ?
            ");
            $stmt->execute([$rating, $review_text, $user_id, $product_id]);
            echo json_encode([
                'success' => true,
                'message' => 'Review updated successfully!'
            ]);
        } else {

            $stmt = $pdo->prepare("
                INSERT INTO product_reviews (product_id, user_id, rating, review_text, created_at)
                VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)
            ");
            $stmt->execute([$product_id, $user_id, $rating, $review_text]);
            echo json_encode([
                'success' => true,
                'message' => 'Review submitted successfully!'
            ]);
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['error' => 'An error occurred while saving your review']);
        exit;
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['product_id'])) {
        echo json_encode(['error' => 'Product ID is required']);
        exit;
    }

    $product_id = filter_var($_GET['product_id'], FILTER_VALIDATE_INT);

    try {

        $stmt = $pdo->prepare("
            SELECT 
                COALESCE(AVG(rating), 0) as avg_rating,
                COUNT(*) as review_count
            FROM product_reviews 
            WHERE product_id = ?
        ");
        $stmt->execute([$product_id]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);


        $stmt = $pdo->prepare("
            SELECT 
                pr.rating,
                pr.review_text,
                pr.created_at,
                pr.user_id
            FROM product_reviews pr
            WHERE pr.product_id = ?
            ORDER BY pr.created_at DESC
        ");
        $stmt->execute([$product_id]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'stats' => $stats,
            'reviews' => $reviews
        ]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['error' => 'An error occurred while fetching reviews']);
        exit;
    }
} else {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}
?>