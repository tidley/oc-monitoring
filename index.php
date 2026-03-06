<?php
require __DIR__ . '/lib/common.php';
$cfg = oc_load_config();
$updates = array_reverse(oc_read_updates($cfg, 300));

// Build simple "current state" summary by latest update per session/agent
$currentByKey = [];
foreach ($updates as $u) {
  $key = ($u['session'] ?? null) ? ('session:' . $u['session']) : ('agent:' . ($u['agent'] ?? 'unknown'));
  if (!isset($currentByKey[$key])) $currentByKey[$key] = $u;
}

function fmt_ts($ts) {
  if (!$ts) return '';
  return gmdate('Y-m-d H:i:s', (int)$ts) . ' UTC';
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>OpenClaw Monitoring</title>
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 24px; background: #0b1020; color: #e7ebff; }
    a { color: #9ec1ff; }
    .row { display: flex; gap: 12px; flex-wrap: wrap; }
    .card { background: #0f1d3f; border: 1px solid #30487f; border-radius: 12px; padding: 12px; }
    .muted { opacity: 0.8; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border-bottom: 1px solid rgba(255,255,255,0.08); padding: 8px; text-align: left; vertical-align: top; }
    code, pre { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
    pre { white-space: pre-wrap; overflow-wrap: anywhere; margin: 0; }
    .pill { display: inline-block; border: 1px solid #3a66cb; border-radius: 999px; font-size: 12px; padding: 2px 10px; background: #112a61; }
  </style>
</head>
<body>
  <h1 style="margin: 0 0 8px 0;">OpenClaw Monitoring</h1>
  <p class="muted" style="margin-top: 0;">Last 300 updates. Data ingested via <code>POST /ingest.php</code>. <a href="/api/updates.php">api</a></p>

  <div class="row">
    <div class="card" style="flex: 1; min-width: 320px;">
      <h3 style="margin: 0 0 8px 0;">Current status (latest per agent/session)</h3>
      <table>
        <thead>
          <tr>
            <th>Key</th>
            <th>State</th>
            <th>Task</th>
            <th>When</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($currentByKey as $key => $u): ?>
          <tr>
            <td><code><?= oc_h($key) ?></code></td>
            <td><span class="pill"><?= oc_h($u['state'] ?? '') ?></span></td>
            <td><?= oc_h($u['task'] ?? '') ?></td>
            <td class="muted"><?= oc_h(fmt_ts($u['ts'] ?? 0)) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="card" style="flex: 1; min-width: 320px;">
      <h3 style="margin: 0 0 8px 0;">Recent updates</h3>
      <table>
        <thead>
          <tr>
            <th>When</th>
            <th>Agent</th>
            <th>Session</th>
            <th>Task</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach (array_slice($updates, 0, 50) as $u): ?>
          <tr>
            <td class="muted"><?= oc_h(fmt_ts($u['ts'] ?? 0)) ?></td>
            <td><?= oc_h($u['agent'] ?? '') ?></td>
            <td><code><?= oc_h($u['session'] ?? '') ?></code></td>
            <td>
              <div><?= oc_h($u['task'] ?? '') ?></div>
              <?php if (!empty($u['data'])): ?>
                <details style="margin-top: 6px;">
                  <summary class="muted">data</summary>
                  <pre><?= oc_h(json_encode($u['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
                </details>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
