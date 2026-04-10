#!/usr/bin/env bash
set -euo pipefail

# Run CodeIgniter dev server with media-friendly upload limits.
php \
  -d upload_max_filesize=220M \
  -d post_max_size=240M \
  -d max_file_uploads=50 \
  -d max_execution_time=300 \
  -d max_input_time=300 \
  -S localhost:8080 -t public public/dev-router.php
