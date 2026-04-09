<?php
header('Content-Type: application/json; charset=utf-8');
require '../../../database/connection.php';

$userid = isset($_GET['userid']) ? (int)$_GET['userid'] : 0;
if ($userid <= 0) {
    echo json_encode(["error" => "Invalid user id"]);
    exit;
}

try {
    $sql = "
        SELECT 
            id,
            firstname,
            middlename,
            lastname,
            TRIM(CONCAT_WS(' ', firstname, NULLIF(middlename,''), lastname)) AS fullname,
            email,
            user_role,
            mobilenumber,
            user_active
        FROM tblusers
        WHERE id = :id
        LIMIT 1
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $userid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(["error" => "User not found"]);
        exit;
    }

    echo json_encode(["status" => "success", "user" => $row]);
} catch (Throwable $e) {
    error_log('fetch_edit error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Server error"]);
}
