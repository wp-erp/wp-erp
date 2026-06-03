/**
 * Floating selection bar — shows when 1+ rows selected.
 *
 * Sticks to the bottom-center, dark surface, with bulk actions and a clear
 * link. Linear-style "command bar" feel.
 */

import { useDispatch, useSelect } from '@wordpress/data';
import { Trash2, X } from 'lucide-react';
import type { JSX } from 'react';

import { __, sprintf } from '@/shared/i18n';
import { storeName as employeesStoreName } from '@/stores/employees';

interface EmployeesStoreSelectors {
	getSelectedIds: () => readonly number[];
}

interface EmployeesStoreDispatch {
	setSelectedIds: ( ids: readonly number[] ) => void;
}

export function PeopleSaasBulkBar(): JSX.Element | null {
	const selectedIds = useSelect(
		( select ) =>
			( select( employeesStoreName ) as unknown as EmployeesStoreSelectors ).getSelectedIds(),
		[]
	);
	const { setSelectedIds } = useDispatch(
		employeesStoreName
	) as unknown as EmployeesStoreDispatch;

	if ( selectedIds.length === 0 ) {
		return null;
	}

	return (
		<div
			role="region"
			aria-label={ __( 'Bulk actions', 'erp' ) }
			className="pointer-events-auto fixed bottom-6 left-1/2 z-40 -translate-x-1/2"
		>
			<div className="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-900 px-3 py-2 text-xs text-slate-100 shadow-lg ring-1 ring-black/5">
				<span className="font-medium">
					{ sprintf(
						/* translators: %d: selected people count */
						__( '%d selected', 'erp' ),
						selectedIds.length
					) }
				</span>
				<span aria-hidden="true" className="h-4 w-px bg-slate-700" />
				<button
					type="button"
					className="inline-flex h-7 items-center gap-1.5 rounded-md border border-slate-700 bg-slate-800 px-2 font-medium text-slate-100 hover:bg-slate-700"
				>
					<Trash2 size={ 12 } strokeWidth={ 1.75 } aria-hidden="true" />
					{ __( 'Move to trash', 'erp' ) }
				</button>
				<button
					type="button"
					onClick={ () => setSelectedIds( [] ) }
					aria-label={ __( 'Clear selection', 'erp' ) }
					className="inline-flex size-7 items-center justify-center rounded-md text-slate-300 hover:bg-slate-800 hover:text-white"
				>
					<X size={ 14 } aria-hidden="true" />
				</button>
			</div>
		</div>
	);
}
