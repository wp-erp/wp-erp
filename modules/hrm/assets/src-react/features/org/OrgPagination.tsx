/**
 * Pagination footer for the HR taxonomy tables (departments / designations).
 *
 * Visually identical to the People table footer
 * (`features/employees/EmployeesTable.tsx`) so pagination looks the same across
 * the HR admin: a left-aligned "Showing X–Y of Z", and a right cluster with a
 * rows-per-page select + prev / next icon buttons and a "page of total"
 * indicator.
 *
 * The markup lives in the shared `TablePager`; this keeps the prop names the
 * taxonomy pages already pass.
 *
 * Client-side pagination over the already-fetched list — these are
 * low-cardinality entities, so the whole set is loaded once and paged in the
 * browser.
 */

import type { JSX } from 'react';

import { TablePager } from '@/shared/components/TablePager';

interface OrgPaginationProps {
	readonly page:       number;
	readonly totalPages: number;
	readonly total:      number;
	readonly perPage:    number;
	readonly onPage:     ( page: number ) => void;
	readonly onPerPage:  ( perPage: number ) => void;
}

export function OrgPagination( { page, totalPages, total, perPage, onPage, onPerPage }: OrgPaginationProps ): JSX.Element {
	return (
		<TablePager
			page={ page }
			perPage={ perPage }
			total={ total }
			totalPages={ totalPages }
			onPage={ onPage }
			onPerPage={ onPerPage }
		/>
	);
}
