#!/usr/bin/env node
/**
 * Release gate: prove a built Tailwind stylesheet was generated from ALL of the
 * sources its `@source` lines name.
 *
 * Tailwind prints nothing when an `@source` path does not resolve or matches no
 * files. The build still exits 0 and ships a stylesheet without that source's
 * classes, so whatever it renders (the design-system components, the shared
 * package) comes out unstyled. Nothing else in the build would notice.
 *
 * For every `@source` in the given entry stylesheet this checks that:
 *   1. the path exists and holds at least one scannable file, and
 *   2. the plain utilities that ONLY that source uses are present in the built
 *      CSS. A source whose own classes are missing was not scanned.
 *
 * Usage: node bin/check-react-css.mjs <entry main.css> <built.css> [<built.css>…]
 * Exits 1 with the reason on failure.
 */

import { existsSync, readdirSync, readFileSync, statSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';

const [ entry, ...builtFiles ] = process.argv.slice( 2 );

if ( ! entry || builtFiles.length === 0 ) {
	console.error( 'Usage: check-react-css.mjs <entry main.css> <built.css> [<built.css>…]' );
	process.exit( 1 );
}

const fail = ( message ) => {
	console.error( `ERROR: ${ message }` );
	process.exit( 1 );
};

// Share of a source's own utilities that must be in the built CSS. Below 100
// because a class-shaped string in a bundle is not always a class.
const REQUIRED_SHARE = 0.9;

// Plain, always-generated utilities. Multi-letter prefixes only: `w-1` or `h-2`
// also match arithmetic in minified code.
const UTILITY = /(?<![\w-])(?:size|gap|gap-x|gap-y|rounded|grid-cols|col-span|row-span|leading|min-w|max-w|min-h|max-h|space-x|space-y|inset|z|order|basis)-(?:\d+(?:\.5)?|xs|sm|md|lg|xl|2xl|3xl|full|none)(?![\w-])/g;

const SCANNED = /\.(?:ts|tsx|js|jsx|mjs|cjs)$/;

function filesUnder( path ) {
	if ( ! existsSync( path ) ) {
		return [];
	}
	if ( statSync( path ).isFile() ) {
		return SCANNED.test( path ) ? [ path ] : [];
	}
	return readdirSync( path, { withFileTypes: true } ).flatMap( ( item ) => {
		if ( item.name === 'node_modules' ) {
			return [];
		}
		return filesUnder( join( path, item.name ) );
	} );
}

function utilitiesIn( files ) {
	const found = new Set();
	for ( const file of files ) {
		for ( const match of readFileSync( file, 'utf8' ).matchAll( UTILITY ) ) {
			found.add( match[ 0 ] );
		}
	}
	return found;
}

const entryDir = dirname( resolve( entry ) );
const sources  = [ ...readFileSync( entry, 'utf8' ).matchAll( /^@source\s+["']([^"']+)["']/gm ) ]
	.map( ( match ) => match[ 1 ] )
	.map( ( raw ) => {
		// `../**/*.{ts,tsx}` → scan the directory the glob starts from.
		const base = raw.split( /\/\*\*|\/\*\./ )[ 0 ];
		return { raw, path: resolve( entryDir, base ) };
	} );

if ( sources.length === 0 ) {
	fail( `${ entry } has no @source lines to check.` );
}

for ( const file of builtFiles ) {
	if ( ! existsSync( file ) ) {
		fail( `built stylesheet not found: ${ file }` );
	}
}

const built = builtFiles.map( ( file ) => readFileSync( file, 'utf8' ) ).join( '\n' );
// A utility counts when it is there bare (`.gap-2`) or behind a variant
// (`.md\:gap-2`, `.dark\:hover\:gap-2`): both prove the source was read.
const has   = ( utility ) => {
	const escaped = utility.replace( /\./g, '\\.' );
	return built.includes( `.${ escaped }` ) || built.includes( `\\:${ escaped }` );
};

const scanned = sources.map( ( source ) => {
	const files = filesUnder( source.path );
	if ( files.length === 0 ) {
		fail( `@source "${ source.raw }" in ${ entry } resolves to ${ source.path }, which has no files to scan. The stylesheet would ship without its classes.` );
	}
	return { ...source, utilities: utilitiesIn( files ) };
} );

for ( const source of scanned ) {
	const others = scanned.filter( ( other ) => other !== source );
	const own    = [ ...source.utilities ].filter(
		( utility ) => ! others.some( ( other ) => other.utilities.has( utility ) )
	);

	if ( own.length === 0 ) {
		// Nothing unique to tell this source apart by; its existence is all we can prove.
		continue;
	}

	const missing = own.filter( ( utility ) => ! has( utility ) );
	const share   = ( own.length - missing.length ) / own.length;

	if ( share < REQUIRED_SHARE ) {
		fail(
			`the built CSS holds only ${ own.length - missing.length } of the ${ own.length } utilities that only @source "${ source.raw }" uses ` +
			`(missing e.g. ${ missing.slice( 0, 6 ).join( ', ' ) }). That source was not scanned.`
		);
	}
}

console.log( `  CSS verified against ${ scanned.length } @source path(s).` );
