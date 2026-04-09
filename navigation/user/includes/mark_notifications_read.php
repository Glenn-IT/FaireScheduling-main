<?php
declare(strict_types=1);
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['userid'])) { echo json_encode(['ok'=>false]); exit; }
require '../../../database/connection.php';

try {
  $uid = (int)$_SESSION['userid'];
  $id  = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

  if ($id > 0) {
    $st = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :u");
    $ok = $st->execute([':id'=>$id, ':u'=>$uid]);
    echo json_encode(['ok'=>$ok, 'mode'=>'single']);
    exit;
  }

  $st = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :u AND is_read = 0");
  $ok = $st->execute([':u'=>$uid]);
  echo json_encode(['ok'=>$ok, 'mode'=>'all']);

} catch (Throwable $e) {
  error_log('mark_notifications_read error: '.$e->getMessage());
  echo json_encode(['ok'=>false]);
}
