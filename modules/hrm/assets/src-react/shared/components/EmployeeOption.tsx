/**
 * Employee row for a picker — avatar + label, matching the Employees table's
 * name cell (`features/employees/columns/NameCell.tsx`), so the same person
 * looks the same whether they appear in the table or in a dropdown.
 *
 * `SmartSelect` takes this via its `renderOption` prop; the multi-select
 * (`EmployeeMultiSelect`) renders it inside a `ComboboxItem`. The avatar rides
 * along on the option as `avatar` — `/erp/v2/employees` already returns
 * `avatar_url`, the pickers simply used to drop it.
 */

import { Avatar, AvatarFallback, AvatarImage } from '@wedevs/plugin-ui';
import type { JSX } from 'react';

import { makeInitials } from './PersonCell';

export interface EmployeeOptionData {
	readonly value:   string;
	readonly label:   string;
	readonly avatar?: string | undefined;
}

export function EmployeeOption( { option }: { readonly option: EmployeeOptionData } ): JSX.Element {
	return (
		<span className="flex min-w-0 items-center gap-2.5">
			<Avatar className="size-6 shrink-0">
				{ option.avatar ? <AvatarImage src={ option.avatar } alt="" /> : null }
				<AvatarFallback className="text-[10px]">{ makeInitials( option.label ) }</AvatarFallback>
			</Avatar>
			<span className="min-w-0 truncate">{ option.label }</span>
		</span>
	);
}

/**
 * Ready-made `renderOption` for `SmartSelect`. The option SmartSelect hands
 * back is its own `{ value, label }` shape, so the avatar is looked up from the
 * caller's list rather than read off that object.
 *
 * @param options the picker's own options, which carry `avatar`
 */
export function employeeOptionRenderer(
	options: readonly EmployeeOptionData[]
): ( option: { value: string; label: string } ) => JSX.Element {
	return ( option ) => {
		const match = options.find( ( o ) => o.value === option.value );

		return <EmployeeOption option={ { value: option.value, label: option.label, avatar: match?.avatar } } />;
	};
}
