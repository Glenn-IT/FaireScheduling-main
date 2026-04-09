<?php
// fetch_calendar_events.php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['userid'])) { echo json_encode([]); exit; }

require '../../../database/connection.php'; // provides $conn (PDO)

// Expect ISO date range from FullCalendar
$start = isset($_GET['start']) ? $_GET['start'] : null;
$end   = isset($_GET['end'])   ? $_GET['end']   : null;

// Basic validation
if (!$start || !$end) {
  echo json_encode([]);
  exit;
}

/*
  We’ll return only APPROVED reservations within the visible range.
  Assumes schema:
    schedules(ID, userID, serviceID, date, time_start, time_end, status, ...)
    services(ID, service_name, ...)
*/
$sql = "
  SELECT 
    s.ID,
    s.userID,
    s.date,
    s.time_start,
    s.time_end,
    s.status,
    sv.service_name
  FROM schedules s
  INNER JOIN services sv ON sv.ID = s.serviceID
  WHERE s.status = 'Approved'
    AND s.date >= :startDate
    AND s.date < :endDate
  ORDER BY s.date ASC, s.time_start ASC
";

try {
  $stmt = $conn->prepare($sql);

  // FullCalendar gives ISO date-times. We only compare on date, so cut to 'YYYY-MM-DD'
  $startDate = substr($start, 0, 10);
  $endDate   = substr($end,   0, 10);

  $stmt->execute([
    ':startDate' => $startDate,
    ':endDate'   => $endDate
  ]);
  $rows = $stmt->fetchAll();

  $events = [];
  foreach ($rows as $r) {
    $date = $r['date'];
    // Build start/end ISO
    $tStart = $r['time_start'] ? substr($r['time_start'], 0, 5) : '09:00';
    $tEnd   = $r['time_end']   ? substr($r['time_end'],   0, 5) : $tStart;

    $events[] = [
      'id'     => (int)$r['ID'],
      'userID' => (int)$r['userID'],
      'status' => $r['status'],
      'title'  => $r['service_name'] ?? 'Reservation',
      'start'  => $date . 'T' . $tStart . ':00',
      'end'    => $date . 'T' . $tEnd   . ':00',
    ];
  }

  echo json_encode($events);
} catch (PDOException $e) {
  error_log('fetch_calendar_events error: ' . $e->getMessage());
  echo json_encode([]);
}
