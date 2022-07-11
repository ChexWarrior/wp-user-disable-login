<?php

require __DIR__.'/../vendor/autoload.php';

// DDEV Local Environment
if (getenv('IS_DDEV_PROJECT') == 'true' && file_exists('/var/www/html/web/wp-config-ddev.php')) {
    include('/var/www/html/web/wp-config-ddev.php');
}

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
