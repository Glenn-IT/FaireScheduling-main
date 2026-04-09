<?php
require '../../../database/connection.php'; // Ensure the correct path to db.php

header('Content-Type: application/json');

try {
    // Ensure the PDO connection is used
    global $conn; 

    // Prepare and execute the query
    $query = "SELECT * FROM services";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    // Fetch results as an associative array
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return JSON response
    echo json_encode(["data" => $accounts]);
    
} catch (PDOException $e) {
    error_log("Database Query Error: " . $e->getMessage());
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
