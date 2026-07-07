#!/usr/bin/env bash
#
# Full front-end build for release. The legacy Vue/webpack bundle and the HRM
# React admin bundle need different Node lines, so each stage switches Node via
# nvm:
#
#   1. Legacy Vue  → Node 12.1.0 (project root .nvmrc): npm run build
#   2. HRM React   → Node 24     (modules/hrm/.nvmrc): delegated to build-react.sh
#
# Wired into the release flow via the root `build:assets` npm script
# (`grunt release` → `run:buildAssets`), so one command produces both bundles on
# the right Node version each. Installs deps when missing (npm ci, falling back to
# npm install), so a fresh clone builds in one command.

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
VUE_NODE=12.1.0

cd "$ROOT"

# Load nvm if available (team standard for switching Node lines).
# nvm refuses to load when npm_config_prefix is set (Homebrew's npm sets it to
# /opt/homebrew), which aborts the release. Clear it before sourcing nvm.
unset npm_config_prefix
if [ -s "${NVM_DIR:-$HOME/.nvm}/nvm.sh" ]; then
	export NVM_DIR="${NVM_DIR:-$HOME/.nvm}"
	# shellcheck disable=SC1091
	. "$NVM_DIR/nvm.sh"
fi

# ── Stage 1: legacy Vue bundle on Node 12.1.0 ───────────────────────────────
if command -v nvm >/dev/null 2>&1; then
	nvm use "$VUE_NODE" >/dev/null 2>&1 || nvm install "$VUE_NODE"
fi

echo "Building legacy Vue bundle on $(node -v) (expected v${VUE_NODE})…"
if [ "$(node -v | sed 's/^v\([0-9]*\).*/\1/')" != "12" ]; then
	echo "  WARNING: the legacy Vue build expects Node ${VUE_NODE}; install nvm + Node ${VUE_NODE} if it fails." >&2
fi

if [ ! -d node_modules ]; then
	echo "  installing dependencies (first run)…"
	if [ -f package-lock.json ]; then npm ci; else npm install; fi
fi

npm run build

# ── Stage 2: HRM React admin bundle on Node 24 ──────────────────────────────
# build-react.sh switches to Node 24 itself and verifies its output.
bash "$ROOT/bin/build-react.sh"

echo "Front-end build complete (Vue + React)."
