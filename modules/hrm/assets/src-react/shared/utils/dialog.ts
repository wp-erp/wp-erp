/**
 * Guards for dialogs that host their own popups (selects, date pickers).
 *
 * A SmartSelect popup is itself a Base UI dialog, portalled to `<body>` and
 * stacked above the form. Dismissing it — Escape, or a press that lands outside
 * it — is reported to *every* open layer, so the host dialog closed too and the
 * part-filled form was lost. `disablePointerDismissal` already covers the press;
 * this covers the key.
 */

/** Selector for an open SmartSelect popup, whichever dialog owns it. */
const OPEN_POPUP = '[data-slot="smart-select-content"][data-open]';

/**
 * True while a nested select popup is open, i.e. while a dismiss belongs to
 * that popup and not to the dialog behind it.
 */
export function hasOpenNestedPopup(): boolean {
	return typeof document !== 'undefined' && document.querySelector( OPEN_POPUP ) !== null;
}

/**
 * `onOpenChange` handler for a form dialog: closes on a genuine dismissal, and
 * ignores the one that really belongs to a nested popup.
 *
 * ```tsx
 * <Dialog open={ open } disablePointerDismissal onOpenChange={ dismissGuard( onClose, busy ) }>
 * ```
 *
 * @param onClose Called when the dialog should actually close.
 * @param busy    While true the dialog refuses to close (a save is in flight).
 */
export function dismissGuard( onClose: () => void, busy = false ) {
	return ( next: boolean ): void => {
		if ( next || busy || hasOpenNestedPopup() ) {
			return;
		}
		onClose();
	};
}
