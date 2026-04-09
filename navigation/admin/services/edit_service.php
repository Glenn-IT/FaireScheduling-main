<?php
header("Content-Type: application/json; charset=utf-8");
require '../../../database/connection.php'; // provides $conn (PDO)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(["error" => "Invalid request method"]); exit;
}

$id          = $_POST['id'] ?? null;
$serviceName = $_POST['category'] ?? null;     // coming from JS `category: name`
$description = $_POST['description'] ?? null;

if (!$id || !$serviceName || !$description) {
  echo json_encode(["error" => "Missing required fields"]); exit;
}

try {
  $sql = "UPDATE services
          SET service_name = :service_name, description = :description
          WHERE id = :id";
  $stmt = $conn->prepare($sql);
  $ok = $stmt->execute([
    ':service_name' => $serviceName,
    ':description'  => $description,
    ':id'           => $id
  ]);

  if ($ok && $stmt->rowCount() >= 0) {
    echo json_encode(["success" => "Service updated successfully!"]);
  } else {
    echo json_encode(["error" => "No changes or update failed."]);
  }
} catch (Throwable $e) {
  error_log("edit_service error: " . $e->getMessage());
  http_response_code(500);
  echo json_encode(["error" => "Database error"]);
}
