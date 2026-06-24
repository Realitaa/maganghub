#!/usr/bin/env bash

# helpers.sh - Shared utility functions and color variables
# Must be sourced from other scripts.

# Define ANSI colors
export COLOR_RESET="\033[0m"
export COLOR_RED="\033[0;31m"
export COLOR_GREEN="\033[0;32m"
export COLOR_YELLOW="\033[0;33m"
export COLOR_BLUE="\033[0;34m"
export COLOR_CYAN="\033[0;36m"
export COLOR_BOLD="\033[1m"

# Print a step header
step() {
    echo -e "\n${COLOR_CYAN}${COLOR_BOLD}> 🚀 ${1}${COLOR_RESET}"
}

# Print a success message
success() {
    echo -e "${COLOR_GREEN}${COLOR_BOLD}${1}${COLOR_RESET}"
}

# Print a failure message and exit
fail() {
    echo -e "${COLOR_RED}${COLOR_BOLD}Error: ${1}${COLOR_RESET}" >&2
    exit 1
}

# Error trap handler
trap_error() {
    local parent_lineno="$1"
    local code="${2:-1}"
    fail "Command failed at line ${parent_lineno} with exit status ${code}"
}

ensure_clean() {
    if [ -n "$(git status --porcelain)" ]; then
        echo -e "${COLOR_YELLOW}Repository is dirty.${COLOR_RESET}" >&2
        echo -e "${COLOR_YELLOW}Please commit your changes first.${COLOR_RESET}" >&2
        exit 1
    fi
}
