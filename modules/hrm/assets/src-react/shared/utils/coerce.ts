/**
 * Coercion helpers — WordPress REST responses are loosely typed. Even with
 * the v2 controller's `cast_*` helpers, the wire shape may include numeric
 * strings, `'0'`/`'1'` booleans, or stringly-typed null markers.
 *
 * Every store resolver / normalizer in the React layer routes responses
 * through these helpers before storing them.
 *
 * Pattern locked at openspec/changes/redesign-hr-free/typescript-strategy.md
 * §Coercion helpers.
 */

export function toInt( value: unknown, fallback: number = 0 ): number {
	if ( typeof value === 'number' && Number.isFinite( value ) ) {
		return Math.trunc( value );
	}
	if ( typeof value === 'string' && value !== '' ) {
		const parsed = Number.parseInt( value, 10 );
		if ( Number.isFinite( parsed ) ) {
			return parsed;
		}
	}
	return fallback;
}

export function toIntOrNull( value: unknown ): number | null {
	if ( value === null || value === undefined || value === '' ) {
		return null;
	}
	const parsed = toInt( value, Number.NaN );
	return Number.isFinite( parsed ) ? parsed : null;
}

export function toNumber( value: unknown, fallback: number = 0 ): number {
	if ( typeof value === 'number' && Number.isFinite( value ) ) {
		return value;
	}
	if ( typeof value === 'string' && value !== '' ) {
		const parsed = Number.parseFloat( value );
		if ( Number.isFinite( parsed ) ) {
			return parsed;
		}
	}
	return fallback;
}

export function toNumberOrNull( value: unknown ): number | null {
	if ( value === null || value === undefined || value === '' ) {
		return null;
	}
	const parsed = toNumber( value, Number.NaN );
	return Number.isFinite( parsed ) ? parsed : null;
}

export function toBool( value: unknown ): boolean {
	if ( typeof value === 'boolean' ) {
		return value;
	}
	if ( typeof value === 'number' ) {
		return value === 1;
	}
	if ( typeof value === 'string' ) {
		return [ '1', 'true', 'yes', 'on' ].includes( value.toLowerCase() );
	}
	return Boolean( value );
}

export function toStr( value: unknown, fallback: string = '' ): string {
	if ( typeof value === 'string' ) {
		return value;
	}
	if ( typeof value === 'number' || typeof value === 'boolean' ) {
		return String( value );
	}
	return fallback;
}

export function toStrOrNull( value: unknown ): string | null {
	if ( value === null || value === undefined ) {
		return null;
	}
	const str = toStr( value );
	return str === '' ? null : str;
}

/**
 * Whitelist a string against a known enum. Returns null when the value is
 * absent or not in the allowed list.
 */
export function toEnumOrNull< T extends string >(
	value: unknown,
	allowed: readonly T[]
): T | null {
	if ( typeof value !== 'string' ) {
		return null;
	}
	return ( allowed as readonly string[] ).includes( value ) ? ( value as T ) : null;
}

/**
 * Defensive array coercion. Always returns an array; non-array inputs become
 * empty arrays.
 */
export function toArray< T >( value: unknown ): T[] {
	return Array.isArray( value ) ? ( value as T[] ) : [];
}

/**
 * Defensive object coercion. Always returns a plain object; non-object inputs
 * become `{}`.
 */
export function toObject< T extends Record< string, unknown > >( value: unknown ): T {
	if ( value && typeof value === 'object' && ! Array.isArray( value ) ) {
		return value as T;
	}
	return {} as T;
}
