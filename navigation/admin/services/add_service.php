<?php
require '../../../database/connection.php'; // Adjust the path as needed

header('Content-Type: application/json');

try {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['serviceName'])) {
        $serviceName = trim($_POST['serviceName']);
        $description = trim($_POST['description']);

        if (empty($serviceName) || empty($description)) {
            echo json_encode(["error" => "All fields are required!"]);
            exit;
        }

        // Check if category already exists
        $checkQuery = "SELECT * FROM services WHERE service_name = :serviceName";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bindParam(':serviceName', $serviceName);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo json_encode(["error" => "Service already exists!"]);
            exit;
        }

        

        // Insert new category
        $insertQuery = "INSERT INTO services (service_name, description) VALUES (:serviceName, :description)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bindParam(':serviceName', $serviceName);
        $stmt->bindParam(':description', $description);

        if ($stmt->execute()) {
            echo json_encode(["success" => "Service added successfully!"]);
        } else {
            echo json_encode(["error" => "Failed to add Service."]);
        }
    } else {
        echo json_encode(["error" => "Invalid request."]);
    }
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
