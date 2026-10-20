<?php
/**
 * Plugin Name: Release Agent – Compat Capture
 * Description: Test-only mu-plugin. Records every PHP notice, warning, deprecation
 *              and fatal raised while WordPress runs, with the request or smoke step
 *              that triggered it. Never ship this in a plugin build.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$GLOBALS['compat_capture'] = array(
    'log'     => getenv( 'COMPAT_LOG' ) ?: WP_CONTENT_DIR . '/compat-errors.jsonl',
    'context' => ( PHP_SAPI === 'cli' ? 'cli' : 'http' ) . ' ' . ( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : implode( ' ', array_slice( (array) ( $GLOBALS['argv'] ?? array() ), 1, 4 ) ) ),
    'seen'    => array(),
);

function compat_capture_set_context( $context ) {
    $GLOBALS['compat_capture']['context'] = $context;
}

function compat_capture_type( $errno ) {
    switch ( $errno ) {
        case E_DEPRECATED:
        case E_USER_DEPRECATED:
            return 'deprecated';
        case E_NOTICE:
        case E_USER_NOTICE:
            return 'notice';
        case E_WARNING:
        case E_USER_WARNING:
        case E_CORE_WARNING:
        case E_COMPILE_WARNING:
            return 'warning';
        case E_ERROR:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_PARSE:
            return 'fatal';
        default:
            return 'error';
    }
}

function compat_capture_write( $type, $message, $file, $line ) {
    $key = md5( $type . $message . $file . $line );
    if ( isset( $GLOBALS['compat_capture']['seen'][ $key ] ) ) {
        return;
    }
    $GLOBALS['compat_capture']['seen'][ $key ] = true;

    $root = defined( 'ABSPATH' ) ? ABSPATH : '';
    $row  = array(
        'type'    => $type,
        'message' => substr( $root ? str_replace( $root, '', (string) $message ) : (string) $message, 0, 1000 ),
        'file'    => $root ? str_replace( $root, '', (string) $file ) : (string) $file,
        'line'    => (int) $line,
        'context' => $GLOBALS['compat_capture']['context'],
        'php'     => PHP_VERSION,
        'wp'      => isset( $GLOBALS['wp_version'] ) ? $GLOBALS['wp_version'] : '',
    );
    @file_put_contents( $GLOBALS['compat_capture']['log'], json_encode( $row ) . "\n", FILE_APPEND | LOCK_EX );
}

set_error_handler(
    function ( $errno, $errstr, $errfile, $errline ) {
        compat_capture_write( compat_capture_type( $errno ), $errstr, $errfile, $errline );
        return false; // let WordPress/PHP continue normally
    },
    E_ALL
);

set_exception_handler(
    function ( $e ) {
        compat_capture_write( 'fatal', get_class( $e ) . ': ' . $e->getMessage(), $e->getFile(), $e->getLine() );
    }
);

register_shutdown_function(
    function () {
        $err = error_get_last();
        if ( $err && in_array( $err['type'], array( E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE ), true ) ) {
            compat_capture_write( 'fatal', $err['message'], $err['file'], $err['line'] );
        }
    }
);

// WordPress's own _deprecated_function() & friends trigger E_USER_DEPRECATED only
// when WP_DEBUG is on; also record them directly with the caller.
foreach ( array( 'deprecated_function_run', 'deprecated_hook_run', 'deprecated_argument_run', 'doing_it_wrong_run' ) as $compat_hook ) {
    add_action(
        $compat_hook,
        function ( $name, $replacement = '', $version = '' ) use ( $compat_hook ) {
            $caller = '';
            $line   = 0;
            foreach ( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) as $frame ) {
                if ( ! empty( $frame['file'] ) && false !== strpos( $frame['file'], '/plugins/' ) ) {
                    $caller = $frame['file'];
                    $line   = $frame['line'];
                    break;
                }
            }
            $type = 'doing_it_wrong_run' === $compat_hook ? 'doing_it_wrong' : 'deprecated';
            compat_capture_write( $type, "{$compat_hook}: {$name} (since {$version}) " . ( is_string( $replacement ) ? $replacement : '' ), $caller, $line );
        },
        10,
        3
    );
}

// Admin page discovery for the smoke test: /wp-admin/?compat_list_pages=1
add_action(
    'admin_menu',
    function () {
        if ( empty( $_GET['compat_list_pages'] ) ) {
            return;
        }
        global $menu, $submenu;
        $prefix = getenv( 'COMPAT_PAGE_PREFIX' ) ?: 'erp';
        $pages  = array();
        foreach ( (array) $menu as $item ) {
            if ( ! empty( $item[2] ) && 0 === strpos( $item[2], $prefix ) ) {
                $pages[] = $item[2];
            }
        }
        foreach ( (array) $submenu as $parent => $items ) {
            foreach ( $items as $item ) {
                if ( ! empty( $item[2] ) && ( 0 === strpos( $item[2], $prefix ) || 0 === strpos( $parent, $prefix ) ) ) {
                    $pages[] = $item[2];
                }
            }
        }
        wp_send_json( array_values( array_unique( $pages ) ) );
    },
    PHP_INT_MAX
);
