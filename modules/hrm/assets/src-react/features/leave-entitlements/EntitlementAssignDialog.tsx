/**
 * Assign-entitlement dialog.
 *
 * Mirrors the legacy assign form (`FormHandler::leave_entitlement()`): pick a
 * policy, choose whether to assign to a single employee or to every employee
 * matching the policy's scope, and (for single) pick the employee. The employee
 * list is resolved server-side from the policy scope. An optional comment is
 * stored on each entitlement.
 */

import {
	Alert,
	AlertDescription,
	Button,
	Dialog,
	DialogContent,
	DialogDescription,
	DialogFooter,
	DialogHeader,
	DialogTitle,
	Label,
	RadioGroup,
	RadioGroupItem,
} from '@wedevs/plugin-ui';
import { useEffect, useState } from 'react';
import type { JSX } from 'react';

import { DependencyHint } from '@/shared/components/DependencyHint';
import { __ } from '@/shared/i18n';

import { SmartSelectField, TextareaField } from '../employee-create/fields';
import type { Option } from '../employee-create/options';
import type { EntitlementAssignInput, IdOption } from './types';

interface EntitlementAssignDialogProps {
	readonly open:          boolean;
	readonly policies:      readonly IdOption[];
	readonly busy:          boolean;
	readonly error:         string | null;
	readonly onClose:       () => void;
	readonly onSubmit:      ( payload: EntitlementAssignInput ) => void;
	readonly loadEmployees: ( policyId: number ) => Promise< readonly IdOption[] >;
}

