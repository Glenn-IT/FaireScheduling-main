<?php
header('Content-Type: application/json');
session_start();

try {
    // ⬇️ Adjust the path to your connection file
    require '../../../database/connection.php'; // or require '../../database/db.php';

    $stmt = $conn->prepare("SELECT ID, service_name FROM services ORDER BY service_name ASC");
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'services' => $services
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'error' => 'Failed to fetch services.'
    ]);
}
