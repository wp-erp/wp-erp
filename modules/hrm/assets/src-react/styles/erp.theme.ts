/**
 * WP-ERP HR design tokens — light + dark.
 *
 * Mirrors openspec/changes/redesign-hr-free/design-tokens.md. Types come from
 * `@wedevs/plugin-ui`'s ThemeProvider so the values fit its `<ThemeProvider>`
 * props without casts.
 *
 * Figma source of truth: file key `zCdciqjm23PlLq1O1BPIMd` (HRM-Redesign-2024).
 */

import type { ThemeTokens as PluginUiTokens } from '@wedevs/plugin-ui';

export type ThemeTokens = PluginUiTokens;

export const erpLightTokens: ThemeTokens = {
	background:          'oklch(1 0 0)',
	foreground:          'oklch(0.1450 0 0)',
	card:                'oklch(1 0 0)',
	cardForeground:      'oklch(0.1450 0 0)',
	popover:             'oklch(1 0 0)',
	popoverForeground:   'oklch(0.1450 0 0)',

	primary:             'oklch(0.6230 0.2140 263.0900)',
	primaryHover:        'oklch(0.5410 0.2120 263.5400)',
	primaryActive:       'oklch(0.4880 0.2430 264.3760)',
	primaryForeground:   'oklch(0.9850 0 0)',

	secondary:           'oklch(0.9700 0 0)',
	secondaryForeground: 'oklch(0.2050 0 0)',

	muted:               'oklch(0.9700 0 0)',
	mutedForeground:     'oklch(0.5560 0 0)',

	accent:              'oklch(0.9550 0.0260 263.0900)',
	accentForeground:    'oklch(0.6230 0.2140 263.0900)',

	destructive:         'oklch(0.5770 0.2450 27.3250)',
	destructiveForeground: 'oklch(1 0 0)',
	success:             'oklch(0.6470 0.1780 145.0000)',
	successForeground:   'oklch(1 0 0)',
	warning:             'oklch(0.7690 0.1880 70.0800)',
	warningForeground:   'oklch(0.2100 0.0340 32.0000)',
	info:                'oklch(0.6230 0.2140 263.0900)',
	infoForeground:      'oklch(1 0 0)',

	successLight:        'oklch(0.9628 0.0593 152.5000)',
	successOnLight:      'oklch(0.5263 0.1572 149.4000)',
	destructiveLight:    'oklch(0.9385 0.0322 19.4000)',
	destructiveOnLight:  'oklch(0.5063 0.2178 28.9000)',
	warningLight:        'oklch(0.9697 0.0497 95.0000)',
	warningOnLight:      'oklch(0.4750 0.1300 70.0000)',
	infoLight:           'oklch(0.9614 0.0288 230.0000)',
	infoOnLight:         'oklch(0.5310 0.1320 232.0000)',
	neutralLight:        'oklch(0.9609 0 0)',
	neutralOnLight:      'oklch(0.4350 0 0)',

	border:              'oklch(0.9220 0 0)',
	input:               'oklch(0.8780 0.0119 250.0000)',
	ring:                'oklch(0.6230 0.2140 263.0900)',

	chart1:              'oklch(0.8100 0.0900 263.0900)',
	chart2:              'oklch(0.6800 0.1500 263.0900)',
	chart3:              'oklch(0.6230 0.2140 263.0900)',
	chart4:              'oklch(0.5410 0.2120 263.5400)',
	chart5:              'oklch(0.3500 0.1500 265.0000)',

	sidebar:                  'oklch(0.9850 0 0)',
	sidebarForeground:        'oklch(0.1450 0 0)',
	sidebarPrimary:           'oklch(0.6230 0.2140 263.0900)',
	sidebarPrimaryForeground: 'oklch(0.9850 0 0)',
	sidebarAccent:            'oklch(0.9550 0.0260 263.0900)',
	sidebarAccentForeground:  'oklch(0.6230 0.2140 263.0900)',
	sidebarBorder:            'oklch(0.9220 0 0)',
	sidebarRing:              'oklch(0.6230 0.2140 263.0900)',

	radius:              '0.5rem',
};

