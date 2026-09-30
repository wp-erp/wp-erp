/**
 * List / grid view switch for the Employees page.
 *
 * Purely a presentation toggle — both views render the same `useEmployeesQuery`
 * data, filters, selection and pagination. The list (table) view is unchanged;
 * the grid view is an additive card layout. The markup is the shared
 * `ViewToggle`.
 */

import type { JSX } from 'react';

import { ViewToggle } from '@/shared/components/ViewToggle';

export type EmployeesView = 'list' | 'grid';

interface EmployeesViewToggleProps {
	readonly value:    EmployeesView;
	readonly onChange: ( view: EmployeesView ) => void;
}

export function EmployeesViewToggle( { value, onChange }: EmployeesViewToggleProps ): JSX.Element {
	return <ViewToggle value={ value } onChange={ onChange } />;
}
