/**
 * The one empty state for the HR admin.
 *
 * Same composition as the Employees list's "No Employees Added Yet" frame
 * (`features/employees/EmployeesEmpty.tsx`, the Figma reference): a tinted
 * circular badge holding a glyph, a bold title, a muted line of help text and
 * an optional call to action. Every list, table and profile tab renders its
 * zero-rows case through this, so an empty screen looks the same everywhere.
 *
 * Two sizes:
 *   - `page`    : a whole list / table is empty (the Employees proportions).
 *   - `compact` : a card, profile tab or dialog section is empty.
 *
 * The small "+" pill on the badge only shows when there is something to add,
 * i.e. when an `action` is passed (override with `showPlus`).
 */

import { Inbox, Plus } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { JSX, ReactNode } from 'react';

interface EmptyStateProps {
	/** Glyph inside the badge. Defaults to an inbox. */
	readonly icon?: LucideIcon;
	readonly title: string;
	/** Muted help text under the title. */
	readonly description?: string;
	/** Call to action (a Button) rendered under the text. */
	readonly action?: ReactNode;
	readonly size?: 'page' | 'compact';
	/** Force the "+" pill on or off. Defaults to "on when there is an action". */
	readonly showPlus?: boolean;
	readonly className?: string;
}

export function EmptyState( {
	icon: Icon = Inbox,
	title,
	description,
	action,
	size = 'page',
	showPlus,
	className = '',
}: EmptyStateProps ): JSX.Element {
	const isPage  = 'page' === size;
	const hasPlus = showPlus ?? Boolean( action );

	return (
		<div
			className={ `flex flex-col items-center text-center ${
				isPage ? 'px-6 py-16' : 'px-4 py-10'
			} ${ className }` }
		>
			<div
				aria-hidden="true"
				className={ `relative flex shrink-0 items-center justify-center rounded-full bg-[#ECF4FF] dark:bg-primary/15 ${
					isPage ? 'size-52' : 'size-24'
				}` }
			>
				<Icon
					size={ isPage ? 72 : 36 }
					strokeWidth={ 1.75 }
					className="text-[#94A3B8]"
				/>
				{ hasPlus ? (
					<span
						className={ `absolute flex items-center justify-center rounded-full border-card bg-primary text-primary-foreground ${
							isPage
								? '-bottom-1 end-0 size-20 border-8'
								: '-bottom-1 -end-1 size-9 border-4'
						}` }
					>
						<Plus size={ isPage ? 28 : 14 } strokeWidth={ 2.5 } />
					</span>
				) : null }
			</div>

			{ isPage ? (
				<h2 className="m-0 mt-10 text-2xl font-bold leading-8 text-foreground">
					{ title }
				</h2>
			) : (
				// A compact empty sits under a card that already has its own
				// heading, so it is a paragraph: it must not add to the outline.
				<p className="m-0 mt-5 text-base font-semibold leading-6 text-foreground">
					{ title }
				</p>
			) }
			{ description ? (
				<p
					className={ `m-0 max-w-md text-sm text-muted-foreground ${
						isPage ? 'mt-3 leading-6' : 'mt-1 leading-5'
					}` }
				>
					{ description }
				</p>
			) : null }
			{ action ? (
				<div className={ isPage ? 'mt-8' : 'mt-5' }>{ action }</div>
			) : null }
		</div>
	);
}
