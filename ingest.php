<?php
require __DIR__ . '/lib/common.php';

$cfg = oc_load_config();
oc_ensure_data_dir($cfg);
oc_require_ingest_auth($cfg);

$payload = oc_request_json();
if (!$payload) {
  http_response_code(400);
  header('Content-Type: application/json');
  echo json_encode(['ok' => false, 'error' => 'invalid_json']);
  exit;
}

// Normalize and protect against huge payloads
$event = [
  'ts' => isset($payload['ts']) ? (int)$payload['ts'] : oc_now(),
  'source' => $payload['source'] ?? 'openclaw',
  'kind' => $payload['kind'] ?? 'status',
  'agent' => $payload['agent'] ?? null,
  'session' => $payload['session'] ?? null,
  'task' => $payload['task'] ?? null,
  'state' => $payload['state'] ?? null,
  'data' => $payload['data'] ?? null,
];

$line = json_encode($event, JSON_UNESCAPED_SLASHES);
if ($line === false) {
  http_response_code(400);
  header('Content-Type: application/json');
  echo json_encode(['ok' => false, 'error' => 'json_encode_failed']);
  exit;
}

if (strlen($line) > 200000) {
  http_response_code(413);
  header('Content-Type: application/json');
  echo json_encode(['ok' => false, 'error' => 'payload_too_large']);
  exit;
}

$file = $cfg['updates_file'] ?? (__DIR__ . '/data/updates.jsonl');
file_put_contents($file, $line . "\n", FILE_APPEND | LOCK_EX);

header('Content-Type: application/json');
echo json_encode(['ok' => true]);
