<?php
/**
 * Reports only the findings a pull request introduces.
 *
 * The code base carries thousands of findings from before these checks existed.
 * Failing a pull request for them teaches everyone to ignore the check, so this
 * keeps a finding only when it sits on a line the pull request adds or changes.
 * A finding about a whole file (line 0) is kept only for a file the pull
 * request adds.
 *
 * Usage: php changed-lines-report.php <phpcs|plugin-check> <report file> <base ref> [<head ref>]
 *
 * - phpcs:        a `phpcs --report=json` file.
 * - plugin-check: the output of `wp plugin check --format=json`.
 *
 * Prints GitHub annotations and exits 1 when a kept finding is an error.
 */

if ( 'cli' !== PHP_SAPI ) {
	exit( 1 );
}

if ( $argc < 4 ) {
	fwrite( STDERR, "Usage: php changed-lines-report.php <phpcs|plugin-check> <report file> <base ref> [<head ref>]\n" );
	exit( 2 );
}

$format = $argv[1];
$report = $argv[2];
$base   = $argv[3];
$head   = isset( $argv[4] ) ? $argv[4] : 'HEAD';

/**
 * Lines each file gains between the merge base and the head.
 *
 * @param string $base Base ref.
 * @param string $head Head ref.
 *
 * @return array [ path => [ 'new' => bool, 'lines' => [ line => true ] ] ]
 */
function changed_lines( $base, $head ) {
	$command = sprintf( 'git diff -U0 --no-color --diff-filter=AM %s...%s', escapeshellarg( $base ), escapeshellarg( $head ) );
	$output  = [];
	$status  = 0;

	exec( $command, $output, $status );

	if ( 0 !== $status ) {
		fwrite( STDERR, "Could not read the diff against {$base}.\n" );
		exit( 2 );
	}

	$files   = [];
	$current = null;
	$is_new  = false;

	foreach ( $output as $line ) {
		if ( 0 === strpos( $line, '--- ' ) ) {
			$is_new = ( '--- /dev/null' === $line );
			continue;
		}

		if ( 0 === strpos( $line, '+++ b/' ) ) {
			$current           = substr( $line, 6 );
			$files[ $current ] = [
				'new'   => $is_new,
				'lines' => [],
			];
			continue;
		}

		if ( null !== $current && preg_match( '/^@@ -\d+(?:,\d+)? \+(\d+)(?:,(\d+))? @@/', $line, $match ) ) {
			$start = (int) $match[1];
			$count = isset( $match[2] ) ? (int) $match[2] : 1;

			for ( $i = 0; $i < $count; $i++ ) {
				$files[ $current ]['lines'][ $start + $i ] = true;
			}
		}
	}

	return $files;
}

/**
 * Findings of a `phpcs --report=json` file.
 *
 * @param string $report Report path.
 *
 * @return array [ [ file, line, type, code, message ] ]
 */
function phpcs_findings( $report ) {
	$data     = json_decode( (string) file_get_contents( $report ), true );
	$findings = [];

	if ( empty( $data['files'] ) ) {
		return $findings;
	}

	foreach ( $data['files'] as $file => $result ) {
		foreach ( $result['messages'] as $message ) {
			$findings[] = [
				'file'    => ltrim( str_replace( '\\', '/', $file ), './' ),
				'line'    => (int) $message['line'],
				'type'    => strtoupper( $message['type'] ),
				'code'    => $message['source'],
				'message' => $message['message'],
			];
		}
	}

	return $findings;
}

/**
 * Findings of `wp plugin check --format=json`: a `FILE: <path>` line, then a
 * JSON list of that file's findings.
 *
 * @param string $report Report path.
 *
 * @return array [ [ file, line, type, code, message ] ]
 */
function plugin_check_findings( $report ) {
	$findings = [];
	$file     = null;

	foreach ( file( $report, FILE_IGNORE_NEW_LINES ) as $line ) {
		if ( 0 === strpos( $line, 'FILE: ' ) ) {
			$file = trim( substr( $line, 6 ) );
			continue;
		}

		if ( null === $file || '' === $line || '[' !== $line[0] ) {
			continue;
		}

		$messages = json_decode( $line, true );

		if ( ! is_array( $messages ) ) {
			continue;
		}

		foreach ( $messages as $message ) {
			$findings[] = [
				'file'    => $file,
				'line'    => (int) $message['line'],
				'type'    => strtoupper( $message['type'] ),
				'code'    => $message['code'],
				'message' => html_entity_decode( $message['message'], ENT_QUOTES ),
			];
		}

		$file = null;
	}

	return $findings;
}

/**
 * Escapes a value for a GitHub workflow command.
 *
 * @param string $value    Value.
 * @param bool   $property Whether it is a property (`file=`, `title=`) rather than the message.
 *
 * @return string
 */
function annotation_escape( $value, $property = false ) {
	$value = str_replace( [ '%', "\r", "\n" ], [ '%25', '%0D', '%0A' ], $value );

	return $property ? str_replace( [ ':', ',' ], [ '%3A', '%2C' ], $value ) : $value;
}

$changed  = changed_lines( $base, $head );
$findings = 'plugin-check' === $format ? plugin_check_findings( $report ) : phpcs_findings( $report );
$errors   = 0;
$warnings = 0;
$skipped  = 0;

foreach ( $findings as $finding ) {
	$file = $finding['file'];

	$introduced = isset( $changed[ $file ] ) && (
		$finding['line'] > 0
			? isset( $changed[ $file ]['lines'][ $finding['line'] ] )
			: $changed[ $file ]['new']
	);

	if ( ! $introduced ) {
		$skipped++;
		continue;
	}

	$is_error = 'ERROR' === $finding['type'];

	if ( $is_error ) {
		$errors++;
	} else {
		$warnings++;
	}

	printf(
		"::%s file=%s,line=%d,title=%s::%s\n",
		$is_error ? 'error' : 'warning',
		annotation_escape( $file, true ),
		max( 1, $finding['line'] ),
		annotation_escape( $finding['code'], true ),
		annotation_escape( $finding['message'] )
	);

	// Annotations are capped per step, so the log keeps the full list.
	printf( "%s %s:%d %s\n    %s\n", $finding['type'], $file, $finding['line'], $finding['code'], $finding['message'] );
}

printf(
	"\n%d error(s) and %d warning(s) on the lines this pull request changes. %d finding(s) in code it does not touch were left out.\n",
	$errors,
	$warnings,
	$skipped
);

exit( $errors > 0 ? 1 : 0 );
