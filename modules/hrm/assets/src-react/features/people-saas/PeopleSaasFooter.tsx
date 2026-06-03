/**
 * Footer pagination — "Showing X–Y of Z" + page-size select + arrow buttons.
 */

import { useDispatch } from '@wordpress/data';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';
import type { EmployeesState } from '@/stores/employees';

import { useEmployeesQuery } from '../employees/useEmployeesQuery';

interface EmployeesStoreDispatch {
	setPagination: ( pagination: EmployeesState[ 'pagination' ] ) => void;
}

export function PeopleSaasFooter(): JSX.Element {
	const { page, perPage, total, totalPages } = useEmployeesQuery();
	const { setPagination } = useDispatch(
		employeesStoreName
	) as unknown as EmployeesStoreDispatch;

	const start = total === 0 ? 0 : ( page - 1 ) * perPage + 1;
	const end   = Math.min( page * perPage, total );

	return (
		<footer className="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 bg-white px-6 py-3 text-xs text-slate-600">
			<span className="tabular-nums">
				{ sprintf( __( 'Showing %1$d–%2$d of %3$d', 'erp' ), start, end, total ) }
			</span>
			<div className="flex items-center gap-3">
				<label className="flex items-center gap-2">
					<span className="text-slate-500">{ __( 'Rows', 'erp' ) }</span>
					<select
						value={ perPage }
						onChange={ ( e ) =>
							setPagination( { page: 1, perPage: parseInt( e.target.value, 10 ) } )
						}
						className="h-7 cursor-pointer rounded-md border border-slate-200 bg-white pl-2 pr-6 text-xs font-medium text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none"
						aria-label={ __( 'Rows per page', 'erp' ) }
					>
						{ [ 10, 20, 50, 100 ].map( ( n ) => (
							<option key={ n } value={ n }>
								{ n }
							</option>
						) ) }
					</select>
				</label>
				<div className="inline-flex items-center gap-1">
					<button
						type="button"
						onClick={ () => setPagination( { page: Math.max( 1, page - 1 ), perPage } ) }
						disabled={ page <= 1 }
						className="inline-flex size-7 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-700 shadow-sm hover:bg-slate-50 disabled:opacity-40"
						aria-label={ __( 'Previous page', 'erp' ) }
					>
						<ChevronLeft size={ 14 } aria-hidden="true" />
					</button>
					<span className="min-w-16 px-2 text-center text-xs font-medium tabular-nums text-slate-700">
						{ sprintf( __( '%1$d / %2$d', 'erp' ), page, Math.max( totalPages, 1 ) ) }
					</span>
					<button
						type="button"
						onClick={ () => setPagination( { page: page + 1, perPage } ) }
						disabled={ page >= totalPages }
						className="inline-flex size-7 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-700 shadow-sm hover:bg-slate-50 disabled:opacity-40"
						aria-label={ __( 'Next page', 'erp' ) }
					>
						<ChevronRight size={ 14 } aria-hidden="true" />
					</button>
				</div>
			</div>
		</footer>
	);
}
