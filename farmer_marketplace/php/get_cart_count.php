<?php
session_start();
require_once '../includes/db_connect.php';

$cart_count = 0;

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    $cart_count = $result['count'];
}

echo json_encode(['count' => $cart_count]);
?> 