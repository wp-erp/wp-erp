---
description: Add a WP-CLI subcommand under `wp erp`
---

# Add a WP-CLI command

Goal: register a new `wp erp …` subcommand for scripted admin / maintenance.

## Inputs
- `$ARGUMENTS` = `<subcommand-name>` e.g. `cache:flush`, `module:list`.

## Procedure

1. Open [includes/cli/commands.php](../../includes/cli/commands.php). New methods live on `WeDevs\ERP\CLI\Commands` (or a sibling class registered via `WP_CLI::add_command`).

2. Each method needs the WP-CLI synopsis docblock pattern:
   ```php
   /**
    * One-line description shown by `wp help erp <subcommand>`.
    *
    * ## OPTIONS
    *
    * <required_arg>
    * : What it is
    *
    * [--optional-flag=<value>]
    * : What it does
    *
    * ## EXAMPLES
    *
    *     wp erp <subcommand> foo
    *     wp erp <subcommand> foo --flag=bar
    *
    * @since X.Y.Z
    */
   public function <subcommand_name>( $args, $assoc_args ) {
       // Use WP_CLI::success / WP_CLI::error / WP_CLI::log — not echo.
   }
   ```

3. Long-running tasks → use `\WP_CLI\Utils\make_progress_bar()` and process in batches.

4. Capability-gated operations should still call `current_user_can()` or document that CLI bypasses (it usually does — confirm in the task).

5. Test from a WP install with this plugin active:
   ```bash
   wp erp <subcommand> --help
   wp erp <subcommand> <args>
   ```

6. Run `./vendor/bin/phpcs includes/cli/commands.php`.
