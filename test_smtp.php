<?php
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php'; 
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'fairechurchscheduling@gmail.com';
    $mail->Password   = 'uvlypjetmkgjnzcq';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->setFrom('fairechurchscheduling@gmail.com', 'Faire Church Scheduling');
    $mail->addAddress('glenard2308@gmail.com');
    $mail->isHTML(true);
    $mail->Subject = 'Faire SMTP Test';
    $mail->Body    = 'If you see this, SMTP works!';

    $mail->send();
    echo "<br><strong style='color:green'>✅ SMTP SUCCESS — email sent!</strong>";
} catch (Exception $e) {
    echo "<br><strong style='color:red'>❌ SMTP ERROR: " . htmlspecialchars($mail->ErrorInfo) . "</strong>";
    error_log("Faire SMTP Error: " . $mail->ErrorInfo);
}
?>
</xai:function_call)

**Run:** `http://localhost/FaireScheduling-main/test_smtp.php`

**Replace** `your-test-email@example.com` with real email.

## Most Likely Fixes
| Issue | Solution |
|-------|----------|
| **App Password Invalid** | Generate new at myaccount.google.com/apppasswords |
| **Port Wrong** | Try port **587** + `ENCRYPTION_STARTTLS` |
| **OAuth2** | Run `phpmailer/get_oauth_token.php` → use refresh token |

**Plan for Fix:**
```
1. Run test_smtp.php → report exact error
2. Update app password OR implement OAuth2
3. Create config/email.php (security)
```

**Next:** Run test → paste **exact error** here.

<ask_followup_question>
<parameter name="question">Create test_smtp.php above and visit http://localhost/FaireScheduling-main/test_smtp.php - what exact ERROR message appears? Also confirm/change the test recipient email.
