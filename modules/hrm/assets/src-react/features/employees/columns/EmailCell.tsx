/**
 * Email cell — clickable mailto link in brand-blue.
 *
 * Figma node `502:22929` (283 px column).
 */

import type { JSX } from 'react';

import { CopyButton } from '@/shared/components/CopyButton';
import { __ } from '@/shared/i18n';
import type { EmployeeListItem } from '@/stores/employees';

interface EmailCellProps {
	readonly row: EmployeeListItem;
}

export function EmailCell( { row }: EmailCellProps ): JSX.Element {
	if ( ! row.email ) {
		return <span className="text-muted-foreground">—</span>;
	}
	return (
		<span className="group/cell inline-flex items-center gap-1 whitespace-nowrap">
			<a href={ `mailto:${ row.email }` } className="text-sm text-primary hover:underline">
				{ row.email }
			</a>
			<CopyButton value={ row.email } label={ __( 'Copy email address', 'erp' ) } />
		</span>
	);
}
