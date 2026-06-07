/**
 * Pro upsell affordance for the HR React admin.
 *
 * When the ERP Pro plugin is NOT installed (`boot.isPro === false`), pro-only
 * nav items still render — but as a "Pro" badge that opens an upgrade dialog
 * instead of navigating, mirroring the legacy admin's `pro_popup` menu badge
 * (`functions.php` → `<span class="pro-popup">Pro</span>` + upgrade modal).
 *
 * Pattern adapted from Dokan's React locked-feature upsell (a localized
 * `is_pro_exists` flag → a "Pro" pill → a click-triggered upgrade modal), reskinned
 * to the WP-ERP design system and pricing URL.
 */

import {
	Button,
	Dialog,
	DialogContent,
	DialogDescription,
	DialogFooter,
	DialogHeader,
	DialogTitle,
} from '@wedevs/plugin-ui';
import { Check, Crown } from 'lucide-react';
import { createContext, useCallback, useContext, useMemo, useState } from 'react';
import type { JSX, ReactNode } from 'react';

import { __, sprintf } from '@/shared/i18n';

/** WP-ERP pricing page (same target as the legacy pro-popup "Upgrade to PRO" button). */
const UPGRADE_URL = 'https://wperp.com/pricing/?utm_source=wpdashboard&utm_medium=hr-react-nav&utm_campaign=pro-upsell';

interface ProUpsellContextValue {
	/** Open the upgrade dialog, naming the feature the user tried to reach. */
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

// Verbatim marketing copy from the legacy pro-popup
// (`includes/Admin/views/erp-pro-popup-modal.php`), flattened from its inline
// markup so the React upsell shows the same content as the Vue/PHP one.
const FEATURES: readonly string[] = [
	__( 'Unlock 12+ premium HR extensions and manage your employee’s recruitment, payroll, attendance, and more', 'erp' ),
	__( 'Nurture B2B & regular clients with 8+ CRM Integrations like HubSpot, Mailchimp, Salesforce, Help Scout, etc.', 'erp' ),
	__( 'From creating invoice to calculating taxes; take full control of your company’s finances with the Accounting module', 'erp' ),
	__( 'Boost your WooCommerce store with powerful CRM and Accounting premium integrations', 'erp' ),
];

/**
 * Wrap the HR app so any nav item can open the shared upgrade dialog.
 */
export function ProUpsellProvider( { children }: { children: ReactNode } ): JSX.Element {
	const [ feature, setFeature ] = useState< string | null >( null );
	const [ open, setOpen ]       = useState( false );

	const openUpsell = useCallback( ( name?: string ): void => {
		setFeature( name ?? null );
		setOpen( true );
	}, [] );

	const value = useMemo< ProUpsellContextValue >( () => ( { openUpsell } ), [ openUpsell ] );

	return (
		<ProUpsellContext.Provider value={ value }>
			{ children }
			<Dialog open={ open } onOpenChange={ setOpen }>
				<DialogContent className="gap-0 rounded-[10px] p-0 sm:max-w-lg">
					<div className="flex flex-col items-center gap-3 rounded-t-[10px] bg-gradient-to-br from-primary/10 to-amber-100/40 px-6 pb-5 pt-8 text-center">
						<span className="flex size-12 items-center justify-center rounded-full bg-amber-100 text-amber-600">
							<Crown size={ 24 } strokeWidth={ 2 } aria-hidden="true" />
						</span>
						<DialogHeader className="gap-1">
							<DialogTitle className="m-0 text-2xl font-bold leading-tight tracking-tight text-foreground">
								{ __( 'Upgrade to WP ERP Pro', 'erp' ) }
							</DialogTitle>
							<DialogDescription className="text-sm text-muted-foreground">
								{ feature
									? sprintf(
										/* translators: %s: pro feature name. */
										__( '%s is a Pro feature. Upgrade to unlock it and more.', 'erp' ),
										feature
									)
									: __( 'to experience even more powerful features 🎉', 'erp' ) }
							</DialogDescription>
						</DialogHeader>
					</div>

					<ul className="m-0 flex flex-col gap-3 px-6 py-5">
						{ FEATURES.map( ( item ) => (
							<li key={ item } className="flex items-start gap-2.5 text-sm text-foreground">
								<span className="mt-0.5 flex size-4 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white">
									<Check size={ 11 } strokeWidth={ 3 } aria-hidden="true" />
								</span>
								{ item }
							</li>
						) ) }
					</ul>

					<DialogFooter className="gap-3 border-t border-border px-6 py-4 sm:gap-3">
						<Button type="button" variant="outline" className="h-10 px-6" onClick={ () => setOpen( false ) }>
							{ __( 'Maybe later', 'erp' ) }
						</Button>
						<Button
							type="button"
							className="h-10 px-6"
							onClick={ () => window.open( UPGRADE_URL, '_blank', 'noopener,noreferrer' ) }
						>
							{ __( 'Upgrade to Pro', 'erp' ) }
						</Button>
					</DialogFooter>
				</DialogContent>
			</Dialog>
		</ProUpsellContext.Provider>
	);
}
