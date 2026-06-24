#!/usr/bin/env bash

# config.sh - Loads and validates deployment settings from .env.deploy
# Must be sourced from other scripts.

# Resolve project directories
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
ENV_DEPLOY_PATH="$ROOT_DIR/.env.deploy"

# Ensure .env.deploy exists
if [ ! -f "$ENV_DEPLOY_PATH" ]; then
    echo -e "\033[0;31mError: .env.deploy not found in project root ($ROOT_DIR).\033[0m" >&2
    echo -e "\033[0;33mPlease copy scripts/.env.deploy.example to .env.deploy and fill in your settings.\033[0m" >&2
    exit 1
fi

# Load variables from .env.deploy
set -a
source "$ENV_DEPLOY_PATH"
set +a

# Apply defaults
export REMOTE_BRANCH="${REMOTE_BRANCH:-main}"
export SSH_PORT="${SSH_PORT:-22}"

# Validate essential configuration variables
REQUIRED_VARS=(REMOTE_HOST REMOTE_USER REMOTE_PATH)
MISSING_VARS=()

for var in "${REQUIRED_VARS[@]}"; do
    if [ -z "${!var:-}" ]; then
        MISSING_VARS+=("$var")
    fi
done

if [ ${#MISSING_VARS[@]} -ne 0 ]; then
    echo -e "\033[0;31mError: Missing required variables in .env.deploy:\033[0m" >&2
    for var in "${MISSING_VARS[@]}"; do
        echo -e "\033[0;33m  - $var\033[0m" >&2
    done
    exit 1
fi

# Setup SSH connection options
# StrictHostKeyChecking=accept-new avoids hung connections on new hosts while maintaining security
SSH_OPTS=(-p "$SSH_PORT" -o StrictHostKeyChecking=accept-new)
if [ -n "${SSH_KEY:-}" ]; then
    # Expand tilde if present in the path
    expanded_key="${SSH_KEY/#\~/$HOME}"
    SSH_OPTS+=(-i "$expanded_key")
fi
export SSH_OPTS