export function EntitlementAssignDialog( {
	open,
	policies,
	busy,
	error,
	onClose,
	onSubmit,
	loadEmployees,
}: EntitlementAssignDialogProps ): JSX.Element {
	const [ policyId, setPolicyId ]   = useState( '' );
	const [ mode, setMode ]           = useState< 'single' | 'all' >( 'single' );
	const [ employeeId, setEmployeeId ] = useState( '' );
	const [ comment, setComment ]     = useState( '' );
	const [ employees, setEmployees ] = useState< readonly IdOption[] >( [] );
	const [ empLoading, setEmpLoading ] = useState( false );
	const [ formErr, setFormErr ]     = useState< { policy?: string | undefined; employee?: string | undefined } >( {} );

	// Reset on open.
	useEffect( () => {
		if ( ! open ) {
			return;
		}
		setPolicyId( '' );
		setMode( 'single' );
		setEmployeeId( '' );
		setComment( '' );
		setEmployees( [] );
		setFormErr( {} );
	}, [ open ] );

	// Reload the matching-employee list whenever the policy changes.
	useEffect( () => {
		const pid = Number( policyId || 0 );
		if ( ! pid ) {
			setEmployees( [] );
			return;
		}
		let active = true;
		setEmpLoading( true );
		void loadEmployees( pid )
			.then( ( list ) => {
				if ( active ) {
					setEmployees( list );
				}
			} )
			.finally( () => {
				if ( active ) {
					setEmpLoading( false );
				}
			} );
		return () => {
			active = false;
		};
	}, [ policyId, loadEmployees ] );

	const policyOpts: Option[]   = policies.map( ( p ) => ( { value: String( p.value ), label: p.label } ) );
	const employeeOpts: Option[] = employees.map( ( e ) => ( { value: String( e.value ), label: e.label } ) );

	function handleSubmit( e: React.FormEvent ): void {
		e.preventDefault();
		const next: { policy?: string | undefined; employee?: string | undefined } = {};
		if ( ! policyId ) {
			next.policy = __( 'Please select a leave policy.', 'erp' );
		}
		if ( mode === 'single' && ! employeeId ) {
			next.employee = __( 'Please select an employee.', 'erp' );
		}
		if ( Object.keys( next ).length > 0 ) {
			setFormErr( next );
			return;
		}

		onSubmit( {
			policy_id:       Number( policyId ),
			assignment_to:   mode,
			single_employee: mode === 'single' ? Number( employeeId ) : undefined,
			comment:         comment.trim(),
		} );
	}

	return (
		<Dialog open={ open } onOpenChange={ ( next ) => ( next || busy ? undefined : onClose() ) }>
			<DialogContent className="gap-4 rounded-[10px] p-6 sm:max-w-lg">
				<DialogHeader>
					<DialogTitle className="m-0 text-2xl font-bold leading-tight tracking-tight text-foreground">
						{ __( 'Assign Leave Policy', 'erp' ) }
					</DialogTitle>
					<DialogDescription>
						{ __( 'Grant a policy’s leave days to a single employee or to everyone matching the policy’s scope.', 'erp' ) }
					</DialogDescription>
				</DialogHeader>
				<div className="h-px w-full bg-border" />

				{ policies.length === 0 ? (
					<div className="flex flex-col gap-4">
						<DependencyHint
							message={ __( 'No leave policy exists yet. A policy is required before you can assign an entitlement.', 'erp' ) }
							steps={ [
								{ label: __( '1. Create a leave type', 'erp' ), path: '/leave/types' },
								{ label: __( '2. Create a leave policy', 'erp' ), path: '/leave/policies' },
							] }
							onBeforeNavigate={ onClose }
						/>
						<DialogFooter>
							<Button type="button" variant="outline" className="h-10 px-6" onClick={ onClose }>
								{ __( 'Close', 'erp' ) }
							</Button>
						</DialogFooter>
					</div>
				) : (
				<form onSubmit={ handleSubmit } className="flex min-w-0 flex-col gap-4">
					<SmartSelectField
						id="entitlement_policy"
						label={ __( 'Leave Policy', 'erp' ) }
						required
						options={ policyOpts }
						value={ policyId }
						onChange={ ( v ) => {
							setPolicyId( v );
							setEmployeeId( '' );
							setFormErr( ( p ) => ( { ...p, policy: undefined } ) );
						} }
						error={ formErr.policy }
						placeholder={ __( '- Select -', 'erp' ) }
						searchPlaceholder={ __( 'Search policies…', 'erp' ) }
						emptyMessage={ __( 'No policies found.', 'erp' ) }
					/>

					<div className="flex flex-col gap-2.5">
						<Label className="text-sm font-medium text-foreground">{ __( 'Assign to', 'erp' ) }</Label>
						<RadioGroup
							value={ mode }
							onValueChange={ ( v ) => setMode( v === 'all' ? 'all' : 'single' ) }
							className="flex flex-col gap-2"
						>
							<label className="flex items-center gap-2 text-sm text-foreground">
								<RadioGroupItem value="single" />
								{ __( 'A single employee', 'erp' ) }
							</label>
							<label className="flex items-center gap-2 text-sm text-foreground">
								<RadioGroupItem value="all" />
								{ __( 'All employees matching the policy scope', 'erp' ) }
							</label>
						</RadioGroup>
					</div>

					{ mode === 'single' ? (
						<SmartSelectField
							id="entitlement_employee"
							label={ __( 'Employee', 'erp' ) }
							required
							options={ employeeOpts }
							value={ employeeId }
							onChange={ ( v ) => {
								setEmployeeId( v );
								setFormErr( ( p ) => ( { ...p, employee: undefined } ) );
							} }
							error={ formErr.employee }
							placeholder={
								! policyId
									? __( 'Select a policy first', 'erp' )
									: empLoading
									? __( 'Loading…', 'erp' )
									: __( '- Select -', 'erp' )
							}
							searchPlaceholder={ __( 'Search employees…', 'erp' ) }
							emptyMessage={ __( 'No matching employees.', 'erp' ) }
						/>
					) : null }

					<TextareaField
						id="entitlement_comment"
						label={ __( 'Comment', 'erp' ) }
						value={ comment }
						onChange={ setComment }
						rows={ 2 }
					/>

					{ error ? (
						<Alert variant="destructive">
							<AlertDescription>{ error }</AlertDescription>
						</Alert>
					) : null }

					<DialogFooter className="gap-5 sm:gap-5">
						<Button type="button" variant="outline" className="h-10 px-6" disabled={ busy } onClick={ onClose }>
							{ __( 'Cancel', 'erp' ) }
						</Button>
						<Button type="submit" className="h-10 px-6" disabled={ busy }>
							{ busy ? __( 'Assigning…', 'erp' ) : __( 'Assign', 'erp' ) }
						</Button>
					</DialogFooter>
				</form>
				) }
			</DialogContent>
		</Dialog>
	);
}
