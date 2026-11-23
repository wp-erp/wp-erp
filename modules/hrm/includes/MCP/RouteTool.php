<?php
/**
 * WP-ERP HR — an MCP tool backed by an `erp/v2` REST route.
 */

namespace WeDevs\ERP\HRM\MCP;

use WP_Error;
use WP_REST_Request;

defined( 'ABSPATH' ) || exit;

/**
 * A tool that runs by dispatching one of the plugin's own REST routes.
 *
 * The HR screens already talk to `erp/v2`, and those routes already carry the
 * validation, sanitisation and permission checks that took the plugin years
 * to get right. Dispatching them internally means a tool can never do
 * something the same user could not do in the HR screens, and that the two
 * cannot drift apart: there is one implementation of "create a leave
 * request", not two.
 *
 * A tool is described by a plain array, so adding one is a few lines:
 *
 *     [
 *         'name'        => 'erp_hr_list_employees',
 *         'title'       => 'List employees',
 *         'description' => 'Search the employee directory…',
 *         'group'       => 'employees',
 *         'mode'        => 'read',              // read | write | delete
 *         'method'      => 'GET',
 *         'route'       => '/erp/v2/employees', // {name} marks a path parameter
 *         'cap'         => 'erp_list_employee', // optional, for listing only
 *         'params'      => [],                  // optional schema additions/overrides
 *         'required'    => [],                  // optional extra required arguments
 *         'omit'        => [],                  // optional route args to hide
 *         'bind'        => [ 'user_id' => '@me' ], // optional fixed values
 *         'pro'         => false,
 *     ]
 *
 * The input schema is read from the arguments the route itself registered,
 * so it stays correct when the route changes. `params` fills in what a route
 * left undeclared.
 */
class RouteTool extends Tool {

	/**
	 * The keys of a REST argument that are also valid JSON Schema.
	 *
	 * @var string[]
	 */
	const SCHEMA_KEYS = [ 'type', 'description', 'enum', 'default', 'minimum', 'maximum', 'items', 'format', 'properties', 'minLength', 'maxLength' ];

	/**
	 * Registered routes keyed by their `{name}` template, built once.
	 *
	 * @var array<string, array<int, array<string, mixed>>>|null
	 */
	private static $routes = null;

	/**
	 * The tool definition.
	 *
	 * @var array<string, mixed>
	 */
	private $definition;

	/**
	 * Constructor.
	 *
	 * @param array<string, mixed> $definition The tool definition.
	 */
	public function __construct( array $definition ) {
		$this->definition = array_merge(
			[
				'name'        => '',
				'title'       => '',
				'description' => '',
				'group'       => '',
				'mode'        => self::MODE_READ,
				'method'      => 'GET',
				'route'       => '',
				'cap'         => '',
				'params'      => [],
				'required'    => [],
				'omit'        => [],
				'bind'        => [],
				'pro'         => false,
			],
			$definition
		);

		$this->definition['method'] = strtoupper( (string) $this->definition['method'] );
	}

