<?php
/**
 * fetch_schedules.php
 * Returns FullCalendar event JSON for existing bookings.
 */
declare(strict_types=1);

session_start();
header('Content-Type: application/json');

require '../../../database/connection.php';

try {
  $serviceID = isset($_GET['serviceID']) && $_GET['serviceID'] !== '' ? (int)$_GET['serviceID'] : null;

  $where  = "1";
  $params = [];
  if ($serviceID !== null) {
    $where .= " AND serviceID = :sid";
    $params[':sid'] = $serviceID;
  }

  $sql = "SELECT id, date, time_start, time_end FROM schedules WHERE $where";
  $stmt = $conn->prepare($sql);
  $stmt->execute($params);

  $events = [];
  while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $events[] = [
      'id'      => (int)$r['id'],
      'title'   => 'Booked',
      'start'   => $r['date'].'T'.$r['time_start'],
      'end'     => $r['date'].'T'.$r['time_end'],
      'display' => 'auto'
    ];
  }

  echo json_encode($events);

} catch (Throwable $e) {
  error_log('fetch_schedules error: '.$e->getMessage());
  echo json_encode([]);
}
