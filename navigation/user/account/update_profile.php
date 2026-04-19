<?php
/**
 * update_profile.php
 * Handles two actions:
 *   action=update_info     — update personal details
 *   action=update_password — change password (requires current password verification)
 */
declare(strict_types=1);
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

require '../../../database/connection.php';
$userid = (int)$_SESSION['userid'];
$action = trim($_POST['action'] ?? '');

// ── Helper ──────────────────────────────────────────────────────────────────
function respond(bool $ok, string $msg): void {
    echo json_encode(['success' => $ok, 'message' => $msg]);
    exit;
}

// ── Update personal info ─────────────────────────────────────────────────────
if ($action === 'update_info') {
    $firstname    = trim($_POST['firstname']    ?? '');
    $middlename   = trim($_POST['middlename']   ?? '');
    $lastname     = trim($_POST['lastname']     ?? '');
    $birthday     = trim($_POST['birthday']     ?? '');
    $mobilenumber = trim($_POST['mobilenumber'] ?? '');
    $email        = trim($_POST['email']        ?? '');

    if (!$firstname || !$lastname || !$email) {
        respond(false, 'First name, last name, and email are required.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(false, 'Invalid email address.');
    }
    if ($mobilenumber && !preg_match('/^09\d{9}$/', $mobilenumber)) {
        respond(false, 'Mobile must be an 11-digit PH number starting with 09.');
    }

    // Check email uniqueness (other than self)
    $chk = $conn->prepare("SELECT id FROM tblusers WHERE email = :email AND id != :id LIMIT 1");
    $chk->execute([':email' => $email, ':id' => $userid]);
    if ($chk->fetchColumn()) {
        respond(false, 'That email is already used by another account.');
    }

    // Recalculate age from birthday
    $age = 0;
    if ($birthday) {
        $bDate = new DateTime($birthday);
        $age   = (int)(new DateTime())->diff($bDate)->y;
    }

    $upd = $conn->prepare("
        UPDATE tblusers
        SET firstname = :fn, middlename = :mn, lastname = :ln,
            birthday = :bd, age = :age,
            mobilenumber = :mob, email = :email
        WHERE id = :id
    ");
    $upd->execute([
        ':fn'    => $firstname,
        ':mn'    => $middlename,
        ':ln'    => $lastname,
        ':bd'    => $birthday ?: null,
        ':age'   => $age,
        ':mob'   => $mobilenumber,
        ':email' => $email,
        ':id'    => $userid,
    ]);

    // Refresh session
    $_SESSION['firstname']  = $firstname;
    $_SESSION['middlename'] = $middlename;
    $_SESSION['lastname']   = $lastname;

    respond(true, 'Profile updated successfully.');
}

// ── Update password ──────────────────────────────────────────────────────────
if ($action === 'update_password') {
    $currentPass = $_POST['current_password'] ?? '';
    $newPass     = $_POST['new_password']     ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    if (!$currentPass || !$newPass || !$confirmPass) {
        respond(false, 'All password fields are required.');
    }
    if (strlen($newPass) < 6) {
        respond(false, 'New password must be at least 6 characters.');
    }
    if ($newPass !== $confirmPass) {
        respond(false, 'New passwords do not match.');
    }

    // Fetch current hash
    $row = $conn->prepare("SELECT password FROM tblusers WHERE id = :id LIMIT 1");
    $row->execute([':id' => $userid]);
    $hash = $row->fetchColumn();

    if (!$hash || !password_verify($currentPass, $hash)) {
        respond(false, 'Current password is incorrect.');
    }

    $newHash = password_hash($newPass, PASSWORD_BCRYPT);
    $conn->prepare("UPDATE tblusers SET password = :pw WHERE id = :id")
         ->execute([':pw' => $newHash, ':id' => $userid]);

    respond(true, 'Password changed successfully.');
}

respond(false, 'Unknown action.');
