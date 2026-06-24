#!/usr/bin/env bash

# deploy.sh - Main deployment orchestrator
set -Eeuo pipefail

# Sourcing helper and config scripts
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/helpers.sh"
source "$SCRIPT_DIR/config.sh"

# Set up error handling trap
trap 'trap_error $LINENO $?' ERR

# 1. Pastikan repository bersih
step "Ensuring repository is clean"
ensure_clean

# 2. Jalankan formatter
step "Running formatter"
composer format
ensure_clean

# 3. Jalankan quality gate
# step "Running quality gate (composer ci:check)"
# composer ci:check

# 4. Build frontend
step "Building frontend assets"
npm run build
ensure_clean

# 5. Push perubahan
step "Pushing commits to remote repository"
git push origin HEAD

# 6. Jalankan remote-before.sh
step "Executing remote pre-deployment tasks"
bash "$SCRIPT_DIR/remote-before.sh"

# 7. Jalankan upload-assets.sh
step "Uploading compiled frontend assets"
bash "$SCRIPT_DIR/upload-assets.sh"

# 8. Jalankan remote-after.sh
step "Executing remote post-deployment tasks"
bash "$SCRIPT_DIR/remote-after.sh"

# 9. Tampilkan ringkasan sukses
git_commit=$(git rev-parse --short HEAD)
echo -e "\n${COLOR_GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "✅ Deployment Success"
echo -e "Branch : ${REMOTE_BRANCH}"
echo -e "Commit : ${git_commit}"
echo -e "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${COLOR_RESET}\n"
