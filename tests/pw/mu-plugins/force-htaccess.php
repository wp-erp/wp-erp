<?php
/**
 * Plugin Name: Force .htaccess Creation
 * Description: Ensures .htaccess file is created for REST API to work properly
 */

// phpcs:disable
add_action('init', function() {
    // Skip if running in WP-CLI
    if (defined('WP_CLI') && WP_CLI) {
        return;
    }

    // Only run if .htaccess doesn't exist or is empty
    $htaccess_file = ABSPATH . '.htaccess';

    if (!file_exists($htaccess_file) || filesize($htaccess_file) == 0) {
        // Get the permalink structure
        $permalink_structure = get_option('permalink_structure');

        // Only proceed if we have pretty permalinks
        if ($permalink_structure && $permalink_structure !== '') {
            // Force WordPress to regenerate rewrite rules
            flush_rewrite_rules(true);

            // If flush_rewrite_rules didn't create the file, create it manually
            if (!file_exists($htaccess_file) || filesize($htaccess_file) == 0) {
                $htaccess_content = <<<EOD
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
EOD;
                @file_put_contents($htaccess_file, $htaccess_content);
                @chmod($htaccess_file, 0644);
            }
        }
    }
}, 999);
// phpcs:enable
