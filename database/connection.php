<?php
$host = "localhost";
$dbname = "dbschedule";
$username = "root";  // Change to your actual database username
$password = "";      // Change to your actual database password

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false, // Prevents SQL injection
    ]);
} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    die(json_encode(["status" => "error", "message" => "Database connection failed."]));
}

// ── Auto-complete: mark Approved bookings as Completed once their date has passed ──
// Runs silently on every page load. Compares the booking date + time_end to NOW().
try {
    $conn->exec("
        UPDATE schedules
        SET status = 'Completed'
        WHERE status = 'Approved'
          AND CONCAT(date, ' ', time_end) < NOW()
    ");
} catch (PDOException $e) {
    error_log("Auto-complete schedule error: " . $e->getMessage());
}
?>
