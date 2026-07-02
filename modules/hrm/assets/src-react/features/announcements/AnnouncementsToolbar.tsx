/**
 * Toolbar for the Announcements list card: status tabs (Published / Draft /
 * Trash) with per-status counts on the left, debounced search input plus a
 * Filter toggle (published-date range) on the right. Presentational — all state
 * is owned by the page.
 */

import { Input } from '@wedevs/plugin-ui';
import { Filter, Search } from 'lucide-react';
import type { JSX } from 'react';

import { DateField } from '@/shared/DateField';
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
								<span className="font-normal text-[#a5a5aa]">({ countFor( tab.value ) })</span>
								{ selected ? (
									<span aria-hidden="true" className="absolute inset-x-0 -bottom-2 h-0.5 bg-primary" />
								) : null }
							</button>
						);
					} ) }
				</div>
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
					<button
						type="button"
						aria-label={ __( 'Toggle filters', 'erp' ) }
						aria-pressed={ filterButtonActive }
						onClick={ onToggleFilters }
						className={ [
							'relative inline-flex size-5 items-center justify-center transition-colors',
							filterButtonActive ? 'text-primary' : 'text-muted-foreground hover:text-foreground',
						].join( ' ' ) }
					>
						<Filter size={ 20 } strokeWidth={ 1.75 } aria-hidden="true" />
						{ activeFilterCount > 0 ? (
							<span className="absolute -right-1.5 -top-1.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-medium text-primary-foreground">
								{ activeFilterCount }
							</span>
						) : null }
					</button>
				</div>
			</div>

			{ filterButtonActive ? (
				<div className="flex flex-wrap items-center gap-4 border-b border-border bg-muted/20 px-4 py-3">
					<label className="flex items-center gap-2 text-sm text-muted-foreground">
						{ __( 'From', 'erp' ) }
						<DateField
							value={ startDate }
							onChange={ onStartDate }
							max={ endDate || undefined }
							className="h-9 w-44 border-border bg-background px-3 text-sm"
						/>
					</label>
					<label className="flex items-center gap-2 text-sm text-muted-foreground">
						{ __( 'To', 'erp' ) }
						<DateField
							value={ endDate }
							onChange={ onEndDate }
							min={ startDate || undefined }
							className="h-9 w-44 border-border bg-background px-3 text-sm"
						/>
					</label>
				</div>
			) : null }
		</>
	);
}
