import { describe, expect, it } from 'vitest';

import { csvCell } from './csv';

describe( 'csvCell', () => {
	it( 'writes a formula as text', () => {
		expect( csvCell( '=HYPERLINK("http://x","y")' ) ).toBe( '"\'=HYPERLINK(""http://x"",""y"")"' );
		expect( csvCell( '@SUM(A1)' ) ).toBe( "'@SUM(A1)" );
		expect( csvCell( '+cmd' ) ).toBe( "'+cmd" );
		expect( csvCell( '\tx' ) ).toBe( "'\tx" );
	} );

	it( 'leaves numbers and phone numbers alone', () => {
		expect( csvCell( -12.5 ) ).toBe( '-12.5' );
		expect( csvCell( '+880 1711-000000' ) ).toBe( '+880 1711-000000' );
		expect( csvCell( '(02) 555-0101' ) ).toBe( '(02) 555-0101' );
	} );

	it( 'quotes separators, and everything when asked', () => {
		expect( csvCell( 'Doe, Jane' ) ).toBe( '"Doe, Jane"' );
		expect( csvCell( 'plain' ) ).toBe( 'plain' );
		expect( csvCell( 'plain', true ) ).toBe( '"plain"' );
		expect( csvCell( null ) ).toBe( '' );
	} );
} );
