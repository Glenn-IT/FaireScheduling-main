<?php
// Temporarily bypass session for diagnostic
$_SESSION['userid'] = 3;

$content = file_get_contents(__DIR__ . '/get_report_data.php');
echo "File size: " . strlen($content) . " bytes\n";

// Find non-ASCII
preg_match_all('/[^\x00-\x7F]/', $content, $matches, PREG_OFFSET_CAPTURE);
if ($matches[0]) {
    foreach ($matches[0] as $m) {
        echo "Non-ASCII at offset " . $m[1] . ": hex=" . bin2hex($m[0]) . " char=" . $m[0] . "\n";
    }
} else {
    echo "No non-ASCII characters found in PHP file.\n";
}

// Now test actual JSON output by requiring connection and running queries
require '../../database/connection.php';
$year = 2025; $month = 0; $svcID = 0;
$where  = ' WHERE YEAR(s.date) = :year ';
$params = [':year' => $year];

$detailStmt = $conn->prepare(
    "SELECT s.ID, s.date, s.time_start, s.time_end, s.status,
            CONCAT(u.firstname, ' ', u.lastname) AS client,
            u.email,
            sv.service_name,
            s.other_contact_person, s.contact_phone, s.notes
     FROM schedules s
     LEFT JOIN tblusers u  ON u.id  = s.userID
     LEFT JOIN services sv ON sv.ID = s.serviceID
     $where
     ORDER BY s.date DESC"
);
$detailStmt->execute($params);
$rows = $detailStmt->fetchAll(PDO::FETCH_ASSOC);

echo "\n--- RAW rows ---\n";
foreach ($rows as $i => $r) {
    foreach ($r as $k => $v) {
        $hex = bin2hex((string)$v);
        if (preg_match('/[^\x00-\x7f]/', (string)$v)) {
            echo "Row $i [$k]: non-ASCII detected! hex=$hex value=" . $v . "\n";
        }
    }
}

$json = json_encode($rows, JSON_UNESCAPED_UNICODE);
echo "\njson_encode result length: " . strlen($json) . "\n";
echo "json_last_error: " . json_last_error() . " (" . json_last_error_msg() . ")\n";
echo "\nFull JSON:\n" . $json . "\n";
