<?php
require_once __DIR__ . '/../shared/config/db_connect.php';
$pdo = getServiceCenterPDO();
$columns = $pdo->query("DESCRIBE claim_repair_details")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($columns, JSON_PRETTY_PRINT);
?>
