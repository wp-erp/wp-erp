/**
 * Inline "+ Add new" affordance rendered beside a dependency select's label.
 *
 * Lets a user create a prerequisite (a leave type, a department…) without
 * leaving the form they are filling — it opens the same create modal the
 * standalone setup screen uses, and the host wires the new record back into the
 * select. Pair it with the `labelAction` slot on `SmartSelectField`/`FieldShell`.
 */

import { Plus } from 'lucide-react';
import type { JSX } from 'react';

interface QuickAddButtonProps {
	readonly label:     string;
	readonly onClick:   () => void;
	readonly disabled?: boolean | undefined;
}

export function QuickAddButton( { label, onClick, disabled }: QuickAddButtonProps ): JSX.Element {
	return (
		<button
			type="button"
			onClick={ onClick }
			disabled={ disabled }
			className="inline-flex shrink-0 items-center gap-1 text-xs font-medium text-primary underline-offset-2 hover:underline disabled:opacity-50"
		>
			<Plus size={ 13 } aria-hidden="true" />
			{ label }
		</button>
	);
}
