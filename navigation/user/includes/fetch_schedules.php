<?php
require '../../../database/connection.php';
header('Content-Type: application/json');

try {
    $sql = "SELECT 
                s.ID, 
                s.date, 
                s.time_start, 
                s.time_end,
                s.userID,
                CONCAT(u.firstname, ' ', u.lastname) AS username, 
                sv.service_name AS service
            FROM schedules s
            JOIN tblusers u ON s.userID = u.id
            JOIN services sv ON s.serviceID = sv.ID";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $events = [];

    foreach ($data as $row) {
        $events[] = [
            'id' => $row['ID'],
            'title' => $row['service'],
            'start' => $row['date'] . 'T' . $row['time_start'],
            'end' => $row['date'] . 'T' . $row['time_end'],
            'displayTime' => date("g:i A", strtotime($row['time_start'])) . ' - ' . date("g:i A", strtotime($row['time_end'])),
            'userID' => $row['userID'] // ✅ include this
        ];
    }

    echo json_encode($events);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
