<?php
/**
 * verify_passkey.php
 * Checks if the submitted passkey matches the stored code for the given email.
 * Does NOT reset the password — just confirms the code is correct.
 */
session_start();
include '../database/connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$data    = json_decode(file_get_contents('php://input'), true);
$email   = trim($data['email']   ?? '');
$passkey = trim($data['passkey'] ?? '');

if (!$email || !$passkey) {
    echo json_encode(['success' => false, 'message' => 'Email and verification code are required.']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id, code FROM tblusers WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Email not found.']);
        exit;
    }

    if ((string)$user['code'] !== (string)$passkey || (int)$passkey === 0) {
        echo json_encode(['success' => false, 'message' => 'Incorrect verification code. Please check your email and try again.']);
        exit;
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log('verify_passkey error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error. Please try again.']);
}
