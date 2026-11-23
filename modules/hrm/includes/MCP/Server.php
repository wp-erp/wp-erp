<?php
/**
 * WP-ERP HR — the MCP protocol handler.
 */

namespace WeDevs\ERP\HRM\MCP;

use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Turns a decoded JSON-RPC message into a JSON-RPC reply.
 *
 * It knows nothing about HTTP — the transport hands it a message and a
 * resolved user, and takes back an array to encode.
 */
class Server {

	/**
	 * The protocol revision this server implements.
	 */
	const PROTOCOL_VERSION = '2025-06-18';

	/**
	 * Revisions we can still speak, newest first.
	 *
	 * @var string[]
	 */
	const SUPPORTED_VERSIONS = [ '2025-06-18', '2025-03-26', '2024-11-05' ];

	// JSON-RPC error codes. Parse errors are raised by the transport, which
	// is the only layer that sees the raw body.
	const INVALID_REQUEST  = -32600;
	const METHOD_NOT_FOUND = -32601;
	const INVALID_PARAMS   = -32602;

	/**
	 * The tool catalogue.
	 *
	 * @var ToolRegistry
	 */
	private $registry;

	/**
	 * Records what each tool call did.
	 *
	 * @var AuditLog
	 */
	private $audit;

	/**
	 * Constructor.
	 *
	 * @param ToolRegistry $registry The tool catalogue.
	 * @param AuditLog     $audit    The audit log.
	 */
	public function __construct( ToolRegistry $registry, AuditLog $audit ) {
		$this->registry = $registry;
		$this->audit    = $audit;
	}

	/**
	 * Handle one JSON-RPC message.
	 *
	 * @param array<string, mixed> $message  The decoded message.
	 * @param int                  $user_id  The user the caller authenticated as.
	 * @param int                  $token_id The token used, for the audit trail.
	 * @param string               $scope    The token's scope: `full` or `read`.
	 *
	 * @return array<string, mixed>|null The reply, or null for a notification,
	 *                                   which takes no response.
	 */
	public function handle( array $message, int $user_id, int $token_id, string $scope = 'full' ): ?array {
		if ( ! isset( $message['jsonrpc'] ) || '2.0' !== $message['jsonrpc'] ) {
			return $this->error( $message['id'] ?? null, self::INVALID_REQUEST, __( 'Invalid JSON-RPC version.', 'erp' ) );
		}

		// Notifications carry no id and expect no reply.
		if ( ! isset( $message['id'] ) ) {
			return null;
		}

		$id     = $message['id'];
		$method = isset( $message['method'] ) && is_string( $message['method'] ) ? $message['method'] : '';
		$params = isset( $message['params'] ) && is_array( $message['params'] ) ? $message['params'] : [];

		switch ( $method ) {
			case 'initialize':
				return $this->initialize( $id, $params );

			case 'ping':
				return $this->result( $id, new \stdClass() );

			case 'tools/list':
				return $this->list_tools( $id, $user_id, $scope );

			case 'tools/call':
				return $this->call_tool( $id, $params, $user_id, $token_id, $scope );

			default:
				return $this->error(
					$id,
					self::METHOD_NOT_FOUND,
					/* translators: %s: the JSON-RPC method name. */
					sprintf( __( 'Method not found: %s', 'erp' ), $method )
				);
		}
	}

	/**
	 * Answer the handshake.
	 *
	 * @param mixed                $id     The request id.
	 * @param array<string, mixed> $params The request params.
	 *
	 * @return array<string, mixed>
	 */
	private function initialize( $id, array $params ): array {
		$requested = $params['protocolVersion'] ?? '';

		// Echo the client's version when we speak it, otherwise offer ours
		// and let the client decide whether it can continue.
		$version = in_array( $requested, self::SUPPORTED_VERSIONS, true ) ? $requested : self::PROTOCOL_VERSION;

		return $this->result(
			$id,
			[
				'protocolVersion' => $version,
				'capabilities'    => [
					'tools' => [ 'listChanged' => false ],
				],
				'serverInfo'      => [
					'name'    => 'wp-erp-hr',
					'title'   => 'WP ERP HR',
					'version' => defined( 'WPERP_VERSION' ) ? WPERP_VERSION : '1.0.0',
				],
				'instructions'    => __(
					'Human resources data for this WordPress site, from WP ERP: employees, departments, leave, announcements, reports and, where WP ERP Pro is active, attendance, payroll, recruitment and more. Every call runs as the WordPress user the access token belongs to, limited to what that user may do in the HR screens and to the features the site administrator has opened to them over MCP — so the tool list differs from person to person. Employees are identified by their WordPress user id (`user_id`). Look a record up before changing it, and search before creating so duplicates are not introduced. Dates are YYYY-MM-DD.',
					'erp'
				),
			]
		);
	}

