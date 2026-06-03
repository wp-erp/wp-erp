/**
 * Compact current-location weather chip for the dashboard header.
 *
 * Self-contained: pulls coords + conditions via `useWeather` (Open-Meteo,
 * browser geolocation with an IP fallback). Renders a small card when ready, a
 * skeleton while resolving, and a "Show weather" retry button when location is
 * denied or unavailable — never an error, so it stays out of the way.
 */

import {
	Cloud,
	CloudDrizzle,
	CloudFog,
	CloudLightning,
	CloudRain,
	CloudSnow,
	CloudSun,
	Droplets,
	MapPin,
	Moon,
	RefreshCw,
	Sun,
	Thermometer,
} from 'lucide-react';
import type { ComponentType, JSX, SVGProps } from 'react';

import { __ } from '@/shared/i18n';

import { useWeather } from './useWeather';

type LucideIcon = ComponentType< SVGProps< SVGSVGElement > & { size?: number; strokeWidth?: number } >;

interface Condition {
	readonly label: string;
	readonly Icon:  LucideIcon;
	readonly tint:  string;
}

/**
 * Map a WMO weather code to a label, icon, and accent tint. Codes per the
 * Open-Meteo docs. `isDay` swaps the clear-sky glyph (sun vs. moon). Tints
 * follow the dashboard StatCard pattern (tinted bg + dark-mode text variant) so
 * they stay legible in both color schemes.
 */
function describe( code: number, isDay: boolean ): Condition {
	if ( code === 0 ) {
		return isDay
			? { label: __( 'Clear', 'erp' ), Icon: Sun, tint: 'bg-amber-500/10 text-amber-600 dark:text-amber-400' }
			: { label: __( 'Clear', 'erp' ), Icon: Moon, tint: 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' };
	}
	if ( code === 1 || code === 2 ) {
		return { label: __( 'Partly cloudy', 'erp' ), Icon: CloudSun, tint: 'bg-sky-500/10 text-sky-600 dark:text-sky-400' };
	}
	if ( code === 3 ) {
		return { label: __( 'Overcast', 'erp' ), Icon: Cloud, tint: 'bg-slate-500/10 text-slate-600 dark:text-slate-300' };
	}
	if ( code === 45 || code === 48 ) {
		return { label: __( 'Fog', 'erp' ), Icon: CloudFog, tint: 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-300' };
	}
	if ( code >= 51 && code <= 57 ) {
		return { label: __( 'Drizzle', 'erp' ), Icon: CloudDrizzle, tint: 'bg-sky-500/10 text-sky-600 dark:text-sky-400' };
	}
	if ( ( code >= 61 && code <= 67 ) || ( code >= 80 && code <= 82 ) ) {
		return { label: __( 'Rain', 'erp' ), Icon: CloudRain, tint: 'bg-blue-500/10 text-blue-600 dark:text-blue-400' };
	}
	if ( ( code >= 71 && code <= 77 ) || code === 85 || code === 86 ) {
		return { label: __( 'Snow', 'erp' ), Icon: CloudSnow, tint: 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-300' };
	}
	if ( code >= 95 ) {
		return { label: __( 'Thunderstorm', 'erp' ), Icon: CloudLightning, tint: 'bg-violet-500/10 text-violet-600 dark:text-violet-400' };
	}
	return { label: __( 'Cloudy', 'erp' ), Icon: Cloud, tint: 'bg-slate-500/10 text-slate-600 dark:text-slate-300' };
}

interface WeatherWidgetProps {
	/** Drop the standalone card chrome when sitting inside another card. */
	readonly embedded?: boolean;
}

export function WeatherWidget( { embedded = false }: WeatherWidgetProps ): JSX.Element {
	const { status, data, retry } = useWeather();

	const shell = embedded
		? 'flex items-center gap-3'
		: 'flex items-center gap-3 rounded-xl border border-border bg-card px-4 py-2.5 shadow-sm';

	// First load (no data yet) shows a skeleton; a manual refresh keeps the
	// existing chip and just spins the refresh icon.
	if ( status === 'loading' && ! data ) {
		return (
			<div
				className={ `h-[3.75rem] w-52 animate-pulse rounded-xl ${ embedded ? 'bg-muted/40' : 'border border-border bg-muted/40' }` }
				aria-hidden="true"
			/>
		);
	}

	// No data at all (denied/unavailable) → offer a retry; keep a stale chip
	// visible if a later refresh fails.
	if ( ! data ) {
		return (
			<button
				type="button"
				onClick={ retry }
				className="inline-flex h-9 items-center gap-1.5 rounded-md border border-border bg-card px-3 text-sm font-medium text-muted-foreground hover:bg-muted"
			>
				<CloudSun size={ 16 } aria-hidden="true" />
				{ __( 'Show weather', 'erp' ) }
			</button>
		);
	}

	const { label, Icon, tint } = describe( data.code, data.isDay );
	const refreshing = status === 'loading';

	return (
		<div className={ shell }>
			<span
				className={ `flex size-14 shrink-0 items-center justify-center rounded-xl ${ tint }` }
				aria-hidden="true"
			>
				<Icon size={ 32 } strokeWidth={ 1.8 } />
			</span>

			<div className="min-w-0 leading-tight">
				<div className="flex items-baseline gap-0.5">
					<span className="text-3xl font-bold text-foreground tabular-nums">{ Math.round( data.tempC ) }°</span>
					<span className="text-base font-medium text-muted-foreground">C</span>
				</div>
				<div className="flex items-center gap-1 text-sm text-muted-foreground">
					<span className="whitespace-nowrap">{ label }</span>
					{ data.city ? (
						<>
							<span aria-hidden="true">·</span>
							<MapPin size={ 13 } strokeWidth={ 2 } aria-hidden="true" className="shrink-0" />
							<span className="truncate">{ data.city }</span>
						</>
					) : null }
				</div>
			</div>

			<div className="ml-1.5 hidden flex-col gap-1.5 border-l border-border pl-4 text-xs leading-none text-muted-foreground sm:flex">
				<span className="inline-flex items-center gap-1.5 whitespace-nowrap" title={ __( 'Feels like', 'erp' ) }>
					<Thermometer size={ 14 } strokeWidth={ 2 } aria-hidden="true" />
					{ Math.round( data.feelsC ) }°
				</span>
				<span className="inline-flex items-center gap-1.5 whitespace-nowrap" title={ __( 'Humidity', 'erp' ) }>
					<Droplets size={ 14 } strokeWidth={ 2 } aria-hidden="true" />
					{ Math.round( data.humidity ) }%
				</span>
			</div>

			<button
				type="button"
				onClick={ retry }
				disabled={ refreshing }
				aria-label={ __( 'Refresh weather', 'erp' ) }
				title={ __( 'Refresh weather', 'erp' ) }
				className="ml-0.5 inline-flex size-7 shrink-0 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground disabled:opacity-60"
			>
				<RefreshCw size={ 14 } strokeWidth={ 2 } aria-hidden="true" className={ refreshing ? 'animate-spin' : '' } />
			</button>
		</div>
	);
}
