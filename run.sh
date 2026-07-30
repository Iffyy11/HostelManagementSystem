#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
export PATH="$HOME/.homebrew/bin:$HOME/.homebrew/sbin:$PATH"
export COMPOSER_DISABLE_PLATFORM_CHECK=1

if ! command -v php &>/dev/null; then
  echo "Waiting for PHP (Homebrew may still be compiling)..."
  for i in {1..120}; do
    if command -v php &>/dev/null; then break; fi
    sleep 15
  done
fi

if ! command -v php &>/dev/null; then
  echo "PHP not ready yet. Finish install with:"
  echo '  eval "$(~/.homebrew/bin/brew shellenv)" && brew install php'
  exit 1
fi

cd "$ROOT"
echo "Using: $(php -v | head -1)"
echo "Starting at http://127.0.0.1:8000"
echo "Login: admin@usiu.ac.ke / password"
exec php artisan serve --host=127.0.0.1 --port=8000
