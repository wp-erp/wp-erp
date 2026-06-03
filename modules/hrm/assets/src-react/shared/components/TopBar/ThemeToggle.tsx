/**
 * 3-state theme toggle (Light / Dark / Auto).
 *
 * Built from three plugin-ui `Toggle` components grouped visually. Single-
 * select semantics are enforced in the handler — clicking the current mode is
 * a no-op (Toggle's own off-press is suppressed).
 */

import { Toggle } from '@wedevs/plugin-ui';
import { Monitor, Moon, Sun } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import { useTheme } from '@/shared/hooks/useTheme';
import type { ThemeMode } from '@/types/global';

const SEGMENTS: ReadonlyArray< {
	readonly value: ThemeMode;
	readonly label: string;
	readonly Icon:  typeof Sun;
} > = [
	{ value: 'light', label: 'Light', Icon: Sun },
	{ value: 'dark',  label: 'Dark',  Icon: Moon },
	{ value: 'auto',  label: 'Auto',  Icon: Monitor },
];

export function ThemeToggle(): JSX.Element {
	const { mode, setMode } = useTheme();

	return (
		<div
			role="group"
			aria-label={ __( 'Theme', 'erp' ) }
			className="inline-flex items-center gap-0.5 rounded-md border border-border bg-card p-0.5"
		>
			{ SEGMENTS.map( ( { value, label, Icon } ) => (
				<Toggle
					key={ value }
					pressed={ mode === value }
					aria-label={ label }
					onPressedChange={ ( pressed: boolean ) => {
						if ( pressed ) {
							setMode( value );
						}
					} }
					className="size-7 rounded-sm text-muted-foreground data-[state=on]:bg-info-light data-[state=on]:text-info-on-light"
				>
					<Icon size={ 16 } strokeWidth={ 1.75 } aria-hidden="true" />
				</Toggle>
			) ) }
		</div>
	);
}
