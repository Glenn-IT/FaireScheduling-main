<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // from /navigation/admin/account/ -> /database/connection.php
    require '../../../database/connection.php';
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "
        SELECT 
            id,
            TRIM(CONCAT_WS(' ',
                firstname,
                NULLIF(middlename, ''),
                lastname
            )) AS fullname,
            mobilenumber,
            email,
            user_role,
            user_active
        FROM tblusers
        ORDER BY id ASC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['data' => $rows], JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $e) {
    error_log('fetch_accounts error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['data' => [], 'error' => 'Server error']);
}
