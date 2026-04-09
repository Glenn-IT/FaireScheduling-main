<?php
/**
 * Simple helper to create notifications.
 * Table: notifications(user_id, type, title, message, link, is_read, created_at)
 */
declare(strict_types=1);

if (!function_exists('add_notification')) {
  function add_notification(PDO $conn, int $user_id, string $title, string $message = '',
                            string $type = 'info', ?string $link = null): bool {
    $type = in_array($type, ['info','success','warning','error'], true) ? $type : 'info';
    $sql = "INSERT INTO notifications (user_id, type, title, message, link, is_read, created_at)
            VALUES (:uid, :type, :title, :msg, :link, 0, NOW())";
    $st = $conn->prepare($sql);
    return $st->execute([
      ':uid'   => $user_id,
      ':type'  => $type,
      ':title' => $title,
      ':msg'   => $message,
      ':link'  => $link
    ]);
  }
}
