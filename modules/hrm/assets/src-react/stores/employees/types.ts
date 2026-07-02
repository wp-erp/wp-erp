/**
 * Types for the `erp-hr/employees` @wordpress/data store.
 *
 * Mirrors the `/erp/v2/employees` response shape (backend-owned). The `extra`
 * bag is the per-row pro-extension surface — backend's
 * `erp_hr_v2_employees_response_item` filter writes any pro fields there.
 */

import type { ReactNode } from 'react';

import type { Capability } from '@/types/global';

export type EmployeeStatus =
	| 'active'
	| 'inactive'
	| 'terminated'
	| 'resigned'
	| 'deceased';

export interface EmployeeLookup {
	readonly id:   number;
	readonly name: string;
}

export interface EmployeeReportingTo {
	readonly id:        number;
	readonly full_name: string;
}

export interface EmployeeListItem {
	readonly id:                 number;
	readonly user_id:            number;
	readonly employee_id:        string;
	readonly full_name:          string;
	readonly first_name:         string;
	readonly last_name:          string;
	readonly email:              string;
	readonly avatar_url:         string | null;
	readonly status:             EmployeeStatus | null;
	readonly employee_type:      string | null;
	readonly hire_date:          string | null;
	readonly termination_date:   string | null;
	/** Latest status-change date (Terminated At / Inactive From / …); null for active. */
	readonly status_date:        string | null;
	readonly is_active:          boolean;
	readonly department:         EmployeeLookup | null;
	readonly designation:        EmployeeLookup | null;
	readonly location:           EmployeeLookup | null;
	readonly reporting_to:       EmployeeReportingTo | null;
	readonly phone:              string | null;
	readonly pay_type:           string | null;
	readonly extra:              Record< string, unknown >;
}

export interface EmployeeListQuery {
	readonly search?:         string;
	readonly status?:         EmployeeStatus | 'all' | 'trash';
	readonly department_id?:  number;
	readonly designation_id?: number;
	readonly location_id?:    number;
	readonly employee_type?:  string;
	readonly orderby?:        'full_name' | 'email' | 'hire_date' | 'status';
	readonly order?:          'asc' | 'desc';
	readonly per_page?:       number;
	readonly page?:           number;
}

export interface EmployeeListMeta {
	readonly total:      number;
	readonly totalPages: number;
}

/**
 * Per-status counts for the table tabs. Returned by GET /erp/v2/employees/counts.
 * `all` excludes trash. `by_status` carries the per-bucket counts plus `trash`.
 */
export interface EmployeeStatusCounts {
	readonly all:       number;
	readonly by_status: Record< string, number >;
}

/**
 * Filter context that scopes a counts request — kept narrow so the counts
 * cache key is stable across UI-only changes (status flip, sort, page).
 */
export interface EmployeeCountsQuery {
	readonly search?:         string;
	readonly department_id?:  number;
	readonly designation_id?: number;
	readonly location_id?:    number;
}

export interface EmployeeListError {
	readonly code:    string;
	readonly message: string;
}

export interface EmployeesState {
	/** byId map — authoritative store of every employee fetched in this session. */
	readonly byId: Readonly< Record< number, EmployeeListItem > >;
	/** Per-query result rows (ordered by API). Key = canonical queryKey. */
	readonly byQuery: Readonly< Record< string, readonly number[] > >;
	/** Per-query pagination meta. */
	readonly metaByQuery: Readonly< Record< string, EmployeeListMeta > >;
	/** UI filter state — also URL-synced. */
	readonly filters: EmployeeListQuery;
	readonly sort: {
		readonly orderby: NonNullable< EmployeeListQuery[ 'orderby' ] >;
		readonly order:   NonNullable< EmployeeListQuery[ 'order' ] >;
	};
	readonly pagination: { readonly page: number; readonly perPage: number };
	readonly selectedIds: readonly number[];
	readonly isLoading: boolean;
	readonly lastError: EmployeeListError | null;
	/** Counts cache keyed by canonical EmployeeCountsQuery hash. */
	readonly countsByKey: Readonly< Record< string, EmployeeStatusCounts > >;
}

// ─── Public extension surface (consumed by features/employees + pro hooks) ───

export interface ColumnContext {
	readonly can:    ( capability: Capability, employeeId?: number ) => boolean;
	readonly hasPro: boolean;
	readonly locale: string;
	readonly dir:    'ltr' | 'rtl';
}

export interface EmployeeColumn {
	readonly id:             string;
	readonly label:          string;
	readonly priority:       number;
	readonly defaultVisible: boolean;
	readonly sortable:       boolean;
	readonly filterable:     boolean;
	readonly getValue:       ( row: EmployeeListItem ) => string | number | null;
	readonly render:         ( row: EmployeeListItem, ctx: ColumnContext ) => ReactNode;
	readonly capability?:    Capability;
}

export interface EmployeeFilter {
	readonly id:         string;
	readonly label:      string;
	readonly priority:   number;
	readonly Component:  ( props: { value: unknown; onChange: ( next: unknown ) => void } ) => ReactNode;
	readonly toQuery:    ( value: unknown ) => Partial< EmployeeListQuery >;
	readonly fromQuery:  ( q: EmployeeListQuery ) => unknown;
	readonly capability?: Capability;
}

export interface EmployeeRowAction {
	readonly id:         string;
	readonly label:      string;
	readonly priority:   number;
	readonly icon?:      string;
	readonly variant?:   'default' | 'destructive';
	readonly onSelect:   ( employee: EmployeeListItem ) => void | Promise< void >;
	readonly isVisible?: ( employee: EmployeeListItem, ctx: ColumnContext ) => boolean;
	readonly capability?: Capability;
}

export interface EmployeeBulkAction {
	readonly id:         string;
	readonly label:      string;
	readonly priority:   number;
	readonly icon?:      string;
	readonly variant?:   'default' | 'destructive';
	readonly confirm?:   { readonly title: string; readonly description: string; readonly confirmLabel: string };
	readonly onSelect:   ( selectedIds: readonly number[] ) => void | Promise< void >;
	readonly capability?: Capability;
}

export interface EmployeeToolbarItem {
	readonly id:         string;
	readonly label:      string;
	readonly priority:   number;
	readonly icon?:      string;
	readonly variant?:   'primary' | 'secondary' | 'ghost';
	readonly onSelect:   () => void;
	readonly capability?: Capability;
}

export type ColumnsFilter      = ( columns: EmployeeColumn[], ctx: ColumnContext ) => EmployeeColumn[];
export type FiltersFilter      = ( filters: EmployeeFilter[], ctx: ColumnContext ) => EmployeeFilter[];
export type RowActionsFilter   = ( actions: EmployeeRowAction[], employee: EmployeeListItem, ctx: ColumnContext ) => EmployeeRowAction[];
export type BulkActionsFilter  = ( actions: EmployeeBulkAction[], selectedIds: readonly number[], ctx: ColumnContext ) => EmployeeBulkAction[];
export type ToolbarItemsFilter = ( items: EmployeeToolbarItem[], ctx: ColumnContext ) => EmployeeToolbarItem[];

// Raw response shape (defensive — WP-loose typing).
export type RawEmployeeListItem = Partial< Omit< EmployeeListItem, 'extra' > > & {
	readonly extra?: unknown;
};

export const STORE_NAME = 'erp-hr/employees' as const;
export type  StoreName  = typeof STORE_NAME;
