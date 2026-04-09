<?php
header('Content-Type: application/json; charset=utf-8');

require '../../../database/connection.php'; // must provide $conn (PDO)

try {
    // Validate input
    if (!isset($_POST['id']) || !ctype_digit((string)$_POST['id'])) {
        echo json_encode(["error" => "Invalid user ID"]);
        exit;
    }
    $userId = (int)$_POST['id'];

    // Deactivate
    $sql = "UPDATE tblusers SET user_active = 0 WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => "User deactivated successfully"]);
    } else {
        // Either not found or already 0
        echo json_encode(["error" => "User not found or already deactivated"]);
    }
} catch (Throwable $e) {
    error_log("deactivate_account error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Database error"]);
}
