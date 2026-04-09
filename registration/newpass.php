<?php
session_start();
include '../database/connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');
$passkey = trim($data['passkey'] ?? '');

if (!$email || !$password || !$passkey) {
    echo json_encode(['success' => false, 'error' => 'Email, passkey, and password are required.']);
    exit;
}

$passwordRegex = "/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";
if (!preg_match($passwordRegex, $password)) {
    echo json_encode(['success' => false, 'error' => 'Password must include 1 uppercase, 1 number, 1 special character, and be at least 8 characters long.']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM tblusers WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'Email not found.']);
        exit;
    }

    if ((string)$user['code'] !== (string)$passkey) {
        echo json_encode(['success' => false, 'error' => 'Invalid passkey.']);
        exit;
    }

    // ✅ Hash the new password before storing
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE tblusers SET password = :password, code = 0 WHERE id = :userid");
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':userid', $user['id']);
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log("Error updating password: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred.']);
}
