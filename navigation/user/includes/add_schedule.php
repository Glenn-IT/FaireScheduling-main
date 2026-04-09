<?php
header('Content-Type: application/json');
  require '../../../database/connection.php';

try {
    // Read & validate
    $userID     = (int)($_POST['userID'] ?? 0);
    $serviceID  = (int)($_POST['serviceID'] ?? 0);
    $date       = $_POST['date'] ?? '';
    $time_start = $_POST['time_start'] ?? '';
    $time_end   = $_POST['time_end'] ?? '';

    if (!$userID || !$serviceID || !$date || !$time_start || !$time_end) {
        echo json_encode(['status'=>'error','error'=>'Missing required fields.']); exit;
    }
    if (strtotime("$date $time_end") <= strtotime("$date $time_start")) {
        echo json_encode(['status'=>'error','error'=>'End time must be after start time.']); exit;
    }

    // Start a transaction to avoid race conditions
    $conn->beginTransaction();

    // Check overlap with any non-canceled/non-denied schedules
    $checkSql = "
        SELECT ID FROM schedules
        WHERE date = :date
          AND status NOT IN ('Canceled','Cancelled','Denied')
          AND (time_start < :new_end AND time_end > :new_start)
        LIMIT 1
        FOR UPDATE
    ";
    $check = $conn->prepare($checkSql);
    $check->execute([
        ':date'      => $date,
        ':new_start' => $time_start,
        ':new_end'   => $time_end,
    ]);

    if ($check->fetchColumn()) {
        $conn->rollBack();
        echo json_encode(['status'=>'conflict','error'=>'That time slot is already booked.']);
        exit;
    }

    // No conflict — insert
    $ins = $conn->prepare("
        INSERT INTO schedules (userID, serviceID, date, time_start, time_end, status)
        VALUES (:userID, :serviceID, :date, :time_start, :time_end, 'Pending')
    ");
    $ok = $ins->execute([
        ':userID'     => $userID,
        ':serviceID'  => $serviceID,
        ':date'       => $date,
        ':time_start' => $time_start,
        ':time_end'   => $time_end,
    ]);

    $conn->commit();

    echo json_encode($ok
        ? ['status'=>'success']
        : ['status'=>'error','error'=>'Failed to save.']);
} catch (Throwable $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo json_encode(['status'=>'error','error'=>$e->getMessage()]);
}