	/**
	 * List the tools this user may call.
	 *
	 * @param mixed  $id      The request id.
	 * @param int    $user_id The calling user.
	 * @param string $scope   The token's scope.
	 *
	 * @return array<string, mixed>
	 */
	private function list_tools( $id, int $user_id, string $scope ): array {
		$tools = [];

		foreach ( $this->registry->available_for( $user_id, $scope ) as $tool ) {
			$tools[] = $tool->to_array();
		}

		return $this->result( $id, [ 'tools' => $tools ] );
	}

	/**
	 * Run a tool and shape its result.
	 *
	 * @param mixed                $id       The request id.
	 * @param array<string, mixed> $params   The request params, straight off the
	 *                                       wire and so of no guaranteed shape.
	 * @param int                  $user_id  The calling user.
	 * @param int                  $token_id The token used.
	 * @param string               $scope    The token's scope.
	 *
	 * @return array<string, mixed>
	 */
	private function call_tool( $id, array $params, int $user_id, int $token_id, string $scope ): array {
		$name      = isset( $params['name'] ) && is_string( $params['name'] ) ? $params['name'] : '';
		$arguments = $params['arguments'] ?? [];

		if ( ! is_array( $arguments ) ) {
			return $this->error( $id, self::INVALID_PARAMS, __( 'Tool arguments must be an object.', 'erp' ) );
		}

		$available = $this->registry->available_for( $user_id, $scope );

		// An unavailable tool is reported the same way whether it does not
		// exist, is switched off, or is beyond what this user was granted.
		// The model has no business learning which.
		if ( ! isset( $available[ $name ] ) ) {
			// The administrator does. A refusal is written down for a tool
			// that exists. A name matching no tool at all is not logged: it
			// is caller-controlled text, and a row for every made-up name
			// would let a caller fill the log with anything it liked.
			$known = $this->registry->get( $name );

			if ( $known ) {
				$this->audit->record(
					$token_id,
					$user_id,
					$known->get_name(),
					$arguments,
					'refused',
					$this->registry->refusal_reason( $known, $user_id, $scope )
				);
			}

			return $this->error(
				$id,
				self::INVALID_PARAMS,
				/* translators: %s: the tool name. */
				sprintf( __( 'Unknown tool: %s', 'erp' ), $name )
			);
		}

		$tool       = $available[ $name ];
		$validation = $this->validate_arguments( $tool->get_input_schema(), $arguments );

		if ( is_wp_error( $validation ) ) {
			return $this->tool_error( $id, $validation->get_error_message() );
		}

		$result = $tool->execute( $arguments );

		$this->audit->record(
			$token_id,
			$user_id,
			$name,
			$arguments,
			is_wp_error( $result ) ? 'error' : 'success',
			is_wp_error( $result ) ? $result->get_error_message() : null
		);

		if ( is_wp_error( $result ) ) {
			// A tool that fails on its own terms reports through the result,
			// not a protocol error, so the model can read what went wrong
			// and try something else.
			return $this->tool_error( $id, $result->get_error_message() );
		}

		return $this->result(
			$id,
			[
				'content'           => [
					[
						'type' => 'text',
						'text' => (string) wp_json_encode( $result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ),
					],
				],
				'structuredContent' => empty( $result ) ? new \stdClass() : $result,
				'isError'           => false,
			]
		);
	}

