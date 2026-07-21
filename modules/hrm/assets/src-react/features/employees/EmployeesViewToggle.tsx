/**
 * List / grid view switch for the Employees page.
 *
 * Purely a presentation toggle — both views render the same `useEmployeesQuery`
 * data, filters, selection and pagination. The list (table) view is unchanged;
 * the grid view is an additive card layout.
 */

import { LayoutGrid, List } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

export type EmployeesView = 'list' | 'grid';

interface EmployeesViewToggleProps {
	readonly value:    EmployeesView;
	readonly onChange: ( view: EmployeesView ) => void;
}

export function EmployeesViewToggle( { value, onChange }: EmployeesViewToggleProps ): JSX.Element {
	return (
		<div role="group" aria-label={ __( 'View', 'erp' ) } className="inline-flex h-10 items-center gap-0.5 overflow-hidden rounded-md border border-border bg-card p-0.5">
			<ToggleButton
				active={ value === 'list' }
				label={ __( 'List view', 'erp' ) }
				onClick={ () => onChange( 'list' ) }
			>
				<List size={ 16 } aria-hidden="true" />
			</ToggleButton>
			<ToggleButton
				active={ value === 'grid' }
				label={ __( 'Grid view', 'erp' ) }
				onClick={ () => onChange( 'grid' ) }
			>
				<LayoutGrid size={ 16 } aria-hidden="true" />
			</ToggleButton>
		</div>
	);
}

interface ToggleButtonProps {
	readonly active:   boolean;
	readonly label:    string;
	readonly onClick:  () => void;
	readonly children: JSX.Element;
}

function ToggleButton( { active, label, onClick, children }: ToggleButtonProps ): JSX.Element {
	return (
		<button
			type="button"
			aria-label={ label }
			aria-pressed={ active }
			title={ label }
			onClick={ onClick }
			className={ [
				'inline-flex size-8 items-center justify-center rounded transition-colors',
				active ? 'bg-muted text-foreground' : 'text-muted-foreground hover:text-foreground',
			].join( ' ' ) }
		>
			{ children }
		</button>
	);
}
