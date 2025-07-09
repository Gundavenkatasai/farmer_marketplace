<?php
session_start();
require_once '../includes/db_connect.php';


function sendEmailNotification($to, $subject, $message)
{
    $headers = "From: noreply@agrimarket.com\r\n";
    $headers .= "Reply-To: noreply@agrimarket.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    return mail($to, $subject, $message, $headers);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $response = ['success' => false, 'message' => 'An error occurred'];

    if ($_POST['action'] === 'contact') {
        $supplier_id = (int) $_POST['supplier_id'];
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $subject = trim($_POST['subject']);
        $message = trim($_POST['message']);


        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            $response['message'] = 'All fields are required';
        } else {

            $stmt = $pdo->prepare("
                SELECT s.name as supplier_name, sc.email as supplier_email
                FROM suppliers s
                LEFT JOIN supplier_contact sc ON s.id = sc.supplier_id AND sc.is_primary = 1
                WHERE s.id = ?
            ");
            $stmt->execute([$supplier_id]);
            $supplier = $stmt->fetch();

            if ($supplier) {

                $stmt = $pdo->prepare("
                    INSERT INTO supplier_contact_requests (
                        supplier_id, name, email, subject, message, created_at
                    ) VALUES (?, ?, ?, ?, ?, NOW())
                ");

                if ($stmt->execute([$supplier_id, $name, $email, $subject, $message])) {

                    $email_subject = "New Contact Request: " . $subject;
                    $email_message = "
                        <h2>New Contact Request</h2>
                        <p><strong>From:</strong> {$name} ({$email})</p>
                        <p><strong>Subject:</strong> {$subject}</p>
                        <p><strong>Message:</strong></p>
                        <p>{$message}</p>
                    ";

                    if (sendEmailNotification($supplier['supplier_email'], $email_subject, $email_message)) {
                        $response['success'] = true;
                        $response['message'] = 'Your message has been sent successfully';
                    } else {
                        $response['message'] = 'Message saved but email notification failed';
                    }
                }
            } else {
                $response['message'] = 'Supplier not found';
            }
        }
    } else if ($_POST['action'] === 'quote') {
        $supplier_id = (int) $_POST['supplier_id'];
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $product_details = trim($_POST['product_details']);
        $requirements = trim($_POST['requirements']);


        if (empty($name) || empty($email) || empty($product_details)) {
            $response['message'] = 'Required fields are missing';
        } else {

            $stmt = $pdo->prepare("
                SELECT s.name as supplier_name, sc.email as supplier_email
                FROM suppliers s
                LEFT JOIN supplier_contact sc ON s.id = sc.supplier_id AND sc.is_primary = 1
                WHERE s.id = ?
            ");
            $stmt->execute([$supplier_id]);
            $supplier = $stmt->fetch();

            if ($supplier) {

                $stmt = $pdo->prepare("
                    INSERT INTO supplier_quote_requests (
                        supplier_id, name, email, product_details, requirements, status, created_at
                    ) VALUES (?, ?, ?, ?, ?, 'pending', NOW())
                ");

                if ($stmt->execute([$supplier_id, $name, $email, $product_details, $requirements])) {

                    $email_subject = "New Quote Request";
                    $email_message = "
                        <h2>New Quote Request</h2>
                        <p><strong>From:</strong> {$name} ({$email})</p>
                        <p><strong>Product Details:</strong></p>
                        <p>{$product_details}</p>
                        " . ($requirements ? "<p><strong>Additional Requirements:</strong></p><p>{$requirements}</p>" : "") . "
                    ";

                    if (sendEmailNotification($supplier['supplier_email'], $email_subject, $email_message)) {
                        $response['success'] = true;
                        $response['message'] = 'Your quote request has been submitted successfully';
                    } else {
                        $response['message'] = 'Request saved but email notification failed';
                    }
                }
            } else {
                $response['message'] = 'Supplier not found';
            }
        }
    }


    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>