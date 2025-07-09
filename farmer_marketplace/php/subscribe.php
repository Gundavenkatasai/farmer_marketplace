<?php
session_start();
require_once '../includes/db_connect.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);


    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        exit;
    }

    try {

        $stmt = $pdo->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'This email is already subscribed to our newsletter.']);
            exit;
        }


        $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email, subscribed_at) VALUES (?, NOW())");
        $stmt->execute([$email]);

        echo json_encode(['success' => true, 'message' => 'Thank you for subscribing to our newsletter!']);
    } catch (PDOException $e) {


        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
    }
} else {

    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>