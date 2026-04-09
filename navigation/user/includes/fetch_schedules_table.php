<?php
header('Content-Type: application/json');
session_start();

try {
  require '../../../database/connection.php';

  // Optional: limit to logged-in user
  $userID = (int)($_SESSION['userid'] ?? 0);

    $sql = "
    SELECT s.ID, sv.service_name, s.date, s.time_start, s.time_end, s.status
    FROM schedules s
    JOIN services sv ON sv.ID = s.serviceID
    WHERE s.userID = :uid
    ORDER BY s.date DESC, s.time_start ASC
    ";
  $stmt = $conn->prepare($sql);
  $stmt->execute([':uid' => $userID]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode(['data' => $rows]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['data' => [], 'error' => 'Failed to load schedules']);
}
