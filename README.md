# oc-monitoring

A cPanel-friendly PHP dashboard for monitoring an OpenClaw deployment:

- which agents/sessions are running
- what tasks are in-flight
- recent status updates

## Data flow (recommended)

This repo is designed to work with a **NIP-17 (pushstr) status feed**, but cPanel/PHP cannot reliably maintain websocket subscriptions to Nostr relays.

So the recommended architecture is:

1. **Orange/OpenClaw publishes status events** to Nostr (NIP-17 DM to your monitoring identity, or public events).
2. A small **bridge/collector** (runs on the OpenClaw host, where websockets are fine) subscribes to those events and POSTs them to this site’s `/ingest.php` endpoint.
3. The PHP site stores the updates as JSON lines in `data/updates.jsonl` and renders the dashboard.

This keeps the UI simple and deployable on shared hosting.

## Deploy

- Upload to cPanel hosting
- Set a secret in `config.php`
- (Optional) set up a cron job to prune old data via `cron/prune.php`

## Endpoints

- `GET /` dashboard
- `POST /ingest.php` ingest status updates (bridge posts here)
- `GET /api/updates.php` JSON updates (for future frontend)

