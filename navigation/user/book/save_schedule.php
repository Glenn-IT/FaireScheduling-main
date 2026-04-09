<?php
/**
 * save_schedule.php
 * Inserts a booking, sends an email, creates a notification.
 * Returns JSON. PDO throughout.
 */
declare(strict_types=1);

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['userid'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit;
}

require '../../../database/connection.php';
require '../includes/notify.php';  // add_notification($conn, ...)

// ---- PHPMailer (optional email) ----
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../../../phpmailer/src/Exception.php';
require '../../../phpmailer/src/PHPMailer.php';
require '../../../phpmailer/src/SMTP.php';

/**
 * Normalize various time inputs ("07:49", "7:49 pm", "07:49 AM") to "HH:MM:SS".
 * Returns null if it can't be parsed.
 */
function normalize_time(?string $s): ?string {
  if ($s === null) return null;
  $s = trim($s);
  if ($s === '') return null;

  // Quick path: "HH:MM" or "HH:MM:SS"
  if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $s)) {
    if (strlen($s) === 5) return $s . ':00';  // HH:MM -> HH:MM:00
    return $s; // HH:MM:SS
  }

  $ts = strtotime($s);
  if ($ts === false) return null;
  return date('H:i:s', $ts);
}

try {
  $userID       = (int)$_SESSION['userid'];
  $serviceID    = isset($_POST['serviceID']) && $_POST['serviceID'] !== '' ? (int)$_POST['serviceID'] : null;
  $date         = $_POST['date']        ?? '';
  $timeStartRaw = $_POST['time_start']  ?? '';
  $timeEndRaw   = $_POST['time_end']    ?? '';
  $otherPerson  = trim((string)($_POST['other_contact_person'] ?? ''));
  $contactPhone = trim((string)($_POST['contact_phone'] ?? ''));
  $notes        = trim((string)($_POST['notes'] ?? ''));

  $timeStart = normalize_time($timeStartRaw);
  $timeEnd   = normalize_time($timeEndRaw);

  if ($date === '' || $timeStart === null || $timeEnd === null) {
    echo json_encode(['success'=>false,'message'=>'Invalid date/time']); exit;
  }
  if ($timeEnd <= $timeStart) {
    echo json_encode(['success'=>false,'message'=>'End time must be after start time']); exit;
  }

  // ---------- Overlap check ----------
  $where = "date = :date AND NOT (time_end <= :t1 OR time_start >= :t2)";
  $params = [':date'=>$date, ':t1'=>$timeStart, ':t2'=>$timeEnd];
  if ($serviceID !== null) { $where .= " AND serviceID = :sid"; $params[':sid'] = $serviceID; }

  $sqlOverlap = "SELECT 1 FROM schedules WHERE $where LIMIT 1";
  $st = $conn->prepare($sqlOverlap);
  $st->execute($params);
  if ($st->fetch()) {
    echo json_encode(['success'=>false,'message'=>'Selected time overlaps an existing booking.']);
    exit;
  }

  // ---------- Insert booking ----------
  $sqlInsert = "
    INSERT INTO schedules (
      userID, serviceID, date, time_start, time_end,
      other_contact_person, contact_phone, notes,
      date_created, status
    ) VALUES (
      :userID, :serviceID, :date, :time_start, :time_end,
      :other_contact_person, :contact_phone, :notes,
      NOW(), 'Pending'
    )
  ";
  $ins = $conn->prepare($sqlInsert);
  $ok = $ins->execute([
    ':userID'               => $userID,
    ':serviceID'            => $serviceID, // must not be null from UI
    ':date'                 => $date,
    ':time_start'           => $timeStart,
    ':time_end'             => $timeEnd,
    ':other_contact_person' => ($otherPerson !== '') ? $otherPerson : null,
    ':contact_phone'        => ($contactPhone !== '') ? $contactPhone : null,
    ':notes'                => ($notes !== '') ? $notes : null,
  ]);

  if (!$ok) {
    echo json_encode(['success'=>false,'message'=>'Database insert failed.']);
    exit;
  }

  // --------- Build notification + email ----------
  $stmtU = $conn->prepare("
    SELECT CONCAT_WS(' ', firstname, middlename, lastname) AS name, email
    FROM tblusers WHERE id = :id LIMIT 1
  ");
  $stmtU->execute([':id'=>$userID]);
  $user = $stmtU->fetch(PDO::FETCH_ASSOC) ?: ['name'=>'Client', 'email'=>null];

  // Resolve service label
  $svcLabel = 'Church Booking';
  if ($serviceID) {
    $stmtSvc = $conn->prepare("SELECT service_name FROM services WHERE id = :sid LIMIT 1");
    $stmtSvc->execute([':sid'=>$serviceID]);
    $svcLabel = ($stmtSvc->fetchColumn()) ?: ('Service '.$serviceID);
  }

  $ref      = 'BK-' . strtoupper(dechex(time())) . '-' . substr(strtoupper(md5($userID.$date.$timeStart.$timeEnd)),0,4);
  $dateNice = date('F j, Y', strtotime($date));
  $timeNice = date('g:i A', strtotime($timeStart)) . ' - ' . date('g:i A', strtotime($timeEnd));

  // Notification for the user
  add_notification(
    $conn, $userID,
    'Booking received',
    "We received your booking for $dateNice, $timeNice. Status: Pending review.",
    'info',
    '../schedule/schedule.php'
  );

  // Send confirmation email (if email available)
  if (!empty($user['email'])) {
    try {
      $mail = new PHPMailer(true);
      $mail->isSMTP();
      $mail->Host       = 'smtp.gmail.com';
      $mail->SMTPAuth   = true;
      $mail->Username   = 'papermaxx99@gmail.com';
      $mail->Password   = 'bxcccyqtkrmgxsqc'; // Gmail app password
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
      $mail->Port       = 465;

      $mail->setFrom('papermaxx99@gmail.com', 'Faire Church Scheduling');
      $mail->addAddress($user['email'], (string)$user['name']);
      $mail->isHTML(true);
      $mail->Subject = 'Booking Received';

      $safeName  = htmlspecialchars((string)$user['name'], ENT_QUOTES);
      $safeOCP   = $otherPerson  ? "<tr><td style='padding:6px 0;color:#64748b'>Other Contact</td><td style='padding:6px 0'>".htmlspecialchars($otherPerson, ENT_QUOTES)."</td></tr>" : "";
      $safeCPH   = $contactPhone ? "<tr><td style='padding:6px 0;color:#64748b'>Contact No.</td><td style='padding:6px 0'>".htmlspecialchars($contactPhone, ENT_QUOTES)."</td></tr>" : "";
      $safeNotes = $notes        ? "<tr><td style='padding:6px 0;color:#64748b'>Notes</td><td style='padding:6px 0'>".nl2br(htmlspecialchars($notes, ENT_QUOTES))."</td></tr>" : "";

      $mail->Body = "
<!doctype html>
<html><head><meta charset='utf-8'><meta name='viewport' content='width=device-width'><title>Booking Received</title></head>
<body style='margin:0;background:#f5f7fb;font-family:Arial,Helvetica,sans-serif;color:#0f172a'>
  <table role='presentation' width='100%' cellspacing='0' cellpadding='0' style='background:#f5f7fb;padding:24px 0'>
    <tr><td align='center'>
      <table role='presentation' width='600' cellspacing='0' cellpadding='0' style='background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 8px 24px rgba(15,23,42,.08)'>
        <tr>
          <td style='background:#1e3a8a;padding:18px 24px;color:#fff'>
            <table width='100%'><tr>
              <td style='font-size:18px;font-weight:700'>Faire Church</td>
              <td align='right' style='font-size:12px;opacity:.9'>Reference: <strong>{$ref}</strong></td>
            </tr></table>
          </td>
        </tr>
        <tr><td style='padding:24px'>
          <h2 style='margin:0 0 8px;font-size:20px;color:#0f172a'>Thank you, {$safeName}</h2>
          <p style='margin:0 0 16px;line-height:1.6;color:#334155'>
            We received your booking request. Our team will review and confirm your schedule via email or SMS.
          </p>
          <table role='presentation' width='100%' cellspacing='0' cellpadding='0' style='margin:12px 0 6px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px'>
            <tr><td style='padding:14px 16px'>
              <table width='100%' cellspacing='0' cellpadding='0' style='font-size:14px;color:#0f172a'>
                <tr><td style='padding:6px 0;width:140px;color:#64748b'>Event</td><td style='padding:6px 0'><strong><?=htmlspecialchars($svcLabel, ENT_QUOTES)?></strong></td></tr>
                <tr><td style='padding:6px 0;width:140px;color:#64748b'>Date</td><td style='padding:6px 0'><strong>{$dateNice}</strong></td></tr>
                <tr><td style='padding:6px 0;width:140px;color:#64748b'>Time</td><td style='padding:6px 0'><strong>{$timeNice}</strong></td></tr>
                {$safeOCP}{$safeCPH}{$safeNotes}
                <tr><td style='padding:6px 0;color:#64748b'>Status</td><td style='padding:6px 0'><span style='display:inline-block;background:#fff7ed;color:#c2410c;border:1px solid #fdba74;border-radius:999px;padding:3px 8px;font-weight:700'>Pending Review</span></td></tr>
              </table>
            </td></tr>
          </table>
          <p style='margin:14px 0 0;font-size:12px;color:#64748b'>If you have questions, just reply to this email.</p>
        </td></tr>
        <tr><td style='background:#0b1220;color:#a7b0c3;padding:14px 24px;font-size:12px;text-align:center'>
          Faire Church • Piat
        </td></tr>
      </table>
    </td></tr>
  </table>
</body></html>";
      $mail->AltBody =
        "Booking Received ({$ref})\nEvent: {$svcLabel}\nDate: {$dateNice}\nTime: {$timeNice}\n" .
        ($otherPerson ? "Other Contact: {$otherPerson}\n" : "") .
        ($contactPhone ? "Contact No.: {$contactPhone}\n" : "") .
        ($notes ? "Notes: {$notes}\n" : "") .
        "Status: Pending Review";
      $mail->send();
    } catch (Exception $e) {
      // ignore email errors
    }
  }

  echo json_encode(['success' => true]);

} catch (Throwable $e) {
  error_log('save_schedule error: '.$e->getMessage());
  http_response_code(500);
  echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
}
