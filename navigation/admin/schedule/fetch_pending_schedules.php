<?php
// schedule/fetch_schedules.php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['userid'])) { echo json_encode(['data'=>[]]); exit; }

require '../../../database/connection.php'; // provides $conn (PDO)

// allow ?status=Pending|Approved|Denied|Completed, default = Pending
$allowed = ['Pending','Approved','Denied','Completed'];
$status = $_GET['status'] ?? 'Pending';
if (!in_array($status, $allowed, true)) { $status = 'Pending'; }

$sql = "
  SELECT 
    s.ID, s.userID, s.serviceID, s.date, s.time_start, s.time_end, s.date_created, s.status,
    s.other_contact_person, s.contact_phone, s.address, s.notes,
    u.lastname, u.firstname, u.middlename, u.mobilenumber, u.email,
    sv.service_name, sv.description AS service_description
  FROM schedules s
  INNER JOIN tblusers u ON u.id = s.userID
  INNER JOIN services sv ON sv.ID = s.serviceID
  WHERE s.status = :status
  ORDER BY s.ID DESC
";

try {
  $stmt = $conn->prepare($sql);
  $stmt->execute([':status' => $status]);
  $rows = $stmt->fetchAll();
  echo json_encode(['data' => $rows]);
} catch (PDOException $e) {
  error_log('fetch_schedules error: ' . $e->getMessage());
  echo json_encode(['data'=>[], 'error'=>'Query failed.']);
}
