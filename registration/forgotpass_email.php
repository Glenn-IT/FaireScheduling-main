<?php
session_start();

header('Content-Type: application/json');
require '../database/connection.php';
require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Utility: Send verification email
function sendEmailVerification($user) {
    $passkey = rand(100000, 999999);
    $_SESSION['passkey'] = $passkey;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'fairechurchscheduling@gmail.com';
        $mail->Password = 'uvlypjetmkgjnzcq';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('fairechurchscheduling@gmail.com', 'Faire Church Scheduling');
        $mail->addAddress($user['email']);
        $mail->isHTML(true);
        $mail->Subject = 'Reset Your Password';
        $mail->Body = getVerificationEmailBody($user['firstname'], $user['lastname'], $passkey);

        $mail->send();
        return $passkey;
    } catch (Exception $e) {
        error_log("Email Error: " . $e->getMessage());
        return false;
    }
}

// Email body formatter
function getVerificationEmailBody($firstName, $lastName, $passkey) {
    return "
    <p style='color: #111'>
        Hello <strong>$firstName $lastName</strong>,<br><br>
        A password reset request has been initiated for your account. To continue, please enter the verification code shown below:<br><br>
        <strong style='font-size: 1.5rem; color: #0056b3;'>$passkey</strong><br><br>
        Kindly input this code in the designated field to set a new password.<br><br>
        If you did not request this reset, no further action is needed and you may safely disregard this message.<br><br>
        Thank you,<br>
        <em>IT Team</em>
    </p>";
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// ✅ Decode JSON input
$rawInput = file_get_contents("php://input");
error_log("📥 Raw input: $rawInput");

$data = json_decode($rawInput, true);
$email = trim($data['email'] ?? '');

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Please provide an email address.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM tblusers WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $passkey = sendEmailVerification($user);

        if ($passkey) {
            $stmt = $conn->prepare("UPDATE tblusers SET code = :code WHERE id = :id");
            $stmt->bindParam(':code', $passkey);
            $stmt->bindParam(':id', $user['id']);
            $stmt->execute();

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send verification email.']);
        }
    } else {
        error_log("❌ Email not found: $email");
        echo json_encode(['success' => false, 'message' => 'Email not found in our records.']);
    }
} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
}
exit;
