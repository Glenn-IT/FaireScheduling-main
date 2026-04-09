<?php
declare(strict_types=1);
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['userid'])) { echo json_encode(['items'=>[]]); exit; }
require '../../../database/connection.php';

try {
  $uid   = (int)$_SESSION['userid'];
  $limit = isset($_GET['limit']) ? max(1, min(50, (int)$_GET['limit'])) : 10;

  $sql = "SELECT id, type, title, message, link, is_read, created_at
          FROM notifications
          WHERE user_id = :u
          ORDER BY created_at DESC, id DESC
          LIMIT {$limit}";
  $st = $conn->prepare($sql);
  $st->execute([':u'=>$uid]);

  $items = [];
  while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
    $items[] = [
      'id'         => (int)$r['id'],
      'type'       => $r['type'] ?? 'info',
      'title'      => (string)$r['title'],
      'message'    => (string)($r['message'] ?? ''),
      'link'       => $r['link'] ?? null,
      'is_read'    => (int)$r['is_read'],
      'created_at' => (string)$r['created_at'],
    ];
  }
  echo json_encode(['items'=>$items]);

} catch (Throwable $e) {
  error_log('get_notifications error: '.$e->getMessage());
  echo json_encode(['items'=>[]]);
}
