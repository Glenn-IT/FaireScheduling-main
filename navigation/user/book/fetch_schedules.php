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

  $where  = "s.status NOT IN ('Cancelled','Canceled')";
  $params = [];
  if ($serviceID !== null) {
    $where .= " AND s.serviceID = :sid";
    $params[':sid'] = $serviceID;
  }

  $sql = "SELECT s.id, s.date, s.time_start, s.time_end, s.status,
               sv.service_name
        FROM schedules s
        LEFT JOIN services sv ON sv.ID = s.serviceID
        WHERE $where";
  $stmt = $conn->prepare($sql);
  $stmt->execute($params);

  $colours = [
    'Pending'   => '#f59e0b',
    'Approved'  => '#22c55e',
    'Completed' => '#3b82f6',
    'Denied'    => '#ef4444',
  ];

  $events = [];
  while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $status = $r['status'] ?? 'Pending';
    $color  = $colours[$status] ?? '#94a3b8';
    $events[] = [
      'id'        => (int)$r['id'],
      'title'     => ($r['service_name'] ?? 'Booked') . ' (' . $status . ')',
      'start'     => $r['date'].'T'.$r['time_start'],
      'end'       => $r['date'].'T'.$r['time_end'],
      'color'     => $color,
      'textColor' => '#fff',
      'display'   => 'auto'
    ];
  }

  echo json_encode($events);

} catch (Throwable $e) {
  error_log('fetch_schedules error: '.$e->getMessage());
  echo json_encode([]);
}
