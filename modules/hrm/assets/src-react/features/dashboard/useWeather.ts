/**
 * Current-location weather for the dashboard.
 *
 * Resolution chain:
 *   1. Browser geolocation (`navigator.geolocation`) — one-time permission
 *      prompt, precise coordinates.
 *   2. IP-based fallback (ipapi.co) when geolocation is denied/unavailable —
 *      approximate, no prompt.
 *
 * Weather comes from Open-Meteo (free, no API key, CORS-enabled). The place
 * name is reverse-geocoded from BigDataCloud (also free/no key); on the IP path
 * the city is already known so we skip that call.
 *
 * Everything fails silent: any network/permission error resolves to
 * `unavailable` so the widget can simply hide rather than error the dashboard.
 */

import { useEffect, useState } from 'react';

export interface WeatherData {
	readonly tempC:    number;
	readonly feelsC:   number;
	readonly humidity: number;
	readonly windKmh:  number;
	readonly code:     number;
	readonly isDay:    boolean;
	readonly city:     string;
}

export type WeatherStatus = 'loading' | 'ready' | 'unavailable';

export interface UseWeatherResult {
	readonly status: WeatherStatus;
	readonly data:   WeatherData | null;
	readonly retry:  () => void;
}

const GEO_TIMEOUT_MS = 8000;
const GEO_MAX_AGE_MS = 10 * 60 * 1000;

interface Coords {
	readonly lat:   number;
	readonly lon:   number;
	readonly city?: string | undefined;
}

async function resolveCoords(): Promise< Coords > {
	const fromBrowser = await new Promise< Coords | null >( ( resolve ) => {
		if ( typeof navigator === 'undefined' || ! navigator.geolocation ) {
			resolve( null );
			return;
		}
		navigator.geolocation.getCurrentPosition(
			( pos ) => resolve( { lat: pos.coords.latitude, lon: pos.coords.longitude } ),
			() => resolve( null ),
			{ timeout: GEO_TIMEOUT_MS, maximumAge: GEO_MAX_AGE_MS }
		);
	} );

	if ( fromBrowser ) {
		return fromBrowser;
	}

	// No browser location — approximate from IP (no prompt). GeoJS is CORS-open
	// and keyless (ipapi.co blocks keyless cross-origin; ipwho.is 403s the
	// browser). It returns lat/lon as strings, so coerce.
	const res = await fetch( 'https://get.geojs.io/v1/ip/geo.json' );
	if ( ! res.ok ) {
		throw new Error( 'ip lookup failed' );
	}
	const json = ( await res.json() ) as { latitude?: string; longitude?: string; city?: string };
	const lat = Number( json.latitude );
	const lon = Number( json.longitude );
	if ( ! Number.isFinite( lat ) || ! Number.isFinite( lon ) ) {
		throw new Error( 'ip lookup incomplete' );
	}
	return { lat, lon, city: json.city };
}

async function reverseCity( lat: number, lon: number ): Promise< string > {
	try {
		const res = await fetch(
			`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${ lat }&longitude=${ lon }&localityLanguage=en`
		);
		if ( ! res.ok ) {
			return '';
		}
		const json = ( await res.json() ) as {
			city?: string;
			locality?: string;
			principalSubdivision?: string;
		};
		return json.city || json.locality || json.principalSubdivision || '';
	} catch {
		return '';
	}
}

type WeatherCore = Omit< WeatherData, 'city' >;

async function fetchWeather( lat: number, lon: number ): Promise< WeatherCore > {
	const res = await fetch(
		`https://api.open-meteo.com/v1/forecast?latitude=${ lat }&longitude=${ lon }` +
			'&current=temperature_2m,apparent_temperature,relative_humidity_2m,weather_code,is_day,wind_speed_10m' +
			'&temperature_unit=celsius&wind_speed_unit=kmh'
	);
	if ( ! res.ok ) {
		throw new Error( 'weather fetch failed' );
	}
	const json = ( await res.json() ) as {
		current?: {
			temperature_2m?:      number;
			apparent_temperature?: number;
			relative_humidity_2m?: number;
			wind_speed_10m?:       number;
			weather_code?:         number;
			is_day?:               number;
		};
	};
	const current = json.current;
	if (
		! current ||
		typeof current.temperature_2m !== 'number' ||
		typeof current.weather_code !== 'number'
	) {
		throw new Error( 'weather payload invalid' );
	}
	return {
		tempC:    current.temperature_2m,
		feelsC:   current.apparent_temperature ?? current.temperature_2m,
		humidity: current.relative_humidity_2m ?? 0,
		windKmh:  current.wind_speed_10m ?? 0,
		code:     current.weather_code,
		isDay:    current.is_day === 1,
	};
}

// Cache the last reading so navigating back to the dashboard (or a quick
// reload) reuses it instead of hammering three third-party APIs every mount.
// Short TTL keeps it fresh; sessionStorage scopes it to the browser tab.
const CACHE_KEY = 'erp-hr-weather-v1';
const CACHE_TTL_MS = 15 * 60 * 1000;

function readCache(): WeatherData | null {
	try {
		const raw = window.sessionStorage.getItem( CACHE_KEY );
		if ( ! raw ) {
			return null;
		}
		const parsed = JSON.parse( raw ) as { ts?: number; data?: WeatherData };
		if (
			typeof parsed.ts !== 'number' ||
			! parsed.data ||
			Date.now() - parsed.ts > CACHE_TTL_MS
		) {
			return null;
		}
		return parsed.data;
	} catch {
		return null;
	}
}

function writeCache( data: WeatherData ): void {
	try {
		window.sessionStorage.setItem( CACHE_KEY, JSON.stringify( { ts: Date.now(), data } ) );
	} catch {
		// Storage unavailable/full — fine, just skip caching.
	}
}

export function useWeather(): UseWeatherResult {
	const cached = readCache();
	const [ status, setStatus ] = useState< WeatherStatus >( cached ? 'ready' : 'loading' );
	const [ data, setData ]     = useState< WeatherData | null >( cached );
	const [ nonce, setNonce ]   = useState( 0 );

	useEffect( () => {
		let cancelled = false;

		// Fresh cache on first mount → reuse, no network. The retry button
		// (nonce > 0) always forces a refetch.
		if ( nonce === 0 && readCache() ) {
			return () => {
				cancelled = true;
			};
		}

		setStatus( 'loading' );

		void ( async () => {
			try {
				const { lat, lon, city: ipCity } = await resolveCoords();
				const [ weather, city ] = await Promise.all( [
					fetchWeather( lat, lon ),
					ipCity ? Promise.resolve( ipCity ) : reverseCity( lat, lon ),
				] );
				if ( cancelled ) {
					return;
				}
				const next = { ...weather, city: city || ipCity || '' };
				writeCache( next );
				setData( next );
				setStatus( 'ready' );
			} catch {
				if ( cancelled ) {
					return;
				}
				setStatus( 'unavailable' );
			}
		} )();

		return () => {
			cancelled = true;
		};
	}, [ nonce ] );

	return { status, data, retry: () => setNonce( ( n ) => n + 1 ) };
}