	/**
	 * Build tools from a list of definitions.
	 *
	 * @param array<int, array<string, mixed>> $definitions The definitions.
	 *
	 * @return RouteTool[]
	 */
	public static function from_definitions( array $definitions ): array {
		$tools = [];

		foreach ( $definitions as $definition ) {
			if ( is_array( $definition ) && ! empty( $definition['name'] ) && ! empty( $definition['route'] ) ) {
				$tools[] = new self( $definition );
			}
		}

		return $tools;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_name(): string {
		return (string) $this->definition['name'];
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_title(): string {
		return (string) $this->definition['title'];
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_description(): string {
		return (string) $this->definition['description'];
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_group(): string {
		return (string) $this->definition['group'];
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_mode(): string {
		$mode = (string) $this->definition['mode'];

		return isset( Access::MODE_LEVEL[ $mode ] ) ? $mode : self::MODE_DELETE;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_capability(): string {
		return (string) $this->definition['cap'];
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_pro(): bool {
		return ! empty( $this->definition['pro'] );
	}

	/**
	 * Whether the route behind the tool is registered.
	 *
	 * A Pro extension that is switched off registers no routes, so its tools
	 * drop out of the catalogue on their own.
	 *
	 * @return bool
	 */
	public function is_available(): bool {
		return null !== $this->handler();
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_input_schema(): array {
		$handler    = $this->handler();
		$bound      = array_keys( (array) $this->definition['bind'] );
		$omit       = array_merge( (array) $this->definition['omit'], $bound );
		$properties = [];
		$required   = [];

		foreach ( (array) ( $handler['args'] ?? [] ) as $name => $arg ) {
			if ( in_array( $name, $omit, true ) || ! is_array( $arg ) ) {
				continue;
			}

			$properties[ $name ] = array_intersect_key( $arg, array_flip( self::SCHEMA_KEYS ) );

			if ( ! empty( $arg['required'] ) ) {
				$required[] = $name;
			}
		}

		// Path parameters are always needed, whether or not the route
		// troubled to declare them.
		foreach ( $this->path_params() as $name ) {
			if ( in_array( $name, $bound, true ) ) {
				continue;
			}

			if ( ! isset( $properties[ $name ]['type'] ) ) {
				$properties[ $name ]['type'] = 'integer';
			}

			$required[] = $name;
		}

		foreach ( (array) $this->definition['params'] as $name => $param ) {
			if ( ! is_array( $param ) ) {
				continue;
			}

			$properties[ $name ] = array_merge( $properties[ $name ] ?? [], $param );
		}

		foreach ( $properties as $name => $property ) {
			// A REST default of null or an empty list says nothing useful
			// and trips strict schema validators.
			if ( array_key_exists( 'default', $property ) && ( null === $property['default'] || [] === $property['default'] ) ) {
				unset( $properties[ $name ]['default'] );
			}
		}

		$required = array_values( array_unique( array_merge( $required, (array) $this->definition['required'] ) ) );

		return [
			'type'       => 'object',
			'properties' => $properties,
			'required'   => array_values( array_intersect( $required, array_keys( $properties ) ) ),
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function execute( array $arguments ) {
		if ( ! $this->is_available() ) {
			return new WP_Error( 'erp_hr_mcp_unavailable', __( 'This tool is not available on this site.', 'erp' ) );
		}

		// Bound values win over anything the caller sent, so a self-service
		// tool cannot be pointed at somebody else by naming their id.
		foreach ( (array) $this->definition['bind'] as $name => $value ) {
			$arguments[ $name ] = '@me' === $value ? get_current_user_id() : $value;
		}

		$path = (string) $this->definition['route'];

		foreach ( $this->path_params() as $name ) {
			if ( ! isset( $arguments[ $name ] ) || ! is_scalar( $arguments[ $name ] ) || '' === (string) $arguments[ $name ] ) {
				return new WP_Error(
					'erp_hr_mcp_missing_argument',
					/* translators: %s: the argument name. */
					sprintf( __( 'Missing required argument: %s', 'erp' ), $name )
				);
			}

			$path = str_replace( '{' . $name . '}', rawurlencode( (string) $arguments[ $name ] ), $path );
			unset( $arguments[ $name ] );
		}

		$method  = $this->definition['method'];
		$request = new WP_REST_Request( $method, $path );

		if ( in_array( $method, [ 'GET', 'DELETE' ], true ) ) {
			$request->set_query_params( $arguments );
		} else {
			$request->set_header( 'content-type', 'application/json' );
			$request->set_body( (string) wp_json_encode( $arguments ) );
		}

		$response = rest_do_request( $request );

		if ( $response->is_error() ) {
			$error = $response->as_error();

			return new WP_Error( $error->get_error_code(), $this->error_message( $error ) );
		}

		$result  = [ 'data' => rest_get_server()->response_to_data( $response, false ) ];
		$headers = $response->get_headers();

		if ( isset( $headers['X-WP-Total'] ) ) {
			$result['total'] = (int) $headers['X-WP-Total'];
		}

		if ( isset( $headers['X-WP-TotalPages'] ) ) {
			$result['total_pages'] = (int) $headers['X-WP-TotalPages'];
		}

		return $result;
	}

	/**
	 * A REST error as one line the model can act on.
	 *
	 * @param WP_Error $error The error the route returned.
	 *
	 * @return string
	 */
	private function error_message( WP_Error $error ): string {
		$message = $error->get_error_message();
		$data    = $error->get_error_data();

		// Validation failures name the offending arguments in the data,
		// which is exactly what the model needs to correct the call.
		if ( is_array( $data ) && ! empty( $data['params'] ) && is_array( $data['params'] ) ) {
			$message .= ' ' . implode( ' ', array_map( 'strval', $data['params'] ) );
		}

		return wp_strip_all_tags( $message );
	}

	/**
	 * The names of the `{placeholders}` in the route.
	 *
	 * @return string[]
	 */
	private function path_params(): array {
		preg_match_all( '/\{([a-z_]+)\}/', (string) $this->definition['route'], $matches );

		return $matches[1];
	}

	/**
	 * The registered handler for this tool's route and method.
	 *
	 * @return array<string, mixed>|null
	 */
	private function handler(): ?array {
		$routes = self::routes();
		$route  = (string) $this->definition['route'];

		if ( ! isset( $routes[ $route ] ) ) {
			return null;
		}

		foreach ( $routes[ $route ] as $handler ) {
			if ( ! empty( $handler['methods'][ $this->definition['method'] ] ) ) {
				return $handler;
			}
		}

		return null;
	}

	/**
	 * Every registered `erp/` route, keyed by its `{name}` template.
	 *
	 * @return array<string, array<int, array<string, mixed>>>
	 */
	private static function routes(): array {
		if ( null !== self::$routes ) {
			return self::$routes;
		}

		self::$routes = [];

		foreach ( rest_get_server()->get_routes() as $route => $handlers ) {
			if ( 0 !== strpos( $route, '/erp/' ) ) {
				continue;
			}

			// `(?P<user_id>[\d]+)` becomes `{user_id}`. Route patterns here
			// never nest a group inside a named one, so matching up to the
			// first closing parenthesis is enough.
			$template = preg_replace( '/\(\?P<([a-z_]+)>[^)]*\)/', '{$1}', $route );

			self::$routes[ $template ] = $handlers;
		}

		return self::$routes;
	}
}
