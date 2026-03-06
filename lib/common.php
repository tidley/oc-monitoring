<?php

function oc_load_config() {
  $cfgPath = __DIR__ . '/../config.php';
  if (!file_exists($cfgPath)) {
    http_response_code(500);
    echo "Missing config.php (copy config.php.example to config.php)";
    exit;
  }
  $cfg = require $cfgPath;
  if (!is_array($cfg)) $cfg = [];
  return $cfg;
}

function oc_ensure_data_dir($cfg) {
  $dir = $cfg['data_dir'] ?? (__DIR__ . '/../data');
  if (!is_dir($dir)) {
    @mkdir($dir, 0755, true);
  }
  return $dir;
}

function oc_read_updates($cfg, $limit = 200) {
  $file = $cfg['updates_file'] ?? (__DIR__ . '/../data/updates.jsonl');
  if (!file_exists($file)) return [];

  // Read last N lines efficiently (simple approach: read whole file; OK for small deployments)
  $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  if (!$lines) return [];
  $slice = array_slice($lines, max(0, count($lines) - $limit));

  $out = [];
  foreach ($slice as $line) {
    $j = json_decode($line, true);
    if (is_array($j)) $out[] = $j;
  }
  return $out;
}

function oc_h($s) {
  return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function oc_now() {
  return time();
}

function oc_request_json() {
  $raw = file_get_contents('php://input');
  if (!$raw) return null;
  $j = json_decode($raw, true);
  return is_array($j) ? $j : null;
}

function oc_require_ingest_auth($cfg) {
  $secret = $cfg['ingest_secret'] ?? '';
  $hdr = $_SERVER['HTTP_X_OC_SECRET'] ?? '';
  if (!$secret || !$hdr || !hash_equals($secret, $hdr)) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'unauthorized']);
    exit;
  }
}
