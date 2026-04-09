<?php
declare(strict_types=1);
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['userid'])) { echo json_encode(['count'=>0]); exit; }
require '../../../database/connection.php';

try {
  $uid = (int)$_SESSION['userid'];
  $st  = $conn->prepare("SELECT COUNT(*) AS c FROM notifications WHERE user_id = :u AND is_read = 0");
  $st->execute([':u'=>$uid]);
  $row = $st->fetch(PDO::FETCH_ASSOC) ?: ['c'=>0];
  echo json_encode(['count' => (int)$row['c']]);
} catch (Throwable $e) {
  error_log('get_unread_count error: '.$e->getMessage());
  echo json_encode(['count'=>0]);
}
