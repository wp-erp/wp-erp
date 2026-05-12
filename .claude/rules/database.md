# Database rules — WP ERP

## Table naming

- All ERP tables prefixed `{$wpdb->prefix}erp_*` (e.g. `wp_erp_hr_employees`, `wp_erp_crm_contacts`, `wp_erp_ac_journals`).
- Custom tables registered in `WeDevs\ERP\WeDevsERPInstaller` (activation hook) and any module installer.
- `$wpdb->erp_peoplemeta` shortcut set in [wp-erp.php:382](../../wp-erp.php#L382) (`WeDevs_ERP::setup_database`).

## Querying

### Prefer Eloquent

Models live in `modules/*/includes/Models/`. Use them for non-trivial reads/writes:

```php
\WeDevs\ERP\HRM\Models\Employee::where( 'user_id', $user_id )->withTrashed()->forceDelete();
```

Provided by `tareq1988/wp-eloquent` (Composer dep).

### Raw `$wpdb` is OK when

- Aggregations / JOINs Eloquent makes ugly
- One-off admin queries
- The surrounding file already uses raw `$wpdb`

**Always**:
- `$wpdb->prepare()` for any variable in SQL
- `esc_sql()` for table names that vary at runtime
- Treat `WordPress.DB.PreparedSQL.NotPrepared` (warning) as blocking

## Caching pattern

Reads cache, writes invalidate via a "last_changed" group key.

```php
// Read
$last_changed = erp_cache_get_last_changed( 'hrm', 'employee' );
$cache_key    = 'erp-get-employees-' . md5( serialize( $args ) ) . " : $last_changed";
$results      = wp_cache_get( $cache_key, 'erp' );

if ( false === $results ) {
    $results = $wpdb->get_results( $wpdb->prepare( …, … ) );
    wp_cache_set( $cache_key, $results, 'erp' );
}
```

```php
// Write
$wpdb->insert( "{$wpdb->prefix}erp_hr_employees", $data );
erp_cache_set_last_changed( 'hrm', 'employee' );
```

Cache group is always `'erp'`. Helpers live in [includes/functions-cache-helper.php](../../includes/functions-cache-helper.php). The full reference pattern is in [modules/hrm/includes/functions-employee.php](../../modules/hrm/includes/functions-employee.php).

**Forgetting `erp_cache_set_last_changed` after a write is the #1 stale-data bug in this codebase.**

## Migrations / schema changes

- Schema changes go in `WeDevsERPInstaller` and bump a stored version option so `Updates` runs migrators on the next admin request.
- Migrators live under `includes/Updates/`. Each is a file named `update-X.Y.Z.php` with a `function erp_updates_X_Y_Z()`.
- Never change an existing migration after it's been released — write a new one.

## People model

`erp_peoplemeta` and `erp_peoples` are shared by CRM (contacts) and HRM (employees, sort of). Use `erp_get_people()` / `erp_get_peoples()` helpers; don't query the tables directly unless you're writing a new helper.

## Don't

- Don't `DROP` or `TRUNCATE` ERP tables outside the uninstaller.
- Don't change a primary key column type after release.
- Don't store unhashed passwords or secrets — use WP user auth + Application Passwords for API access.
- Don't bypass the cache invalidation pattern "just for one write".
