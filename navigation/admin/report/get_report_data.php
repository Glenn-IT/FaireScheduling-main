<?php
ob_start();
session_start();
require '../../../database/connection.php';
ob_clean();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['userid'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Support both date-range mode (date_from/date_to) and legacy year/month mode
$dateFrom = isset($_GET['date_from']) && $_GET['date_from'] !== '' ? $_GET['date_from'] : null;
$dateTo   = isset($_GET['date_to'])   && $_GET['date_to']   !== '' ? $_GET['date_to']   : null;
$year  = isset($_GET['year'])    ? (int)$_GET['year']    : (int)date('Y');
$month = isset($_GET['month'])   ? (int)$_GET['month']   : 0;
$svcID = isset($_GET['service']) ? (int)$_GET['service'] : 0;

if ($dateFrom && $dateTo) {
    $where  = ' WHERE s.date >= :date_from AND s.date <= :date_to ';
    $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];
} else {
    $where  = ' WHERE YEAR(s.date) = :year ';
    $params = [':year' => $year];
    if ($month > 0) {
        $where .= ' AND MONTH(s.date) = :month ';
        $params[':month'] = $month;
    }
}

if ($svcID > 0) {
    $where .= ' AND s.serviceID = :svc ';
    $params[':svc'] = $svcID;
}

try {
    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS total,
            COALESCE(SUM(s.status = 'Approved'),  0) AS approved,
            COALESCE(SUM(s.status = 'Pending'),   0) AS pending,
            COALESCE(SUM(s.status IN ('Cancelled','Canceled')), 0) AS cancelled,
            COALESCE(SUM(s.status = 'Denied'),    0) AS denied,
            COALESCE(SUM(s.status = 'Completed'), 0) AS completed
         FROM schedules s $where"
    );
    $stmt->execute($params);
    $kpis = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare(
        "SELECT DATE_FORMAT(s.date, '%b') AS lbl, MONTH(s.date) AS mn, COUNT(*) AS c
         FROM schedules s $where GROUP BY mn, lbl ORDER BY mn"
    );
    $stmt->execute($params);
    $monthlyRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $monthlyLabels = []; $monthlyCounts = [];
    foreach ($monthlyRaw as $r) { $monthlyLabels[] = $r['lbl']; $monthlyCounts[] = (int)$r['c']; }

    $stmt = $conn->prepare(
        "SELECT COALESCE(sv.service_name, 'No service') AS lbl, COUNT(*) AS c
         FROM schedules s LEFT JOIN services sv ON sv.ID = s.serviceID
         $where GROUP BY s.serviceID, sv.service_name ORDER BY c DESC"
    );
    $stmt->execute($params);
    $svcRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $svcLabels = []; $svcCounts = [];
    foreach ($svcRaw as $r) { $svcLabels[] = $r['lbl']; $svcCounts[] = (int)$r['c']; }

    $stmt = $conn->prepare(
        "SELECT s.status AS lbl, COUNT(*) AS c
         FROM schedules s $where GROUP BY s.status ORDER BY c DESC"
    );
    $stmt->execute($params);
    $statusRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $statusLabels = []; $statusCounts = [];
    foreach ($statusRaw as $r) { $statusLabels[] = $r['lbl']; $statusCounts[] = (int)$r['c']; }

    $stmt = $conn->prepare(
        "SELECT s.ID, s.date, s.time_start, s.time_end, s.status,
                CONCAT(u.firstname, ' ', u.lastname) AS client,
                u.email,
                COALESCE(sv.service_name, 'N/A') AS service,
                COALESCE(s.other_contact_person, '') AS other_contact_person,
                COALESCE(s.contact_phone, '') AS contact_phone,
                COALESCE(s.notes, '') AS notes
         FROM schedules s
         LEFT JOIN tblusers u  ON u.id  = s.userID
         LEFT JOIN services sv ON sv.ID = s.serviceID
         $where ORDER BY s.date DESC, s.time_start DESC"
    );
    $stmt->execute($params);
    $detailRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $details = [];
    foreach ($detailRaw as $r) {
        $details[] = [
            'ID'                   => (int)$r['ID'],
            'date'                 => (string)($r['date'] ?? ''),
            'time_start'           => (string)($r['time_start'] ?? ''),
            'time_end'             => (string)($r['time_end'] ?? ''),
            'status'               => (string)($r['status'] ?? ''),
            'client'               => (string)($r['client'] ?? ''),
            'email'                => (string)($r['email'] ?? ''),
            'service'              => (string)($r['service'] ?? ''),
            'other_contact_person' => (string)($r['other_contact_person'] ?? ''),
            'contact_phone'        => (string)($r['contact_phone'] ?? ''),
            'notes'                => (string)($r['notes'] ?? ''),
        ];
    }

    $allServices = $conn->query(
        "SELECT ID, service_name FROM services ORDER BY service_name"
    )->fetchAll(PDO::FETCH_ASSOC);

    $years = $conn->query(
        "SELECT DISTINCT YEAR(date) AS yr FROM schedules ORDER BY yr DESC"
    )->fetchAll(PDO::FETCH_COLUMN);
    if (empty($years)) { $years = [(int)date('Y')]; }

    $payload = [
        'kpis'        => $kpis,
        'monthly'     => ['labels' => $monthlyLabels, 'data' => $monthlyCounts],
        'byService'   => ['labels' => $svcLabels,     'data' => $svcCounts],
        'byStatus'    => ['labels' => $statusLabels,  'data' => $statusCounts],
        'details'     => $details,
        'allServices' => $allServices,
        'years'       => $years,
    ];

    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        http_response_code(500);
        echo json_encode(['error' => 'json_encode failed: ' . json_last_error_msg()]);
        exit;
    }
    echo $json;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}