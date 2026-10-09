#!/usr/bin/env bash
#
# Generate the React HR app's JS translation JSON from the committed `.po` files,
# then consolidate the code-split chunk strings into the registered entry handles.
#
# Follows Dokan's wp-cli i18n approach (`wp i18n make-pot` / `make-json`). The merge
# step is ERP-specific: the HR React bundle is code-split and the chunks are not
# registered WP handles, so their per-file JSON would never load — see
# bin/merge-react-json.php for the full rationale.
#
# Run after `npm run makepot` and after translating `i18n/languages/erp-<locale>.po`.
#
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LANG_DIR="$ROOT/i18n/languages"

echo "make-json: $LANG_DIR"
wp i18n make-json "$LANG_DIR" --no-purge

echo "consolidating code-split chunks into entry handles..."
php "$ROOT/bin/merge-react-json.php" "$LANG_DIR"
