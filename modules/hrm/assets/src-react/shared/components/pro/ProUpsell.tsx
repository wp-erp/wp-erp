/**
 * Pro upsell affordance for the HR React admin.
 *
 * When the ERP Pro plugin is NOT installed (`boot.isPro === false`), pro-only
 * nav items still render — but as a "Pro" badge that opens an upgrade dialog
 * instead of navigating, mirroring the legacy admin's `pro_popup` menu badge.
 *
 * The dialog is a faithful React port of the legacy pro-popup
 * (`includes/Admin/views/erp-pro-popup-modal.php`): the two-column promo with
 * the diamond mark, orange "Upgrade to" heading, the same four feature bullets,
 * the illustration slider, the orange "Upgrade to PRO" button and the trust row.
 */

import {
	Dialog,
	DialogContent,
} from '@wedevs/plugin-ui';
import { Check, Crown } from 'lucide-react';
import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import type { JSX, ReactNode } from 'react';

import { useBoot } from '@/shared/hooks/useBoot';
import { __ } from '@/shared/i18n';

/** WP-ERP pricing page (same target as the legacy pro-popup "Upgrade to PRO" button). */
const UPGRADE_URL = 'https://wperp.com/pricing/?utm_source=wpdashboard&utm_medium=hr-react-nav&utm_campaign=pro-upsell';

/** Illustration slides shown on the right (same asset set as the legacy popup). */
const SLIDES = [ 'erp-pro.svg', 'crm.svg', 'accounting.svg', 'woo.svg' ];

interface ProUpsellContextValue {
	/** Open the upgrade dialog (the optional feature name is accepted but the
	 *  legacy promo copy is feature-agnostic, so it is not shown). */
	readonly openUpsell: ( feature?: string ) => void;
}

const ProUpsellContext = createContext< ProUpsellContextValue | null >( null );

/** Access the upsell opener. Safe no-op when used outside a provider. */
export function useProUpsell(): ProUpsellContextValue {
	return useContext( ProUpsellContext ) ?? { openUpsell: () => undefined };
}

/** The small amber "Pro" pill rendered next to a locked nav item. */
export function ProBadge( { className }: { className?: string } ): JSX.Element {
	return (
		<span
			className={ [
				'inline-flex shrink-0 items-center gap-0.5 rounded-full bg-amber-100 px-1.5 py-0.5',
				'text-[10px] font-semibold uppercase leading-none tracking-wide text-amber-700',
				className ?? '',
			].join( ' ' ) }
		>
			<Crown size={ 10 } strokeWidth={ 2.25 } aria-hidden="true" />
			{ __( 'Pro', 'erp' ) }
		</span>
	);
}

/** Feature bullets — verbatim copy + bold emphasis from the legacy pro-popup. */
const FEATURES: readonly ReactNode[] = [
	<>
		<strong>{ __( 'Unlock 12+ premium HR extensions', 'erp' ) }</strong>{ ' ' }
		{ __( 'and manage your employee’s', 'erp' ) }{ ' ' }
		<strong>{ __( 'recruitment, payroll, attendance,', 'erp' ) }</strong>{ ' ' }
		{ __( 'and more', 'erp' ) }
	</>,
	<>
		{ __( 'Nurture B2B & regular clients with', 'erp' ) }{ ' ' }
		<strong>{ __( '8+ CRM Integrations', 'erp' ) }</strong>{ ' ' }
		{ __( 'like -', 'erp' ) }{ ' ' }
		<strong>{ __( 'HubSpot, Mailchimp, Salesforce, Help Scout,', 'erp' ) }</strong>{ ' ' }
		{ __( 'etc.', 'erp' ) }
	</>,
	<>
		{ __( 'From', 'erp' ) }{ ' ' }
		<strong>{ __( 'creating invoice to calculating taxes', 'erp' ) }</strong>;{ ' ' }
		{ __( 'take full control of your company’s finances with the', 'erp' ) }{ ' ' }
		<strong>{ __( 'Accounting module', 'erp' ) }</strong>
	</>,
	<>
		<strong>{ __( 'Boost your WooCommerce store', 'erp' ) }</strong>{ ' ' }
		{ __( 'with powerful', 'erp' ) }{ ' ' }
		<strong>{ __( 'CRM and Accounting', 'erp' ) }</strong>{ ' ' }
		{ __( 'premium integrations', 'erp' ) }
	</>,
];

const TRUST: readonly string[] = [
	__( '10,000+ successful businesses', 'erp' ),
	__( '14 days no questions asked refund policy', 'erp' ),
	__( 'Industry leading 24x7 support', 'erp' ),
];

