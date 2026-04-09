<?php
header('Content-Type: application/json; charset=utf-8');
require '../../../database/connection.php';

$userid       = (int)($_POST['userid'] ?? 0);
$firstname    = trim($_POST['firstname'] ?? '');
$lastname     = trim($_POST['lastname'] ?? '');
$middlename   = trim($_POST['middlename'] ?? '');
$mobilenumber = trim($_POST['mobilenumber'] ?? '');
$email        = trim($_POST['email'] ?? '');
$user_role    = trim($_POST['user_role'] ?? '');

if ($userid<=0 || $firstname==='' || $lastname==='' || $email==='' || $user_role==='') {
  echo json_encode(['success'=>false,'error'=>'Missing required fields']);
  exit;
}

try {
  $sql = "UPDATE tblusers
          SET firstname=:firstname,
              middlename=:middlename,
              lastname=:lastname,
              mobilenumber=:mobilenumber,
              email=:email,
              user_role=:user_role
          WHERE id=:id";
  $stmt = $conn->prepare($sql);
  $ok = $stmt->execute([
    ':firstname'    => $firstname,
    ':middlename'   => $middlename,
    ':lastname'     => $lastname,
    ':mobilenumber' => $mobilenumber,
    ':email'        => $email,
    ':user_role'    => $user_role,
    ':id'           => $userid
  ]);
  echo json_encode(['success'=>(bool)$ok]);
} catch (Throwable $e) {
  error_log('update_accounts error: '.$e->getMessage());
  http_response_code(500);
  echo json_encode(['success'=>false,'error'=>'Database error']);
}
