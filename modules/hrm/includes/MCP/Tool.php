<?php
/**
 * WP-ERP HR — one MCP tool.
 */

namespace WeDevs\ERP\HRM\MCP;

use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * One callable tool exposed over MCP.
 *
 * A tool is the unit an AI client discovers and invokes. It owns its schema,
 * the feature group and mode the administrator's access rules are applied
 * to, and the work it performs.
 */
abstract class Tool {

	const MODE_READ   = 'read';
	const MODE_WRITE  = 'write';
	const MODE_DELETE = 'delete';

	/**
	 * The tool's unique identifier, as sent in `tools/call`.
	 *
	 * @return string
	 */
	abstract public function get_name(): string;

	/**
	 * A short human-readable name for display.
	 *
	 * @return string
	 */
	abstract public function get_title(): string;

	/**
	 * What the tool does, written for the model rather than the developer.
	 *
	 * @return string
	 */
	abstract public function get_description(): string;

	/**
	 * JSON Schema for the arguments the tool accepts.
	 *
	 * @return array<string, mixed>
	 */
	abstract public function get_input_schema(): array;

	/**
	 * The feature group the tool belongs to. See Access::groups().
	 *
	 * @return string
	 */
	abstract public function get_group(): string;

	/**
	 * Whether the tool reads, writes or deletes. One of the MODE_* constants.
	 *
	 * @return string
	 */
	abstract public function get_mode(): string;

	/**
	 * Run the tool as the current user.
	 *
	 * @param array<string, mixed> $arguments Validated against the input schema.
	 *
	 * @return array<string, mixed>|WP_Error Structured result, or an error to
	 *                                       report back to the model.
	 */
	abstract public function execute( array $arguments );

	/**
	 * A capability the user must hold for the tool to be listed, if any.
	 *
	 * Only decides what a client is shown. The tool's own execution is what
	 * enforces permissions, so leaving this empty never widens access.
	 *
	 * @return string
	 */
	public function get_capability(): string {
		return '';
	}

	/**
	 * Whether the tool can run on this site right now.
	 *
	 * False when, for instance, the extension behind it is switched off.
	 *
	 * @return bool
	 */
	public function is_available(): bool {
		return true;
	}

	/**
	 * Whether the tool comes from WP ERP Pro.
	 *
	 * @return bool
	 */
	public function is_pro(): bool {
		return false;
	}

	/**
	 * Behavioural hints for the client. Advisory: the server still enforces
	 * everything itself.
	 *
	 * @return array<string, bool>
	 */
	public function get_annotations(): array {
		$read_only = self::MODE_READ === $this->get_mode();

		return [
			'readOnlyHint'    => $read_only,
			'destructiveHint' => self::MODE_DELETE === $this->get_mode(),
			'idempotentHint'  => $read_only,
			'openWorldHint'   => false,
		];
	}

	/**
	 * The tool as it appears in a `tools/list` response.
	 *
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		return [
			'name'        => $this->get_name(),
			'title'       => $this->get_title(),
			'description' => $this->get_description(),
			'inputSchema' => self::wire_schema( $this->get_input_schema() ),
			'annotations' => $this->get_annotations(),
		];
	}

	/**
	 * Prepare a schema for encoding as JSON.
	 *
	 * PHP has one empty array and JSON has two: `'properties' => []` on a
	 * tool that takes no arguments would go out as `"properties": []`, where
	 * JSON Schema wants an object, and a client that validates the catalogue
	 * refuses the whole list over one such tool.
	 *
	 * @param array<string, mixed> $schema A JSON Schema, as PHP arrays.
	 *
	 * @return array<string, mixed>
	 */
	public static function wire_schema( array $schema ): array {
		if ( array_key_exists( 'required', $schema ) && [] === $schema['required'] ) {
			unset( $schema['required'] );
		}

		if ( isset( $schema['properties'] ) && is_array( $schema['properties'] ) ) {
			if ( empty( $schema['properties'] ) ) {
				$schema['properties'] = new \stdClass();
			} else {
				foreach ( $schema['properties'] as $name => $property ) {
					if ( is_array( $property ) ) {
						$schema['properties'][ $name ] = self::wire_schema( $property );
					}
				}
			}
		}

		if ( isset( $schema['items'] ) && is_array( $schema['items'] ) ) {
			$schema['items'] = self::wire_schema( $schema['items'] );
		}

		return $schema;
	}
}
