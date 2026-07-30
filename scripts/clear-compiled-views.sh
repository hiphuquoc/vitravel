#!/bin/bash
# Clear Blade compiled views — avoids touch() EPERM when CLI user ≠ php-fpm (www-data).
set -euo pipefail
cd "$(dirname "$0")/.."
rm -f storage/framework/views/*.php
echo "Cleared storage/framework/views/*.php"
