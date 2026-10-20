<?php
/**
 * REST smoke sweep, run with:  wp eval-file smoke-rest.php --user=<admin>
 *
 * Dispatches every GET route in the configured namespaces as an administrator
 * and records the status code. Path parameters are filled with "1". Progress is
 * written after each route so a fatal error only loses that one route: the
 * runner script restarts the sweep and the crashing route is reported.
 *
 * Env: COMPAT_REST_NS (comma separated), COMPAT_STATE (progress file),
 *      COMPAT_REST_OUT (results JSON).
 */

$namespaces = array_filter( array_map( 'trim', explode( ',', getenv( 'COMPAT_REST_NS' ) ?: 'erp/v1' ) ) );
$state_file = getenv( 'COMPAT_STATE' ) ?: sys_get_temp_dir() . '/compat-rest-state.json';
$out_file   = getenv( 'COMPAT_REST_OUT' ) ?: sys_get_temp_dir() . '/compat-rest.json';

$state = file_exists( $state_file ) ? json_decode( file_get_contents( $state_file ), true ) : null;
if ( ! is_array( $state ) ) {
    $state = array( 'done' => array(), 'current' => null, 'crashed' => array() );
}
// If the previous run died mid-route, that route crashed PHP.
if ( ! empty( $state['current'] ) ) {
    $state['crashed'][] = $state['current'];
    $state['done'][ $state['current'] ] = array( 'status' => 'fatal' );
    $state['current'] = null;
}

$server = rest_get_server();
$routes = $server->get_routes();
$todo   = array();

foreach ( $routes as $route => $handlers ) {
    $ns_ok = false;
    foreach ( $namespaces as $ns ) {
        if ( 0 === strpos( ltrim( $route, '/' ), $ns ) ) {
            $ns_ok = true;
        }
    }
    if ( ! $ns_ok ) {
        continue;
    }
    foreach ( $handlers as $handler ) {
        $methods = is_array( $handler['methods'] ) ? array_keys( array_filter( $handler['methods'] ) ) : explode( ',', $handler['methods'] );
        if ( in_array( 'GET', array_map( 'strtoupper', $methods ), true ) ) {
            // Fill named params like (?P<id>[\d]+) with 1.
            $path   = preg_replace( '/\(\?P<[^>]+>[^)]*\)/', '1', $route );
            $path   = preg_replace( '/[\^\$]/', '', $path );
            $todo[] = $path;
            break;
        }
    }
}
$todo = array_values( array_unique( $todo ) );

foreach ( $todo as $path ) {
    if ( isset( $state['done'][ $path ] ) ) {
        continue;
    }
    $state['current'] = $path;
    file_put_contents( $state_file, json_encode( $state ) );

    if ( function_exists( 'compat_capture_set_context' ) ) {
        compat_capture_set_context( 'rest GET ' . $path );
    }
    $t0 = microtime( true );
    try {
        $request  = new WP_REST_Request( 'GET', $path );
        $response = rest_do_request( $request );
        $status   = $response->get_status();
        $code     = '';
        if ( $response->is_error() ) {
            $data = $response->get_data();
            $code = is_array( $data ) && isset( $data['code'] ) ? $data['code'] : '';
        }
    } catch ( Throwable $e ) {
        $status = 'exception';
        $code   = get_class( $e ) . ': ' . $e->getMessage();
        if ( function_exists( 'compat_capture_write' ) ) {
            compat_capture_write( 'fatal', $code, $e->getFile(), $e->getLine() );
        }
    }
    $state['done'][ $path ] = array(
        'status' => $status,
        'code'   => $code,
        'ms'     => (int) round( ( microtime( true ) - $t0 ) * 1000 ),
    );
    $state['current'] = null;
    file_put_contents( $state_file, json_encode( $state ) );
}

file_put_contents(
    $out_file,
    json_encode(
        array(
            'routes'  => $state['done'],
            'crashed' => $state['crashed'],
            'total'   => count( $todo ),
        ),
        JSON_PRETTY_PRINT
    )
);
WP_CLI::success( sprintf( 'REST sweep: %d routes, %d crashed', count( $todo ), count( $state['crashed'] ) ) );
