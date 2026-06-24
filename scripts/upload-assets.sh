#!/usr/bin/env bash

# upload-assets.sh - Mirrors local public/build assets to the remote server using SSH and SCP
set -Eeuo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/helpers.sh"
source "$SCRIPT_DIR/config.sh"

trap 'trap_error $LINENO $?' ERR

# Define local build directory
LOCAL_BUILD_PATH="${ROOT_DIR}/public/build"

# Safety Check: Validate REMOTE_PATH to protect against catastrophic rm -rf
if [[ -z "${REMOTE_PATH:-}" || "${REMOTE_PATH}" == "/" ]]; then
    fail "Invalid REMOTE_PATH. Refusing to continue."
fi

# Sanity Check: Verify local build directory exists
if [ ! -d "$LOCAL_BUILD_PATH" ]; then
    fail "Local build folder does not exist at ${LOCAL_BUILD_PATH}. Please run 'npm run build' first."
fi

# Sanity Check: Verify manifest.json exists inside build directory
if [ ! -f "${LOCAL_BUILD_PATH}/manifest.json" ]; then
    fail "Build folder exists but manifest.json is missing. Please run npm run build first."
fi

# Step 1: Clean remote directory using SSH heredoc block
echo "Deleting and recreating remote build directory..."
ssh "${SSH_OPTS[@]}" "${REMOTE_USER}@${REMOTE_HOST}" "bash -s" "${REMOTE_PATH}" << 'EOF'
    set -Eeuo pipefail
    TARGET_PATH="$1"

    if [[ -z "$TARGET_PATH" || "$TARGET_PATH" == "/" ]]; then
        echo "Invalid REMOTE_PATH on remote. Refusing to continue." >&2
        exit 1
    fi

    rm -rf "${TARGET_PATH}/public/build"
    mkdir -p "${TARGET_PATH}/public/build"
EOF

# Step 2: Upload local build directory using SCP
echo "Uploading assets using SCP..."
# Build SCP options array (SCP uses uppercase -P for port)
SCP_OPTS=(-P "${SSH_PORT}" -o StrictHostKeyChecking=accept-new)
if [ -n "${SSH_KEY:-}" ]; then
    expanded_key="${SSH_KEY/#\~/$HOME}"
    SCP_OPTS+=(-i "${expanded_key}")
fi

scp "${SCP_OPTS[@]}" -r "${LOCAL_BUILD_PATH}" "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}/public/"

# Step 3: Success
success "Frontend assets uploaded successfully."
