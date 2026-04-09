<?php
declare(strict_types=1);
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['userid'])) { echo json_encode(['error' => 'Not authorized.']); exit; }
$uid = (int)$_SESSION['userid'];
$id  = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) { echo json_encode(['error' => 'Invalid booking ID.']); exit; }

require '../../../database/connection.php';
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
  // Only allow cancelling bookings that belong to this user and are still Pending
  $sql = "UPDATE schedules SET status = 'Cancelled'
          WHERE id = :id AND userID = :u AND status = 'Pending'";
  $st = $conn->prepare($sql);
  $st->execute([':id'=>$id, ':u'=>$uid]);

  if ($st->rowCount() === 0) {
    echo json_encode(['error' => 'Unable to cancel (not pending or not your booking).']); exit;
  }

  echo json_encode(['success' => 'Booking cancelled.']);
} catch (Throwable $e) {
  echo json_encode(['error' => 'Database error.']);
}
