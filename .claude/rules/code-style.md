# Code style — WP ERP

Enforced by [phpcs.xml](../../phpcs.xml) (WordPress Coding Standards) and [.editorconfig](../../.editorconfig). When in doubt, run `composer phpcs` and let the sniffs decide.

## PHP

- **Indent**: 4 spaces (never tabs).
- **Open tag**: `<?php` only, no closing tag in pure-PHP files.
- **Top of every file**:
  ```php
  <?php
  // don't call the file directly
  if ( ! defined( 'ABSPATH' ) ) {
      exit;
  }
  ```
- **Spaces inside parens** — `foo( $bar )`, not `foo($bar)`. Enforced by `PEAR.Functions.FunctionCallSignature` (requiredSpacesAfterOpen/BeforeClose = 1).
- **Namespaces** PSR-4 under `WeDevs\ERP\…`. New files must declare a `namespace` matching the autoload map in [composer.json](../../composer.json).
- **Function naming**:
  - Free functions are snake_case prefixed:
    - core: `erp_*`
    - HRM: `erp_hr_*`
    - CRM: `erp_crm_*`
    - Accounting: `erp_ac_*`
  - Class methods: snake_case (WP convention — `Generic.NamingConventions.ValidFunctionName` disabled for methods).
- **Class naming**: `PascalCase` matching filename (`Employee.php` → `class Employee`).
- **Arrays**: short syntax `[]`, not `array()`. Associative arrays don't need aligned `=>`.
- **Strict comparisons** — `WordPress.PHP.StrictInArray.MissingTrueStrict` is an **error** (always pass `true` as third arg to `in_array`).
- **Translatable strings** — every literal user-facing string uses `__()`, `_e()`, `esc_html__()`, `esc_attr__()`, etc., with `'erp'` as the text domain. phpcs enforces this.
- **No Yoda conditions required** — `WordPress.PHP.YodaConditions.NotYoda` is disabled. Use whichever reads better.
- **DocBlocks**: not strictly required (`Squiz.Commenting` disabled), but match the surrounding file. Long functions deserve a short `@since` + `@return`.

## JS / Vue

Per [eslintrc.js](../../eslintrc.js):

- `standard` + `plugin:vue/essential`
- 4-space indent, semicolons **required**, no space before function paren
- camelCase off (project mixes snake_case from PHP-side payloads)

## CSS / LESS / SCSS

- 4 spaces (`.editorconfig` default), kebab-case class names
- Vue SFC `<style>` blocks: scoped where local, global only when intentional

## File organization

- **Where to add code**:
  - Cross-module helpers → `includes/functions-*.php` (already split by topic — `functions-cache-helper.php`, `functions-people.php`, etc.)
  - Module-specific helpers → `modules/<m>/includes/functions-*.php` (same per-topic split)
  - Classes → `includes/<Topic>/<ClassName>.php` or `modules/<m>/includes/<ClassName>.php`
  - Eloquent models → `modules/<m>/includes/Models/<ModelName>.php`
  - REST controllers → `includes/API/` (core) or `modules/<m>/includes/Admin/API/` (module)
- Never add a new top-level directory without discussing it.

## Don't

- Don't introduce new function prefixes — stick with `erp_`, `erp_hr_`, `erp_crm_`, `erp_ac_`.
- Don't "fix" intentional typos in capability names (`erp_crate_announcement`, `erp_crm_manage_activites`).
- Don't add `use` imports for classes you only mention in a docblock — phpcs may flag, and they bloat the autoloader's hot path.
- Don't reformat unrelated code in a fix PR. Run `composer phpcbf` on touched files only.
