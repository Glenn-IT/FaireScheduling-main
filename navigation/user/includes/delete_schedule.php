<?php
header('Content-Type: application/json');
session_start();
try {
  require '../../../database/connection.php';
  $userID = (int)($_SESSION['userid'] ?? 0);
  $id = (int)($_POST['id'] ?? 0);

  if (!$userID || !$id) { echo json_encode(['status'=>'error','error'=>'Missing data']); exit; }

  // only allow deleting your own schedule
  $del = $conn->prepare("DELETE FROM schedules WHERE ID = ? AND userID = ?");
  $del->execute([$id, $userID]);

  echo json_encode(['status'=>'success']);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['status'=>'error','error'=>'Server error']);
}
