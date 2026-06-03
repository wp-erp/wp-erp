/**
 * Shared chrome for the report pages, following the employee-profile design
 * system: a page title, a line/underline tab bar (`ReportsTabs`), and a
 * borderless card (`rounded-[10px] bg-card p-6 shadow-sm`) with a section title
 * + divider — identical to `EmployeeSinglePage`'s `DetailCard`.
 */

import type { JSX, ReactNode } from 'react';

import { CapabilityGate } from '@/shared/components/CapabilityGate';
import { ErrorBoundary } from '@/shared/components/ErrorBoundary';
import { __ } from '@/shared/i18n';

import { ReportsTabs } from './ReportsTabs';

interface ReportShellProps {
	readonly title:    string;
	readonly toolbar?: ReactNode;
	readonly children: ReactNode;
}

/** Page title + report tabs + profile-style card, gated on the HR-manager role. */
export function ReportShell( { title, toolbar, children }: ReportShellProps ): JSX.Element {
	return (
		<CapabilityGate caps={ [ 'erp_hr_manager' ] }>
			<ErrorBoundary>
				<div className="mx-auto w-full max-w-7xl space-y-6">
					<header>
						<h1 className="text-2xl font-bold leading-tight tracking-tight text-foreground">
							{ __( 'Reports', 'erp' ) }
						</h1>
					</header>

					<ReportsTabs />

					<section className="rounded-[10px] bg-card p-6 shadow-sm">
						<h2 className="text-lg font-bold leading-tight tracking-tight text-foreground">{ title }</h2>
						<div className="mb-5 mt-4 h-px w-full bg-border" />
						{ toolbar ? <div className="mb-5">{ toolbar }</div> : null }
						{ children }
					</section>
				</div>
			</ErrorBoundary>
		</CapabilityGate>
	);
}

interface ReportStateProps {
	readonly loading: boolean;
	readonly error:   string | null;
	readonly empty:   boolean;
	readonly emptyText?: string;
	readonly children: ReactNode;
}

/** Uniform loading / error / empty fallback used inside a report card. */
export function ReportState( { loading, error, empty, emptyText, children }: ReportStateProps ): JSX.Element {
	if ( error ) {
		return <p className="py-6 text-sm text-destructive">{ error }</p>;
	}
	if ( loading ) {
		return <p className="py-6 text-sm text-muted-foreground">{ __( 'Loading…', 'erp' ) }</p>;
	}
	if ( empty ) {
		return (
			<p className="py-10 text-center text-sm text-muted-foreground">
				{ emptyText ?? __( 'No records found.', 'erp' ) }
			</p>
		);
	}
	return <>{ children }</>;
}
