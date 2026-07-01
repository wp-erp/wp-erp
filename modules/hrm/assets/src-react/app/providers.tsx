/**
 * Composition root for cross-cutting providers.
 *
 * Wraps the shell with plugin-ui's `ThemeProvider`, which:
 *   - Scopes Tailwind v4 styles under `data-pui-plugin="erp-hr-admin"`.
 *   - Writes the `--primary`, `--background`, etc. CSS variables onto its root
 *     based on the resolved color scheme.
 *
 * Mirrors Dokan's `Layout` component
 * (~/Desktop/dokan-ref/src/admin/dashboard/components/Layout.tsx).
 */

import { ThemeProvider, Toaster } from '@wedevs/plugin-ui';
import { useEffect } from 'react';
import type { JSX, ReactNode } from 'react';

import { useColorScheme } from '@/shared/hooks/useColorScheme';
import { useDir } from '@/shared/hooks/useDir';
import { erpDarkTokens, erpLightTokens } from '@/styles/erp.theme';

const PLUGIN_ID = 'erp-hr-admin';

interface ProvidersProps {
	readonly children: ReactNode;
}

export function Providers( { children }: ProvidersProps ): JSX.Element {
	const scheme = useColorScheme();
	const dir    = useDir();

	useEffect( () => {
		const root = document.getElementById( 'erp-hr-app' );
		if ( ! root ) {
			return;
		}
		root.setAttribute( 'data-color-scheme', scheme );
		root.setAttribute( 'data-dir', dir );
		root.dir = dir;
	}, [ scheme, dir ] );

	// Dark-mode for PORTALED content (DropdownMenu, Tooltip, Popover, Dialog).
	// plugin-ui renders those at <body> as a separate `.pui-root` that lives
	// OUTSIDE the ThemeProvider wrapper, so they only receive the light default
	// variables (see plugin-ui's exported `defaultCssVariables`). We copy the
	// CSS custom properties the provider wrote onto its own wrapper into an
	// injected <style> targeting every portal `.pui-root`, so menus follow the
	// active scheme. Reading the wrapper's own vars avoids hardcoding token
	// names — whatever the provider set is what portals get.
	useEffect( () => {
		const wrapper = document.querySelector< HTMLElement >( `[data-pui-plugin="${ PLUGIN_ID }"]` );
		if ( ! wrapper ) {
			return;
		}

		// Read the RESOLVED value of each token variable off the themed wrapper.
		// getComputedStyle works whether the provider sets the vars inline or via
		// a `.dark` stylesheet rule — so we always mirror the active scheme.
		const computed = window.getComputedStyle( wrapper );
		const decls = Object.keys( erpLightTokens )
			.map( ( token ) => {
				const varName = '--' + token.replace( /([A-Z])/g, '-$1' ).toLowerCase();
				const value   = computed.getPropertyValue( varName ).trim();
				return value ? `${ varName }:${ value }` : '';
			} )
			.filter( Boolean );

		if ( decls.length === 0 ) {
			return;
		}

		const STYLE_ID = 'erp-hr-portal-theme';
		let tag = document.getElementById( STYLE_ID ) as HTMLStyleElement | null;
		if ( ! tag ) {
			tag = document.createElement( 'style' );
			tag.id = STYLE_ID;
			document.head.appendChild( tag );
		}
		// Target PORTAL roots only. plugin-ui renders portals at <body> with just
		// the `.pui-root` class (no `data-pui-plugin`), while the themed app
		// wrapper carries `data-pui-plugin`. Excluding it guarantees we never
		// clobber the wrapper's own (correctly-themed) variables — which would
		// otherwise wash out the top bar text.
		tag.textContent = `.pui-root:not([data-pui-plugin]){${ decls.join( ';' ) };color-scheme:${ scheme };}`;
	}, [ scheme ] );

	return (
		<ThemeProvider
			pluginId={ PLUGIN_ID }
			tokens={ erpLightTokens }
			darkTokens={ erpDarkTokens }
			mode={ scheme }
		>
			{ children }
			<Toaster richColors position="bottom-right" theme={ scheme } />
		</ThemeProvider>
	);
}
