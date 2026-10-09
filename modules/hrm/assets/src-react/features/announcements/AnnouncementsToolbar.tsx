/**
 * Toolbar for the Announcements list card: status tabs (Published / Draft /
 * Trash) with per-status counts on the left, debounced search input plus a
 * Filter toggle (published-date range) on the right. Presentational — all state
 * is owned by the page.
 */

import { Input } from '@wedevs/plugin-ui';
import { Search } from 'lucide-react';
import type { JSX } from 'react';

import { FilterLabel } from '@/shared/components/FieldLabels';
import { FilterButton } from '@/shared/components/FilterButton';
import { StatusTabs } from '@/shared/components/StatusTabs';
import { DateRangeField } from '@/shared/DateRangeField';
import { __ } from '@/shared/i18n';

import { STATUS_TABS } from './announcements-format';

interface AnnouncementsToolbarProps {
	readonly status:             string;
	readonly onStatus:           ( value: string ) => void;
	readonly searchInput:        string;
	readonly onSearchInput:      ( value: string ) => void;
	readonly countFor:           ( value: string ) => number;
	readonly onToggleFilters:    () => void;
	readonly filterButtonActive: boolean;
	readonly activeFilterCount:  number;
	readonly startDate:          string;
	readonly endDate:            string;
	readonly onStartDate:        ( value: string ) => void;
	readonly onEndDate:          ( value: string ) => void;
}

export function AnnouncementsToolbar( {
	status,
	onStatus,
	searchInput,
	onSearchInput,
	countFor,
	onToggleFilters,
	filterButtonActive,
	activeFilterCount,
	startDate,
	endDate,
	onStartDate,
	onEndDate,
}: AnnouncementsToolbarProps ): JSX.Element {
	return (
		<>
			<div className="flex flex-wrap items-center justify-between gap-3 border-b border-border px-4 pt-3 pb-2">
				<StatusTabs
					tabs={ STATUS_TABS.map( ( tab ) => ( { value: tab.value, label: tab.label, count: countFor( tab.value ) } ) ) }
					value={ status }
					onChange={ onStatus }
					ariaLabel={ __( 'Announcement status', 'erp' ) }
				/>
				<div className="flex items-center gap-3">
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
					<FilterButton
						active={ filterButtonActive }
						count={ activeFilterCount }
						onToggle={ onToggleFilters }
					/>
				</div>
			</div>

			{ filterButtonActive ? (
				<div className="flex flex-wrap items-center gap-2 border-b border-border bg-muted/20 px-4 py-3">
					<FilterLabel>
						{ __( 'Date range', 'erp' ) }
						<DateRangeField
							value={ { from: startDate, to: endDate } }
							onChange={ ( r ) => {
								onStartDate( r.from );
								onEndDate( r.to );
							} }
							className="w-64 bg-background"
						/>
					</FilterLabel>
				</div>
			) : null }
		</>
	);
}