	/**
	 * Check arguments against a tool's schema.
	 *
	 * Deliberately narrow: required keys, types, and enum membership. The
	 * tool still validates and sanitises everything it uses, so this exists
	 * to give the model a useful message rather than to be the security
	 * boundary.
	 *
	 * @param array<string, mixed> $schema    The tool's input schema.
	 * @param array<string, mixed> $arguments The supplied arguments.
	 *
	 * @return true|WP_Error
	 */
	private function validate_arguments( array $schema, array $arguments ) {
		$properties = $schema['properties'] ?? [];

		foreach ( (array) ( $schema['required'] ?? [] ) as $key ) {
			if ( ! array_key_exists( $key, $arguments ) || '' === $arguments[ $key ] || null === $arguments[ $key ] ) {
				return new WP_Error(
					'erp_hr_mcp_missing_argument',
					/* translators: %s: the argument name. */
					sprintf( __( 'Missing required argument: %s', 'erp' ), $key )
				);
			}
		}

		foreach ( $arguments as $key => $value ) {
			if ( ! isset( $properties[ $key ] ) || ! is_array( $properties[ $key ] ) ) {
				continue;
			}

			$expected = $properties[ $key ]['type'] ?? null;

			if ( $expected && ! $this->matches_type( $value, (array) $expected ) ) {
				return new WP_Error(
					'erp_hr_mcp_invalid_argument',
					sprintf(
						/* translators: 1: argument name, 2: expected JSON Schema type. */
						__( 'Argument %1$s must be of type %2$s.', 'erp' ),
						$key,
						implode( ' or ', (array) $expected )
					)
				);
			}

			$allowed = $properties[ $key ]['enum'] ?? null;

			// Loose on purpose: a route that lists its statuses as 1, 2, 3
			// accepts "1" as well, and so should this.
			if ( is_array( $allowed ) && is_scalar( $value ) && ! in_array( (string) $value, array_map( 'strval', $allowed ), true ) ) {
				return new WP_Error(
					'erp_hr_mcp_invalid_argument',
					sprintf(
						/* translators: 1: argument name, 2: comma-separated allowed values. */
						__( 'Argument %1$s must be one of: %2$s', 'erp' ),
						$key,
						implode( ', ', $allowed )
					)
				);
			}
		}

		return true;
	}

	/**
	 * Whether a value satisfies any of the given JSON Schema types.
	 *
	 * @param mixed    $value The value.
	 * @param string[] $types The JSON Schema type names.
	 *
	 * @return bool
	 */
	private function matches_type( $value, array $types ): bool {
		foreach ( $types as $type ) {
			switch ( $type ) {
				case 'string':
					$matches = is_string( $value );
					break;

				case 'integer':
					// JSON has one number type, so an integral float counts.
					$matches = is_int( $value ) || ( is_float( $value ) && floor( $value ) === $value );
					break;

				case 'number':
					$matches = is_int( $value ) || is_float( $value );
					break;

				case 'boolean':
					$matches = is_bool( $value );
					break;

				case 'array':
				case 'object':
					$matches = is_array( $value );
					break;

				case 'null':
					$matches = null === $value;
					break;

				default:
					$matches = true;
			}

			if ( $matches ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * A successful JSON-RPC reply.
	 *
	 * @param mixed $id     The request id.
	 * @param mixed $result The result payload.
	 *
	 * @return array<string, mixed>
	 */
	private function result( $id, $result ): array {
		return [
			'jsonrpc' => '2.0',
			'id'      => $id,
			'result'  => $result,
		];
	}

	/**
	 * A JSON-RPC protocol error.
	 *
	 * @param mixed  $id      The request id.
	 * @param int    $code    The JSON-RPC error code.
	 * @param string $message The error message.
	 *
	 * @return array<string, mixed>
	 */
	private function error( $id, int $code, string $message ): array {
		return [
			'jsonrpc' => '2.0',
			'id'      => $id,
			'error'   => [
				'code'    => $code,
				'message' => $message,
			],
		];
	}

	/**
	 * A tool failure, reported inside a successful response.
	 *
	 * @param mixed  $id      The request id.
	 * @param string $message What went wrong.
	 *
	 * @return array<string, mixed>
	 */
	private function tool_error( $id, string $message ): array {
		return $this->result(
			$id,
			[
				'content' => [
					[
						'type' => 'text',
						'text' => $message,
					],
				],
				'isError' => true,
			]
		);
	}
}
