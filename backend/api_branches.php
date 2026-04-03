<?php
require_once __DIR__ . '/auth.php';
requireLogin();
require_once __DIR__ . '/../shared/config/db_connect.php';

header('Content-Type: application/json');

try {
    $pdo = getServiceCenterPDO();
    // Fetch all branches for dropdowns/filters
    $stmt = $pdo->query("SELECT id, branch_code, branch_name FROM branches ORDER BY branch_name ASC");
    $branches = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $branches]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
