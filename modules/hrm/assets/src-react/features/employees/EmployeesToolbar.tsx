/**
 * Page header — title + right cluster with toolbar items.
 *
 * Free defaults: "Add new employee" CTA. Pro injects via
 * `wp.hooks.applyFilters('erp_hr.employees.toolbar_items', items, ctx)`.
 */

import {
	Button,
	DropdownMenu,
	DropdownMenuContent,
	DropdownMenuItem,
	DropdownMenuTrigger,
} from '@wedevs/plugin-ui';
import { ChevronDown, Plus, SquareArrowOutDownLeft, SquareArrowOutUpRight } from 'lucide-react';
import { useState } from 'react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';

import { EmployeeExportDialog } from './EmployeeExportDialog';
import { EmployeeImportDialog } from './EmployeeImportDialog';
import { useColumnContext } from './useColumnContext';
import { useEmployeeToolbarItems } from './useEmployeeToolbarItems';

const ICON_MAP: Record< string, typeof Plus > = {
	Plus,
};

export function EmployeesToolbar(): JSX.Element {
	const items = useEmployeeToolbarItems();
	const { can } = useColumnContext();
	const [ importOpen, setImportOpen ] = useState( false );
	const [ exportOpen, setExportOpen ] = useState( false );

	return (
		<header className="mb-6 flex items-center justify-between gap-4">
			<h1 className="text-2xl font-bold leading-8 text-foreground">
				{ __( 'Employees', 'erp' ) }
			</h1>
			<div className="flex items-center gap-3">
				{ can( 'erp_create_employee' ) ? (
					<>
						{ /* Split button: Export is the primary action; the chevron
						   reveals Import (mirrors the HRM 2024 toolbar design). */ }
						<div className="inline-flex h-10 items-center rounded-md border border-border bg-background shadow-sm">
							<button
								type="button"
								onClick={ () => setExportOpen( true ) }
								className="inline-flex h-full items-center gap-2 rounded-l-md py-2 pl-3 pr-2.5 text-sm font-medium leading-5 text-foreground transition-colors hover:bg-muted/50"
							>
								<SquareArrowOutUpRight size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
								<span>{ __( 'Export', 'erp' ) }</span>
							</button>
							<span className="h-4 w-px shrink-0 bg-border" aria-hidden="true" />
							<DropdownMenu>
								<DropdownMenuTrigger
									render={
										<Button
											variant="ghost"
											size="icon"
											className="h-full w-auto rounded-l-none rounded-r-md border-0 px-2 text-foreground shadow-none"
											aria-label={ __( 'More import / export options', 'erp' ) }
										>
											<ChevronDown size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
										</Button>
									}
								/>
								<DropdownMenuContent align="end" className="min-w-44">
									<DropdownMenuItem className="gap-2" onClick={ () => setImportOpen( true ) }>
										<SquareArrowOutDownLeft size={ 14 } aria-hidden="true" />
										{ __( 'Import', 'erp' ) }
									</DropdownMenuItem>
								</DropdownMenuContent>
							</DropdownMenu>
						</div>
						<EmployeeImportDialog open={ importOpen } onClose={ () => setImportOpen( false ) } />
						<EmployeeExportDialog open={ exportOpen } onClose={ () => setExportOpen( false ) } />
					</>
				) : null }
				{ items.map( ( item ) => {
					const Icon = item.icon ? ICON_MAP[ item.icon ] : undefined;
					const variant: 'default' | 'secondary' | 'ghost' = (() => {
						switch ( item.variant ) {
							case 'primary':
								return 'default';
							case 'secondary':
								return 'secondary';
							case 'ghost':
								return 'ghost';
							default:
								return 'default';
						}
					})();
					return (
						<Button
							key={ item.id }
							variant={ variant }
							size="default"
							className="inline-flex h-10 items-center gap-2 rounded-md px-5 text-sm font-medium leading-5 shadow-sm"
							onClick={ () => item.onSelect() }
						>
							{ Icon ? <Icon size={ 16 } strokeWidth={ 2 } aria-hidden="true" /> : null }
							<span>{ item.label }</span>
						</Button>
					);
				} ) }
			</div>
		</header>
	);
}
