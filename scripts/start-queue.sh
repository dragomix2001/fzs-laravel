#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

php artisan queue:work redis --sleep=3 --tries=3 --timeout=600 --queue=default
