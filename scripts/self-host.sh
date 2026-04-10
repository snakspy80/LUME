#!/usr/bin/env bash
set -euo pipefail

HOST="${LUME_HOST:-0.0.0.0}"
PORT="${LUME_PORT:-8080}"

echo "Starting Lume on http://${HOST}:${PORT}"
echo "If you are sharing on your local network, use your PC's LAN IP with port ${PORT}."

php \
  -d upload_max_filesize=220M \
  -d post_max_size=240M \
  -d max_file_uploads=50 \
  -d max_execution_time=300 \
  -d max_input_time=300 \
  -S "${HOST}:${PORT}" -t public public/dev-router.php
