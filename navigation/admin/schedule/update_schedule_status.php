<?php
// schedule/update_schedule_status.php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized.']); exit;
}

require '../../../database/connection.php';
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$userId = (int) $_SESSION['userid'];

$id     = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

$allowedStatuses = ['Approved', 'Denied', 'Completed'];
if ($id <= 0 || !in_array($status, $allowedStatuses, true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']); exit;
}

try {
    // fetch schedule + user + service
    $q = $conn->prepare("
        SELECT 
            s.ID, s.userID, s.serviceID, s.date, s.time_start, s.time_end, s.status,
            s.other_contact_person, s.contact_phone, s.notes,
            u.email, u.firstname, u.lastname,
            sv.service_name, sv.description AS service_description
        FROM schedules s
        LEFT JOIN tblusers u  ON u.id  = s.userID
        LEFT JOIN services sv ON sv.id = s.serviceID
        WHERE s.ID = :id
        LIMIT 1
    ");
    $q->execute([':id' => $id]);
    $row = $q->fetch(PDO::FETCH_ASSOC);
    if (!$row) { echo json_encode(['success'=>false,'message'=>'Schedule not found.']); exit; }

    // update status
    $upd = $conn->prepare("UPDATE schedules SET status = :status WHERE ID = :id LIMIT 1");
    $upd->execute([':status'=>$status, ':id'=>$id]);

    // notification
    $notifType  = ($status === 'Denied') ? 'warning' : 'success';
    $schedDate  = $row['date'] ? date('F j, Y', strtotime($row['date'])) : 'your selected date';
    $timeRng    = '';
    if (!empty($row['time_start'])) {
        $t1 = date('g:i A', strtotime($row['time_start']));
        $t2 = !empty($row['time_end']) ? date('g:i A', strtotime($row['time_end'])) : '';
        $timeRng = $t1 . ($t2 ? ' – ' . $t2 : '');
    }
    $msgBody = ($status === 'Approved')
        ? "Good news! Your booking for {$schedDate}".($timeRng?" ({$timeRng})":'')." has been approved."
        : (($status === 'Denied')
              ? "Your booking for {$schedDate}".($timeRng?" ({$timeRng})":'')." has been denied."
              : "Your booking for {$schedDate}".($timeRng?" ({$timeRng})":'')." is now marked as Completed.");

    $ins = $conn->prepare("
        INSERT INTO notifications (user_id, type, title, message, link, is_read, created_at)
        VALUES (:u, :t, :ti, :m, :lnk, 0, NOW())
    ");
    $ins->execute([
        ':u'   => (int)$row['userID'],
        ':t'   => $notifType,
        ':ti'  => 'Booking update',
        ':m'   => $msgBody,
        ':lnk' => '../schedule/schedule.php'
    ]);

    // email (PHPMailer) with your design
    $toEmail = trim((string)$row['email']);
    if ($toEmail !== '') {
        require '../../../phpmailer/src/Exception.php';
        require '../../../phpmailer/src/PHPMailer.php';
        require '../../../phpmailer/src/SMTP.php';

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'fairechurchscheduling@gmail.com';
            $mail->Password   = 'uvlypjetmkgjnzcq'; // Gmail app password
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('fairechurchscheduling@gmail.com', 'Faire Church');
            $mail->addAddress($toEmail, trim(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? '')));
            $mail->isHTML(true);

            // subject
            $subject = ($status === 'Approved')
                ? 'Your church reservation is approved'
                : (($status === 'Denied')
                    ? 'Your church reservation was denied'
                    : 'Your church reservation is completed');

            // friendly values
            $safeName = htmlspecialchars(trim(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? '')), ENT_QUOTES, 'UTF-8');
            $svcLabel = htmlspecialchars($row['service_name'] ?: '—', ENT_QUOTES, 'UTF-8');
            $niceDate = $row['date'] ? date('F j, Y', strtotime($row['date'])) : '—';
            $niceT1   = $row['time_start'] ? date('g:i A', strtotime($row['time_start'])) : '';
            $niceT2   = $row['time_end']   ? date('g:i A', strtotime($row['time_end']))   : '';
            $niceTime = trim($niceT1 . ($niceT2 ? " – {$niceT2}" : ''));

            $safeOCP   = $row['other_contact_person']
                       ? "<tr><td style='padding:6px 0;width:140px;color:#64748b'>Other contact</td><td style='padding:6px 0'><strong>"
                         . htmlspecialchars($row['other_contact_person'], ENT_QUOTES, 'UTF-8') . "</strong></td></tr>"
                       : '';
            $safeCPH   = $row['contact_phone']
                       ? "<tr><td style='padding:6px 0;width:140px;color:#64748b'>Other contact no.</td><td style='padding:6px 0'><strong>"
                         . htmlspecialchars($row['contact_phone'], ENT_QUOTES, 'UTF-8') . "</strong></td></tr>"
                       : '';
            $safeNotes = $row['notes']
                       ? "<tr><td style='padding:6px 0;width:140px;color:#64748b'>Notes</td><td style='padding:6px 0'><strong>"
                         . nl2br(htmlspecialchars($row['notes'], ENT_QUOTES, 'UTF-8')) . "</strong></td></tr>"
                       : '';

            $ref = 'MJ' . strtoupper(dechex($row['ID'])) . '-' . strtoupper(substr(sha1($row['ID'] . $row['userID']), 0, 4));

            $body = "<!doctype html>
<html><head><meta charset='utf-8'><meta name='viewport' content='width=device-width'><title>Booking Update</title></head>
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
          <h2 style='margin:0 0 8px;font-size:20px;color:#0f172a'>Hello, {$safeName}</h2>
          <p style='margin:0 0 16px;line-height:1.6;color:#334155'>"
            . htmlspecialchars($msgBody, ENT_QUOTES, 'UTF-8') .
          "</p>
          <table role='presentation' width='100%' cellspacing='0' cellpadding='0' style='margin:12px 0 6px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px'>
            <tr><td style='padding:14px 16px'>
              <table width='100%' cellspacing='0' cellpadding='0' style='font-size:14px;color:#0f172a'>
                <tr><td style='padding:6px 0;width:140px;color:#64748b'>Event</td><td style='padding:6px 0'><strong>{$svcLabel}</strong></td></tr>
                <tr><td style='padding:6px 0;width:140px;color:#64748b'>Date</td><td style='padding:6px 0'><strong>{$niceDate}</strong></td></tr>
                <tr><td style='padding:6px 0;width:140px;color:#64748b'>Time</td><td style='padding:6px 0'><strong>".($niceTime ?: '—')."</strong></td></tr>
                {$safeOCP}{$safeCPH}{$safeNotes}
                <tr><td style='padding:6px 0;color:#64748b'>Status</td><td style='padding:6px 0'>
                  <span style='display:inline-block;background:".($status==='Approved' ? '#dcfce7' : ($status==='Completed' ? '#e5e7eb' : '#fff7ed')).";color:".($status==='Approved' ? '#166534' : ($status==='Completed' ? '#374151' : '#c2410c')).";border:1px solid ".($status==='Approved' ? '#86efac' : ($status==='Completed' ? '#d1d5db' : '#fdba74')).";border-radius:999px;padding:3px 8px;font-weight:700'>".$status."</span>
                </td></tr>
              </table>
            </td></tr>
          </table>
          <p style='margin:14px 0 0;font-size:12px;color:#64748b'>You can view full details on your schedules page.</p>
        </td></tr>
        <tr><td style='background:#0b1220;color:#a7b0c3;padding:14px 24px;font-size:12px;text-align:center'>
          Faire Church • Piat
        </td></tr>
      </table>
    </td></tr>
  </table>
</body></html>";

            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags("Booking Update\nReference: {$ref}\nStatus: {$status}\nEvent: {$row['service_name']}\nDate: {$niceDate}\nTime: {$niceTime}");
            $mail->send();
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            error_log('update_schedule_status mail error: ' . $e->getMessage());
        }
    }

    echo json_encode(['success' => true, 'message' => "Schedule #{$id} marked as {$status}."]);

} catch (PDOException $e) {
    error_log('update_schedule_status error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database update failed.']);
}
