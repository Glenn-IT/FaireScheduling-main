<?php
session_start();
require '../../../database/connection.php';

if (!isset($_SESSION['userid'])) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $conn->query("
        SELECT s.ID, s.date, s.time_start, s.time_end, s.status,
               s.other_contact_person, s.contact_phone, s.notes,
               CONCAT(u.firstname, ' ', u.lastname) AS booker_name,
               u.email AS booker_email,
               sv.service_name
        FROM schedules s
        LEFT JOIN tblusers u  ON u.id  = s.userID
        LEFT JOIN services sv ON sv.ID = s.serviceID
        ORDER BY s.date, s.time_start
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Map status → colour
    $colours = [
        'Pending'   => ['bg' => '#f59e0b', 'text' => '#fff'],
        'Approved'  => ['bg' => '#22c55e', 'text' => '#fff'],
        'Completed' => ['bg' => '#3b82f6', 'text' => '#fff'],
        'Denied'    => ['bg' => '#ef4444', 'text' => '#fff'],
        'Cancelled' => ['bg' => '#6b7280', 'text' => '#fff'],
        'Canceled'  => ['bg' => '#6b7280', 'text' => '#fff'],
    ];

    $events = [];
    foreach ($rows as $r) {
        $status = $r['status'] ?? 'Pending';
        $colour = $colours[$status] ?? ['bg' => '#94a3b8', 'text' => '#fff'];
        $label  = $r['service_name'] ? $r['service_name'] : 'Booking';

        $events[] = [
            'id'          => $r['ID'],
            'title'       => $label,
            'start'       => $r['date'] . 'T' . $r['time_start'],
            'end'         => $r['date'] . 'T' . $r['time_end'],
            'color'       => $colour['bg'],
            'textColor'   => $colour['text'],
            'extendedProps' => [
                'status'        => $status,
                'booker'        => $r['booker_name'],
                'email'         => $r['booker_email'],
                'phone'         => $r['contact_phone'],
                'contact'       => $r['other_contact_person'],
                'notes'         => $r['notes'],
                'time_start'    => date('h:i A', strtotime($r['time_start'])),
                'time_end'      => date('h:i A', strtotime($r['time_end'])),
                'date_fmt'      => date('F j, Y', strtotime($r['date'])),
                'service'       => $label,
            ],
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($events);
} catch (Throwable $e) {
    header('Content-Type: application/json');
    echo json_encode([]);
}
