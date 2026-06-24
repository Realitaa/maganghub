#!/usr/bin/env bash

# remote-after.sh - Execute tasks on the remote server after assets are uploaded
set -Eeuo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/helpers.sh"
source "$SCRIPT_DIR/config.sh"

trap 'trap_error $LINENO $?' ERR

echo "Connecting to remote host ${REMOTE_HOST}..."

# Execute commands on remote host via SSH
ssh "${SSH_OPTS[@]}" "${REMOTE_USER}@${REMOTE_HOST}" "bash -s" "${REMOTE_PATH}" << 'EOF'
    set -Eeuo pipefail
    TARGET_PATH="$1"

    cd "${TARGET_PATH}"

    echo "Running database migrations..."
    php artisan migrate --force

    echo "Optimizing application cache..."
    php artisan optimize

    echo "Restarting queue workers..."
    php artisan queue:restart || true

    echo "Restarting Horizon..."
    php artisan horizon:terminate || true

    echo "Taking application online (maintenance mode disabled)..."
    php artisan up || true
EOF