/** Right-column illustration slider (auto-advancing, click-to-jump dots). */
function ProSlides( { base }: { base: string } ): JSX.Element {
	const [ index, setIndex ] = useState( 0 );

	useEffect( () => {
		const timer = window.setInterval( () => {
			setIndex( ( prev ) => ( prev + 1 ) % SLIDES.length );
		}, 3500 );
		return () => window.clearInterval( timer );
	}, [] );

	return (
		<div className="flex h-full flex-col items-center justify-center gap-6 rounded-xl bg-[#eaf3ff] p-6">
			<div className="relative flex aspect-square w-full max-w-90 items-center justify-center">
				{ SLIDES.map( ( slide, i ) => (
					<img
						key={ slide }
						src={ `${ base }/${ slide }` }
						alt=""
						aria-hidden="true"
						className={ [
							'absolute inset-0 m-auto max-h-full max-w-full object-contain transition-opacity duration-500',
							i === index ? 'opacity-100' : 'opacity-0',
						].join( ' ' ) }
					/>
				) ) }
			</div>
			<div className="flex items-center gap-2">
				{ SLIDES.map( ( slide, i ) => (
					<button
						key={ slide }
						type="button"
						aria-label={ `${ __( 'Slide', 'erp' ) } ${ i + 1 }` }
						onClick={ () => setIndex( i ) }
						className={ [
							'size-2 rounded-full transition-colors',
							i === index ? 'bg-primary' : 'bg-border',
						].join( ' ' ) }
					/>
				) ) }
			</div>
		</div>
	);
}

/**
 * Wrap the HR app so any nav item can open the shared upgrade dialog.
 */
export function ProUpsellProvider( { children }: { children: ReactNode } ): JSX.Element {
	const [ open, setOpen ] = useState( false );
	const boot = useBoot();
	const base = boot.assets.proPopupUrl ?? '';

	const openUpsell = useCallback( (): void => {
		setOpen( true );
	}, [] );

	const value = useMemo< ProUpsellContextValue >( () => ( { openUpsell } ), [ openUpsell ] );

	return (
		<ProUpsellContext.Provider value={ value }>
			{ children }
			<Dialog open={ open } onOpenChange={ setOpen }>
				<DialogContent className="max-h-[92vh] gap-0 overflow-y-auto rounded-[10px] p-0 sm:max-w-4xl">
					<div className="grid grid-cols-1 gap-8 p-8 md:grid-cols-[1.1fr_1fr]">
						{ /* Left — promo copy */ }
						<div className="flex flex-col">
							{ base ? (
								<img src={ `${ base }/pro-diamond.svg` } alt="" aria-hidden="true" className="mb-4 size-12" />
							) : (
								<span className="mb-4 flex size-12 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
									<Crown size={ 24 } strokeWidth={ 2 } aria-hidden="true" />
								</span>
							) }

							<h2 className="m-0 text-3xl font-bold leading-tight tracking-tight text-foreground">
								<span className="text-[#f7941d]">{ __( 'Upgrade to', 'erp' ) }</span>{ ' ' }
								{ __( 'WP ERP', 'erp' ) } <strong>{ __( 'Pro', 'erp' ) }</strong>
							</h2>
							<p className="mb-6 mt-2 text-base text-muted-foreground">
								{ __( 'to experience even more powerful features 🎉', 'erp' ) }
							</p>

							<ul className="m-0 flex flex-col gap-4">
								{ FEATURES.map( ( item, i ) => (
									<li key={ i } className="flex items-start gap-3 text-sm leading-relaxed text-foreground">
										<span className="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white">
											<Check size={ 12 } strokeWidth={ 3 } aria-hidden="true" />
										</span>
										<span>{ item }</span>
									</li>
								) ) }
							</ul>

							<a
								href={ UPGRADE_URL }
								target="_blank"
								rel="noreferrer noopener"
								className="mt-8 inline-flex h-12 w-fit items-center gap-2 rounded-md bg-[#f7941d] px-7 text-sm font-bold text-white transition-colors hover:bg-[#e0820f]"
							>
								{ __( 'Upgrade to PRO', 'erp' ) }
								<Crown size={ 16 } strokeWidth={ 2.25 } aria-hidden="true" />
							</a>
						</div>

						{ /* Right — illustration slider */ }
						<div className="hidden md:block">
							<ProSlides base={ base } />
						</div>
					</div>

					<div className="flex flex-wrap items-center justify-between gap-4 border-t border-border px-8 py-4">
						{ TRUST.map( ( item ) => (
							<span key={ item } className="inline-flex items-center gap-1.5 text-xs text-muted-foreground">
								<Check size={ 14 } strokeWidth={ 2.5 } className="text-emerald-500" aria-hidden="true" />
								{ item }
							</span>
						) ) }
					</div>
				</DialogContent>
			</Dialog>
		</ProUpsellContext.Provider>
	);
}
