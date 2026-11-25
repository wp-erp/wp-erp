<?php
/**
 * WP-ERP HR — keeps other plugins' bearer-token checks off the MCP endpoint.
 */

namespace WeDevs\ERP\HRM\MCP;

use WP_Error;
use WP_REST_Request;

defined( 'ABSPATH' ) || exit;

/**
 * Keeps other plugins' bearer-token checks away from the MCP endpoint.
 *
 * A bearer token says nothing about who issued it, so every plugin that
 * accepts one tends to claim all of them. JWT authentication plugins are the
 * common case: they read the Authorization header on every REST request,
 * fail to parse our token as a JWT, and answer 403 before the route is ever
 * dispatched.
 *
 * The transport authenticates every call itself, so nothing is lost by
 * setting such a verdict aside. It is only done for a token carrying our own
 * prefix, on our own route — an error raised for any other reason is left
 * exactly as it was.
 */
class AuthShield {

	/**
	 * Late enough to run after the plugins whose verdict is being set aside.
	 */
	const PRIORITY = 999;

	/**
	 * Hook in.
	 *
	 * @return void
	 */
	public function register(): void {
		add_filter( 'rest_authentication_errors', [ $this, 'authentication_errors' ], self::PRIORITY );
		add_filter( 'rest_pre_dispatch', [ $this, 'pre_dispatch' ], self::PRIORITY, 3 );
	}

	/**
	 * Set aside an authentication failure raised against one of our tokens.
	 *
	 * @param mixed $result Null, true, or the error another plugin raised.
	 *
	 * @return mixed
	 */
	public function authentication_errors( $result ) {
		if ( ! $result instanceof WP_Error ) {
			return $result;
		}

		$route = isset( $GLOBALS['wp']->query_vars['rest_route'] ) ? (string) $GLOBALS['wp']->query_vars['rest_route'] : '';

		return $this->is_ours( $route, $this->header_from_server() ) ? null : $result;
	}

	/**
	 * Set aside a short-circuit raised against one of our tokens.
	 *
	 * @param mixed           $result  Null, or whatever an earlier filter answered with.
	 * @param mixed           $server  The REST server, unused.
	 * @param WP_REST_Request $request The request.
	 *
	 * @return mixed
	 */
	public function pre_dispatch( $result, $server, $request ) {
		if ( ! $result instanceof WP_Error || ! $request instanceof WP_REST_Request ) {
			return $result;
		}

		return $this->is_ours( (string) $request->get_route(), (string) $request->get_header( 'authorization' ) ) ? null : $result;
	}

	/**
	 * Whether a request is for our endpoint and carries one of our tokens.
	 *
	 * @param string $route  The REST route requested.
	 * @param string $header The Authorization header.
	 *
	 * @return bool
	 */
	private function is_ours( string $route, string $header ): bool {
		if ( '/' . Settings::REST_NAMESPACE . Settings::REST_ROUTE !== untrailingslashit( $route ) ) {
			return false;
		}

		return (bool) preg_match( '/^Bearer\s+' . preg_quote( TokenManager::PREFIX, '/' ) . '\S+$/i', trim( $header ) );
	}

	/**
	 * Read the Authorization header before a request object exists.
	 *
	 * @return string
	 */
	private function header_from_server(): string {
		foreach ( [ 'HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION' ] as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				return sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
			}
		}

		return '';
	}
}
