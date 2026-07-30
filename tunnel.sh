#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
TUNNEL_BIN="$ROOT/.tools/cloudflared"

if [[ ! -x "$TUNNEL_BIN" ]]; then
  echo "cloudflared not found at $TUNNEL_BIN"
  echo "Download from: https://github.com/cloudflare/cloudflared/releases"
  exit 1
fi

echo "Starting public tunnel to http://127.0.0.1:8000"
echo "Copy the https://*.trycloudflare.com URL into MPESA_CALLBACK_URL in .env"
echo "Example: MPESA_CALLBACK_URL=https://YOUR-URL.trycloudflare.com/mpesa/callback"
exec "$TUNNEL_BIN" tunnel --url http://127.0.0.1:8000 --no-autoupdate
