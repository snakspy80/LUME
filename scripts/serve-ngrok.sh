#!/usr/bin/env bash
set -euo pipefail

HOST="${LUME_HOST:-127.0.0.1}"
PORT="${LUME_PORT:-8080}"
NGROK_BIN="${NGROK_BIN:-ngrok}"
NGROK_API_URL="${NGROK_API_URL:-http://127.0.0.1:4040/api/tunnels}"
SERVER_LOG="${LUME_SERVER_LOG:-writable/logs/dev-server.log}"
NGROK_LOG="${LUME_NGROK_LOG:-writable/logs/ngrok.log}"
PUBLIC_URL=""

mkdir -p "$(dirname "$SERVER_LOG")" "$(dirname "$NGROK_LOG")"

cleanup() {
  if [[ -n "${SERVER_PID:-}" ]] && kill -0 "${SERVER_PID}" 2>/dev/null; then
    kill "${SERVER_PID}" 2>/dev/null || true
  fi

  if [[ -n "${NGROK_PID:-}" ]] && kill -0 "${NGROK_PID}" 2>/dev/null; then
    kill "${NGROK_PID}" 2>/dev/null || true
  fi
}

trap cleanup EXIT INT TERM

if ! command -v "$NGROK_BIN" >/dev/null 2>&1; then
  echo "ngrok was not found on PATH." >&2
  exit 1
fi

if ! command -v curl >/dev/null 2>&1; then
  echo "curl is required to discover the ngrok public URL." >&2
  exit 1
fi

echo "Starting ngrok tunnel for http://${HOST}:${PORT}"
"$NGROK_BIN" http "http://${HOST}:${PORT}" --log=stdout >"$NGROK_LOG" 2>&1 &
NGROK_PID=$!

for _ in $(seq 1 30); do
  if ! kill -0 "$NGROK_PID" 2>/dev/null; then
    echo "ngrok exited before the tunnel URL was ready." >&2
    tail -n 40 "$NGROK_LOG" >&2 || true
    exit 1
  fi

  if PUBLIC_URL="$(curl -fsS "$NGROK_API_URL" | php -r '$data = json_decode(stream_get_contents(STDIN), true); if (! is_array($data["tunnels"] ?? null)) { exit(1); } foreach ($data["tunnels"] as $tunnel) { $url = $tunnel["public_url"] ?? ""; if (is_string($url) && str_starts_with($url, "https://")) { echo $url; exit(0); } } foreach ($data["tunnels"] as $tunnel) { $url = $tunnel["public_url"] ?? ""; if (is_string($url) && $url !== "") { echo $url; exit(0); } } exit(1);' 2>/dev/null)"; then
    break
  fi

  sleep 1
done

if [[ -z "$PUBLIC_URL" ]]; then
  echo "Timed out waiting for ngrok to publish a tunnel URL." >&2
  tail -n 40 "$NGROK_LOG" >&2 || true
  exit 1
fi

PUBLIC_URL="${PUBLIC_URL%/}/"

echo "Public URL: ${PUBLIC_URL}"
echo "Starting Lume with that public base URL."

env LUME_PUBLIC_URL="$PUBLIC_URL" php \
  -d upload_max_filesize=220M \
  -d post_max_size=240M \
  -d max_file_uploads=50 \
  -d max_execution_time=300 \
  -d max_input_time=300 \
  -S "${HOST}:${PORT}" -t public public/dev-router.php >>"$SERVER_LOG" 2>&1 &
SERVER_PID=$!

sleep 2

if ! kill -0 "$SERVER_PID" 2>/dev/null; then
  echo "The PHP server exited unexpectedly." >&2
  tail -n 40 "$SERVER_LOG" >&2 || true
  exit 1
fi

echo "Local URL: http://${HOST}:${PORT}/"
echo "ngrok URL: ${PUBLIC_URL}"
echo "Server log: ${SERVER_LOG}"
echo "ngrok log: ${NGROK_LOG}"

wait "$SERVER_PID"
