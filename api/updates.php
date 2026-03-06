<?php
require __DIR__ . '/../lib/common.php';

$cfg = oc_load_config();
$limit = isset($_GET['limit']) ? max(1, min(2000, (int)$_GET['limit'])) : 200;
$updates = oc_read_updates($cfg, $limit);

header('Content-Type: application/json');
echo json_encode([
  'ok' => true,
  'count' => count($updates),
  'updates' => $updates,
]);
