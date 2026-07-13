/**
 * Theme toggle — single icon button flipping Light ↔ Dark.
 *
 * Shows the icon of the mode you'd switch *to*: a Moon while light, a Sun
 * while dark. Clicking sets the opposite of the currently resolved scheme.
 */

import { Toggle } from '@wedevs/plugin-ui';
import { Moon, Sun } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import { useTheme } from '@/shared/hooks/useTheme';

export function ThemeToggle(): JSX.Element {
	const { resolved, setMode } = useTheme();
	const isDark = resolved === 'dark';

	return (
		<Toggle
			pressed={ isDark }
			aria-label={ isDark ? __( 'Switch to light mode', 'erp' ) : __( 'Switch to dark mode', 'erp' ) }
			onPressedChange={ () => setMode( isDark ? 'light' : 'dark' ) }
			className="size-9 rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
		>
			{ isDark ? (
				<Sun size={ 16 } strokeWidth={ 1.75 } aria-hidden="true" />
			) : (
				<Moon size={ 16 } strokeWidth={ 1.75 } aria-hidden="true" />
			) }
		</Toggle>
	);
}
