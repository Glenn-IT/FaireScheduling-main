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

    // Set a default/temporary password
    $newPassword = "Faire@2"; // TODO: consider generating a random temp password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Ensure user exists
    $stmt = $conn->prepare("SELECT id FROM tblusers WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    if (!$stmt->fetchColumn()) {
        echo json_encode(["error" => "User not found"]);
        exit;
    }

    // Update password
    $upd = $conn->prepare("UPDATE tblusers SET password = :password WHERE id = :id");
    $upd->execute([':password' => $hashedPassword, ':id' => $userId]);

    if ($upd->rowCount() > 0) {
        // For security, avoid returning the plaintext in logs; you may share it out-of-band if policy allows
        echo json_encode(["success" => "Password has been reset to the default temporary value."]);
    } else {
        // Already same hash or no change
        echo json_encode(["success" => "Password updated (no changes detected)."]);
    }
} catch (Throwable $e) {
    error_log("reset_account error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Database error"]);
}
