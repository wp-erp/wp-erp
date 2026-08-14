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
# nvm refuses to load when npm_config_prefix is set (Homebrew's npm sets it to
# /opt/homebrew), which aborts the release. Clear it before sourcing nvm.
unset npm_config_prefix
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

# React deps are managed with pnpm (Node 24). pnpm installs atomically into the
# module's own node_modules, so a partial/aborted tree can't make `wp-scripts`
# resolve a stray webpack-cli from a parent node_modules. We still gate on the
# build binary so an incomplete tree re-installs rather than building broken.
# First run auto-imports the existing package-lock.json into pnpm-lock.yaml, so
# pinned versions are preserved. node-linker=hoisted (.npmrc) keeps a flat
# node_modules that the wp-scripts/webpack toolchain reads exactly like npm did.
if ! command -v pnpm >/dev/null 2>&1; then
	echo "ERROR: pnpm not found. Install it (brew install pnpm) and retry." >&2
	exit 1
fi

if [ ! -x "$DIR/node_modules/.bin/wp-scripts" ]; then
	echo "  installing dependencies with pnpm (missing or incomplete)…"
	( cd "$DIR" && pnpm install )
fi

( cd "$DIR" && pnpm run build )

# Verify the build actually produced a non-empty bundle in dist-react.
if [ -z "$(find "$OUT" -name '*.js' -size +0c 2>/dev/null | head -1)" ]; then
	echo "ERROR: no dist-react/*.js output produced." >&2
	exit 1
fi

echo "HRM React admin built + verified."