export const erpDarkTokens: ThemeTokens = {
	background:          'oklch(0.1450 0 0)',
	foreground:          'oklch(0.9850 0 0)',
	card:                'oklch(0.2050 0 0)',
	cardForeground:      'oklch(0.9850 0 0)',
	popover:             'oklch(0.2690 0 0)',
	popoverForeground:   'oklch(0.9850 0 0)',

	primary:             'oklch(0.6500 0.2050 263.0900)',
	primaryHover:        'oklch(0.7100 0.1900 263.0900)',
	primaryActive:       'oklch(0.6230 0.2140 263.0900)',
	primaryForeground:   'oklch(0.9850 0 0)',

	secondary:           'oklch(0.2690 0 0)',
	secondaryForeground: 'oklch(0.9850 0 0)',

	muted:               'oklch(0.2690 0 0)',
	mutedForeground:     'oklch(0.7080 0 0)',

	accent:              'oklch(0.3100 0.0500 263.0900)',
	accentForeground:    'oklch(0.9850 0 0)',

	destructive:         'oklch(0.7040 0.1910 22.2160)',
	destructiveForeground: 'oklch(0.9850 0 0)',
	success:             'oklch(0.6470 0.1780 145.0000)',
	successForeground:   'oklch(1 0 0)',
	warning:             'oklch(0.7690 0.1880 70.0800)',
	warningForeground:   'oklch(0.2100 0.0340 32.0000)',
	info:                'oklch(0.6500 0.2050 263.0900)',
	infoForeground:      'oklch(1 0 0)',

	successLight:        'oklch(0.3000 0.0700 152.5000)',
	successOnLight:      'oklch(0.8500 0.1400 149.4000)',
	destructiveLight:    'oklch(0.3000 0.0900 22.2160)',
	destructiveOnLight:  'oklch(0.8500 0.1400 28.9000)',
	warningLight:        'oklch(0.3200 0.0600 70.0000)',
	warningOnLight:      'oklch(0.8800 0.1500 90.0000)',
	infoLight:           'oklch(0.3100 0.0500 230.0000)',
	infoOnLight:         'oklch(0.8500 0.1300 232.0000)',
	neutralLight:        'oklch(0.2690 0 0)',
	neutralOnLight:      'oklch(0.7900 0 0)',

	border:              'oklch(0.2750 0 0)',
	input:               'oklch(0.3250 0 0)',
	ring:                'oklch(0.6500 0.2050 263.0900)',

	chart1:              'oklch(0.8500 0.0800 263.0900)',
	chart2:              'oklch(0.7000 0.1500 263.0900)',
	chart3:              'oklch(0.6500 0.2050 263.0900)',
	chart4:              'oklch(0.5400 0.2120 263.5400)',
	chart5:              'oklch(0.4400 0.1800 265.0000)',

	sidebar:                  'oklch(0.2050 0 0)',
	sidebarForeground:        'oklch(0.9850 0 0)',
	sidebarPrimary:           'oklch(0.6500 0.2050 263.0900)',
	sidebarPrimaryForeground: 'oklch(0.9850 0 0)',
	sidebarAccent:            'oklch(0.3100 0.0500 263.0900)',
	sidebarAccentForeground:  'oklch(0.9850 0 0)',
	sidebarBorder:            'oklch(0.2750 0 0)',
	sidebarRing:              'oklch(0.6500 0.2050 263.0900)',

	radius:              '0.5rem',
};

/**
 * Convert a camelCase token key to its CSS custom property name.
 *
 * `successOnLight` → `--success-on-light`
 */
export function tokenToCssVar( token: keyof ThemeTokens ): string {
	const kebab = ( token as string ).replace( /[A-Z]/g, ( m ) => `-${ m.toLowerCase() }` );
	return `--${ kebab }`;
}

/**
 * Render a `:root { --foo: ... }` block from a tokens object.
 *
 * Used by `app/providers.tsx` to inject the active theme's tokens into the
 * shell scope (`[data-erp-hr-app]`).
 */
export function tokensToCss( scopeSelector: string, tokens: ThemeTokens ): string {
	const entries = ( Object.keys( tokens ) as Array< keyof ThemeTokens > )
		.map( ( key ) => `\t${ tokenToCssVar( key ) }: ${ tokens[ key ] };` )
		.join( '\n' );
	return `${ scopeSelector } {\n${ entries }\n}`;
}
