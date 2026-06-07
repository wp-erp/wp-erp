#!/usr/bin/env bash
#
# Build the HRM React admin bundle for release, on the Node version it pins
# (24 — see `modules/hrm/.nvmrc`), then verify it actually emitted its
# `assets/dist-react` bundle. Wired into the release flow via the root
# `build:react` npm script (`grunt release` → `run:buildReact`), so a bad/missing
# React build aborts the release instead of shipping stale JS alongside the legacy
# Vue bundle that `npm run build` produces.
#
# Installs dependencies automatically when `node_modules` is missing (`npm ci`,
# falling back to `npm install` when there is no lockfile), so a fresh clone builds
# in one command.

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
NODE_VERSION=24
DIR="$ROOT/modules/hrm"
OUT="$DIR/assets/dist-react"

# Switch to the pinned Node line (the React admin needs >= 24). nvm is the team
# standard; honour it when present, otherwise validate the active Node and bail.
if [ -s "${NVM_DIR:-$HOME/.nvm}/nvm.sh" ]; then
	export NVM_DIR="${NVM_DIR:-$HOME/.nvm}"
	# shellcheck disable=SC1091
	. "$NVM_DIR/nvm.sh"
	nvm use "$NODE_VERSION" >/dev/null 2>&1 || nvm install "$NODE_VERSION"
fi

NODE_MAJOR="$(node -v | sed 's/^v\([0-9]*\).*/\1/')"
if [ "$NODE_MAJOR" -lt "$NODE_VERSION" ]; then
	echo "ERROR: the HRM React build needs Node ${NODE_VERSION}+, found $(node -v)." >&2
	echo "       Run 'nvm use ${NODE_VERSION}' (or install Node ${NODE_VERSION}) and retry." >&2
	exit 1
fi

echo "Building HRM React admin on $(node -v)…"

if [ ! -d "$DIR/node_modules" ]; then
	echo "  installing dependencies (first run)…"
	if [ -f "$DIR/package-lock.json" ]; then
		( cd "$DIR" && npm ci )
	else
		( cd "$DIR" && npm install )
	fi
fi

( cd "$DIR" && npm run build )

# Verify the build actually produced a non-empty bundle in dist-react.
if [ -z "$(find "$OUT" -name '*.js' -size +0c 2>/dev/null | head -1)" ]; then
	echo "ERROR: no dist-react/*.js output produced." >&2
	exit 1
fi

echo "HRM React admin built + verified."
