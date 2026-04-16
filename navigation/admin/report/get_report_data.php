<?php
session_start();
require '../../../database/connection.php';

if (!isset($_SESSION['userid'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$year  = isset($_GET['year'])    ? (int)$_GET['year']    : (int)date('Y');
$month = isset($_GET['month'])   ? (int)$_GET['month']   : 0;  // 0 = all months
$svcID = isset($_GET['service']) ? (int)$_GET['service'] : 0;  // 0 = all services

// ── Build WHERE clauses ──────────────────────────────────────────────────────
$where  = " WHERE YEAR(s.date) = :year ";
$params = [':year' => $year];

if ($month > 0) {
    $where .= " AND MONTH(s.date) = :month ";
    $params[':month'] = $month;
}
if ($svcID > 0) {
    $where .= " AND s.serviceID = :svc ";
    $params[':svc'] = $svcID;
}

// ── 1. KPIs ──────────────────────────────────────────────────────────────────
$kpiSQL = "SELECT
    COUNT(*)                                             AS total,
    SUM(status = 'Approved')                             AS approved,
    SUM(status = 'Pending')                              AS pending,
    SUM(status IN ('Cancelled','Canceled'))              AS cancelled,
    SUM(status = 'Denied')                               AS denied,
    SUM(status = 'Completed')                            AS completed
  FROM schedules s $where";
$kpiStmt = $conn->prepare($kpiSQL);
$kpiStmt->execute($params);
$kpis = $kpiStmt->fetch(PDO::FETCH_ASSOC);

// ── 2. Bookings per month ─────────────────────────────────────────────────────
$monthlySQL = "SELECT DATE_FORMAT(s.date,'%b') lbl,
                      MONTH(s.date) mn,
                      COUNT(*) c
               FROM schedules s $where
               GROUP BY mn, lbl
               ORDER BY mn";
$monthlyStmt = $conn->prepare($monthlySQL);
$monthlyStmt->execute($params);
$monthlyRaw = $monthlyStmt->fetchAll(PDO::FETCH_ASSOC);
$monthlyLabels = []; $monthlyCounts = [];
foreach ($monthlyRaw as $r) { $monthlyLabels[] = $r['lbl']; $monthlyCounts[] = (int)$r['c']; }

// ── 3. By service ─────────────────────────────────────────────────────────────
$svcSQL = "SELECT COALESCE(sv.service_name,'(No service)') lbl, COUNT(*) c
           FROM schedules s $where
           LEFT JOIN services sv ON sv.ID = s.serviceID
           GROUP BY s.serviceID, sv.service_name
           ORDER BY c DESC";
$svcStmt = $conn->prepare($svcSQL);
$svcStmt->execute($params);
$svcRaw = $svcStmt->fetchAll(PDO::FETCH_ASSOC);
$svcLabels = []; $svcCounts = [];
foreach ($svcRaw as $r) { $svcLabels[] = $r['lbl']; $svcCounts[] = (int)$r['c']; }

// ── 4. By status ─────────────────────────────────────────────────────────────
$statusSQL = "SELECT status lbl, COUNT(*) c FROM schedules s $where GROUP BY status ORDER BY c DESC";
$statusStmt = $conn->prepare($statusSQL);
$statusStmt->execute($params);
$statusRaw = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
$statusLabels = []; $statusCounts = [];
foreach ($statusRaw as $r) { $statusLabels[] = $r['lbl']; $statusCounts[] = (int)$r['c']; }

// ── 5. Detail table ──────────────────────────────────────────────────────────
$detailSQL = "SELECT s.ID, s.date, s.time_start, s.time_end, s.status,
                     CONCAT(u.firstname,' ',u.lastname) AS client,
                     u.email,
                     COALESCE(sv.service_name,'—') AS service,
                     s.other_contact_person, s.contact_phone, s.notes
              FROM schedules s $where
              LEFT JOIN tblusers u  ON u.id  = s.userID
              LEFT JOIN services sv ON sv.ID = s.serviceID
              ORDER BY s.date DESC, s.time_start DESC";
$detailStmt = $conn->prepare($detailSQL);
$detailStmt->execute($params);
$details = $detailStmt->fetchAll(PDO::FETCH_ASSOC);

// ── 6. Services list for filter ──────────────────────────────────────────────
$allServices = $conn->query("SELECT ID, service_name FROM services ORDER BY service_name")
                    ->fetchAll(PDO::FETCH_ASSOC);

// ── 7. Available years ───────────────────────────────────────────────────────
$years = $conn->query("SELECT DISTINCT YEAR(date) yr FROM schedules ORDER BY yr DESC")
              ->fetchAll(PDO::FETCH_COLUMN);
if (empty($years)) $years = [(int)date('Y')];

header('Content-Type: application/json');
echo json_encode([
    'kpis'         => $kpis,
    'monthly'      => ['labels' => $monthlyLabels, 'data' => $monthlyCounts],
    'byService'    => ['labels' => $svcLabels,     'data' => $svcCounts],
    'byStatus'     => ['labels' => $statusLabels,  'data' => $statusCounts],
    'details'      => $details,
    'allServices'  => $allServices,
    'years'        => $years,
]);
