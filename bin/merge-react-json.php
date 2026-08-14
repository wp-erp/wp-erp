<?php
/**
 * Consolidate code-split React translation JSON into the registered entry handles.
 *
 * `wp i18n make-json` emits one `erp-<locale>-<md5>.json` per source file it found
 * references for. The HR React app is code-split (webpack chunks), and those chunks
 * are NOT registered WordPress script handles — only the entry bundles
 * (`employees.js`, `hr-globals.js`) are, via `wp_set_script_translations()`. So WP
 * would never load the chunk JSON, and any string living in a chunk stays English.
 *
 * `@wordpress/i18n` locale data is GLOBAL per domain: once the entry handle's JSON is
 * loaded, every chunk's `__()` resolves against it. So we merge every locale's chunk
 * strings into the entry handles' JSON files. Run after `wp i18n make-json`.
 *
 * Usage: php bin/merge-react-json.php <languages-dir>
 *
 * This mirrors Dokan's wp-cli i18n approach (make-pot / make-json); the merge is the
 * one ERP-specific step, because Dokan registers each chunk as a WP handle and ERP
 * loads chunks through webpack's own runtime instead.
 */

if ( PHP_SAPI !== 'cli' ) {
	exit( 1 );
}

$lang_dir = $argv[1] ?? '';
if ( $lang_dir === '' || ! is_dir( $lang_dir ) ) {
	fwrite( STDERR, "usage: php merge-react-json.php <languages-dir>\n" );
	exit( 1 );
}

// Registered entry handles' bundle paths, relative to the plugin root — the md5 of
// these is how WordPress + make-json name the JSON file.
$entry_rel = array(
	'modules/hrm/assets/dist-react/employees.js',
	'modules/hrm/assets/dist-react/hr-globals.js',
);

// Discover the locales present from the emitted JSON file names: erp-<locale>-<md5>.json
$locales = array();
foreach ( glob( $lang_dir . '/erp-*.json' ) as $path ) {
	if ( preg_match( '/^erp-(.+)-[0-9a-f]{32}\.json$/', basename( $path ), $m ) ) {
		$locales[ $m[1] ] = true;
	}
}

$total = 0;
foreach ( array_keys( $locales ) as $locale ) {
	// Gather + merge every messages entry across all this locale's JSON files.
	$merged  = array();
	$header  = null;
	foreach ( glob( $lang_dir . "/erp-{$locale}-*.json" ) as $path ) {
		$data = json_decode( (string) file_get_contents( $path ), true );
		if ( ! is_array( $data ) || empty( $data['locale_data']['messages'] ) ) {
			continue;
		}
		$messages = $data['locale_data']['messages'];
		if ( isset( $messages[''] ) && $header === null ) {
			$header = $messages[''];
		}
		foreach ( $messages as $key => $val ) {
			if ( $key === '' ) {
				continue;
			}
			// Skip empty/untranslated values so a chunk that carries a key with a
			// blank translation can't clobber the real one merged from another chunk
			// (glob order is arbitrary — last-write-wins would otherwise lose it).
			if ( ! is_array( $val ) || ! isset( $val[0] ) || $val[0] === '' ) {
				continue;
			}
			$merged[ $key ] = $val;
		}
	}

	if ( ! $merged ) {
		continue;
	}

	$messages = array( '' => $header ?: array( 'domain' => 'messages', 'lang' => $locale ) ) + $merged;
	$payload  = array(
		'translation-revision-date' => gmdate( 'Y-m-d H:i:sO' ),
		'generator'                 => 'erp:merge-react-json',
		'domain'                    => 'messages',
		'locale_data'               => array( 'messages' => $messages ),
	);
	$json = json_encode( $payload, JSON_UNESCAPED_UNICODE );

	// Write the merged payload to each registered entry handle's JSON file.
	foreach ( $entry_rel as $rel ) {
		$file = $lang_dir . '/erp-' . $locale . '-' . md5( $rel ) . '.json';
		file_put_contents( $file, $json );
	}
	$total += count( $merged );
	fwrite( STDOUT, "merged {$locale}: " . count( $merged ) . " strings -> entry handles\n" );
}

fwrite( STDOUT, "done ({$total} strings across " . count( $locales ) . " locale(s))\n" );
