<?php
header('Content-Type: application/json; charset=utf-8');

require '../../../database/connection.php'; // provides $conn (PDO)

try {
    if (!isset($_POST['id']) || !ctype_digit((string)$_POST['id'])) {
        echo json_encode(["error" => "Invalid user ID"]);
        exit;
    }
    $userId = (int)$_POST['id'];

    $stmt = $conn->prepare("UPDATE tblusers SET user_active = 1 WHERE id = :id");
    $stmt->execute([':id' => $userId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => "User activated successfully"]);
    } else {
        // No change: either not found or already active
        echo json_encode(["error" => "User not found or already active"]);
    }
} catch (Throwable $e) {
    error_log("activate_account error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Database error"]);
}
