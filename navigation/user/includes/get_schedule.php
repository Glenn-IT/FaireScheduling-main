<?php
header('Content-Type: application/json');
session_start();
try {
  require '../../../database/connection.php';
  $userID = (int)($_SESSION['userid'] ?? 0);
  $id = (int)($_GET['id'] ?? 0);
  if (!$userID || !$id) { echo json_encode(['status'=>'error','error'=>'Missing data']); exit; }

  $stmt = $conn->prepare("
    SELECT s.ID, s.serviceID, s.date, s.time_start, s.time_end, s.status
    FROM schedules s
    WHERE s.ID = ? AND s.userID = ?
  ");
  $stmt->execute([$id, $userID]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$row) { echo json_encode(['status'=>'error','error'=>'Schedule not found']); exit; }
  if (strtolower($row['status']) === 'approved') {
    echo json_encode(['status'=>'error','error'=>'Approved schedules cannot be edited.']);
    exit;
  }

  echo json_encode(['status'=>'success','schedule'=>$row]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['status'=>'error','error'=>'Server error']);
}
