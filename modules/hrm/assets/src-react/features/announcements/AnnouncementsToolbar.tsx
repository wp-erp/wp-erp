/**
 * Toolbar for the Announcements list card: status tabs (Published / Draft /
 * Trash) with per-status counts on the left, debounced search input on the
 * right. Presentational — all state is owned by the page.
 */

import { Input } from '@wedevs/plugin-ui';
import { Search } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { STATUS_TABS } from './announcements-format';

interface AnnouncementsToolbarProps {
	readonly status:         string;
	readonly onStatus:       ( value: string ) => void;
	readonly searchInput:    string;
	readonly onSearchInput:  ( value: string ) => void;
	readonly countFor:       ( value: string ) => number;
}

export function AnnouncementsToolbar( {
	status,
	onStatus,
	searchInput,
	onSearchInput,
	countFor,
}: AnnouncementsToolbarProps ): JSX.Element {
	return (
		<div className="flex flex-wrap items-center justify-between gap-3 border-b border-border px-4 pt-3 pb-2">
			<div role="tablist" aria-label={ __( 'Announcement status', 'erp' ) } className="flex items-stretch">
				{ STATUS_TABS.map( ( tab ) => {
					const selected = status === tab.value;
					return (
						<button
							key={ tab.value }
							role="tab"
							type="button"
							aria-selected={ selected }
							onClick={ () => onStatus( tab.value ) }
							className={ [
								'relative inline-flex h-11 items-center gap-1.5 px-4 text-sm font-medium',
								selected ? 'text-primary' : 'text-muted-foreground hover:text-foreground',
							].join( ' ' ) }
						>
							<span>{ tab.label }</span>
							<span className="font-normal text-muted-foreground">({ countFor( tab.value ) })</span>
							{ selected ? (
								<span aria-hidden="true" className="absolute inset-x-0 -bottom-2 h-0.5 bg-primary" />
							) : null }
						</button>
					);
				} ) }
			</div>
			<div className="relative">
				<Search
					size={ 16 }
					aria-hidden="true"
					className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"
				/>
				<Input
					type="search"
					value={ searchInput }
					onChange={ ( e ) => onSearchInput( e.target.value ) }
					placeholder={ __( 'Search', 'erp' ) }
					className="h-9 w-60 rounded-md border-border pl-9 text-sm"
					aria-label={ __( 'Search announcements', 'erp' ) }
				/>
			</div>
		</div>
	);
}
