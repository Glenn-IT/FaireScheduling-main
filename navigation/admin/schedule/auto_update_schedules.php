<?php
/**
 * auto_update_schedules.php
 * ─────────────────────────────────────────────────────────────────
 * Automatically updates schedule statuses based on the booking date:
 *
 *  1. Pending  → Cancelled  (booking date has already passed and was never approved)
 *  2. Approved → Completed  (booking date has already passed and it was approved)
 *
 * Called via fetch() on every admin schedule page load (no cron needed).
 * Returns JSON { cancelled: N, completed: N }
 * ─────────────────────────────────────────────────────────────────
 */
declare(strict_types=1);

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['userid'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require '../../../database/connection.php';

try {
    $today = date('Y-m-d');

    // 1) Pending bookings whose date is in the past → Cancelled
    $stmtCancel = $conn->prepare("
        UPDATE schedules
        SET status = 'Cancelled'
        WHERE status = 'Pending'
          AND date < :today
    ");
    $stmtCancel->execute([':today' => $today]);
    $cancelled = $stmtCancel->rowCount();

    // 2) Approved bookings whose date is in the past → Completed
    $stmtComplete = $conn->prepare("
        UPDATE schedules
        SET status = 'Completed'
        WHERE status = 'Approved'
          AND date < :today
    ");
    $stmtComplete->execute([':today' => $today]);
    $completed = $stmtComplete->rowCount();

    echo json_encode([
        'success'   => true,
        'cancelled' => $cancelled,
        'completed' => $completed,
    ]);

} catch (PDOException $e) {
    error_log('auto_update_schedules error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Query failed.']);
}
