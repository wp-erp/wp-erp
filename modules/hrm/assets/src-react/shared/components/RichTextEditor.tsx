/**
 * plugin-ui's `RichTextEditor` with translated, screen-reader names on its
 * toolbar controls. Same props, so call sites swap the import 1:1.
 */

import { RichTextEditor as PluginRichTextEditor } from '@wedevs/plugin-ui';
import { useEffect, useRef, type ComponentProps, type JSX } from 'react';

import { __ } from '@/shared/i18n';

export function RichTextEditor( props: ComponentProps< typeof PluginRichTextEditor > ): JSX.Element {
	const wrap = useRef< HTMLDivElement >( null );

	// shortcut: the toolbar has only hard-coded English button titles and unnamed selects, so they are named from the DOM after mount, upgrade when plugin-ui exposes toolbar label props
	useEffect( () => {
		const names: Record< string, string > = {
			'AI Assist':     __( 'AI Assist', 'erp' ),
			Bold:            __( 'Bold', 'erp' ),
			Italic:          __( 'Italic', 'erp' ),
			Underline:       __( 'Underline', 'erp' ),
			Strikethrough:   __( 'Strikethrough', 'erp' ),
			Link:            __( 'Link', 'erp' ),
			Quote:           __( 'Quote', 'erp' ),
			'Bullet List':   __( 'Bullet List', 'erp' ),
			'Numbered List': __( 'Numbered List', 'erp' ),
			'Align Left':    __( 'Align Left', 'erp' ),
			Image:           __( 'Image', 'erp' ),
			Undo:            __( 'Undo', 'erp' ),
			Redo:            __( 'Redo', 'erp' ),
			More:            __( 'More', 'erp' ),
		};
		wrap.current?.querySelectorAll( 'button[title]' ).forEach( ( button ) => {
			const title = button.getAttribute( 'title' ) ?? '';
			button.setAttribute( 'aria-label', names[ title ] ?? title );
		} );
		// Toolbar selects render in this order; "simple" omits the last.
		const selects = [ __( 'Text style', 'erp' ), __( 'Font', 'erp' ), __( 'Font size', 'erp' ) ];
		wrap.current?.querySelectorAll( 'select' ).forEach( ( select, i ) => {
			select.setAttribute( 'aria-label', selects[ i ] ?? '' );
		} );
	}, [] );

	return (
		<div ref={ wrap }>
			<PluginRichTextEditor { ...props } />
		</div>
	);
}
