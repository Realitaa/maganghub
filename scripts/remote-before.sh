#!/usr/bin/env bash

# remote-before.sh - Execute tasks on the remote server before asset upload
set -Eeuo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/helpers.sh"
source "$SCRIPT_DIR/config.sh"

trap 'trap_error $LINENO $?' ERR

echo "Connecting to remote host ${REMOTE_HOST}..."

# Execute commands on remote host via SSH
ssh "${SSH_OPTS[@]}" "${REMOTE_USER}@${REMOTE_HOST}" "bash -s" "${REMOTE_PATH}" "${REMOTE_BRANCH}" << 'EOF'
    set -Eeuo pipefail
    TARGET_PATH="$1"
    TARGET_BRANCH="$2"

    cd "${TARGET_PATH}"

    echo "Taking application offline (maintenance mode)..."
    php artisan down --render="errors::503" || true

    echo "Fetching latest changes..."
    git fetch origin

    echo "Hard resetting to origin/${TARGET_BRANCH}..."
    git reset --hard "origin/${TARGET_BRANCH}"

    echo "Installing Composer dependencies (no-dev, optimized, no-interaction)..."
    composer install \
        --no-dev \
        --prefer-dist \
        --optimize-autoloader \
        --no-interaction
EOF
