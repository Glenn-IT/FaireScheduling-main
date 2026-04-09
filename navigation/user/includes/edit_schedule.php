<?php
header('Content-Type: application/json');
require '../../database/connection.php'; // adjust if different

try {
    $id         = (int)($_POST['id'] ?? 0);
    $userID     = (int)($_POST['userID'] ?? 0);
    $serviceID  = (int)($_POST['serviceID'] ?? 0);
    $date       = $_POST['date'] ?? '';
    $time_start = $_POST['time_start'] ?? '';
    $time_end   = $_POST['time_end'] ?? '';

    if (!$id || !$userID || !$serviceID || !$date || !$time_start || !$time_end) {
        echo json_encode(['status'=>'error','error'=>'Missing required fields.']); exit;
    }
    if (strtotime("$date $time_end") <= strtotime("$date $time_start")) {
        echo json_encode(['status'=>'error','error'=>'End time must be after start time.']); exit;
    }

    $conn->beginTransaction();

    // Overlap check excluding the record being edited
    $checkSql = "
        SELECT ID FROM schedules
        WHERE date = :date
          AND status NOT IN ('Canceled','Cancelled','Denied')
          AND (time_start < :new_end AND time_end > :new_start)
          AND ID <> :id
        LIMIT 1
        FOR UPDATE
    ";
    $check = $conn->prepare($checkSql);
    $check->execute([
        ':date'      => $date,
        ':new_start' => $time_start,
        ':new_end'   => $time_end,
        ':id'        => $id,
    ]);

    if ($check->fetchColumn()) {
        $conn->rollBack();
        echo json_encode(['status'=>'conflict','error'=>'That time slot is already booked.']);
        exit;
    }

    $upd = $conn->prepare("
        UPDATE schedules
           SET userID = :userID,
               serviceID = :serviceID,
               date = :date,
               time_start = :time_start,
               time_end = :time_end
         WHERE ID = :id
    ");
    $ok = $upd->execute([
        ':userID'     => $userID,
        ':serviceID'  => $serviceID,
        ':date'       => $date,
        ':time_start' => $time_start,
        ':time_end'   => $time_end,
        ':id'         => $id,
    ]);

    $conn->commit();

    echo json_encode($ok
        ? ['status'=>'success']
        : ['status'=>'error','error'=>'Failed to update.']);
} catch (Throwable $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo json_encode(['status'=>'error','error'=>$e->getMessage()]);
}
