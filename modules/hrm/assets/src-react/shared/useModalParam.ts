/**
 * URL-synced modal state — keeps a modal's open/target in the hash query so a
 * browser refresh re-opens it and the view is deep-linkable. Reads/writes a
 * single query key off react-router's `useSearchParams`.
 *
 * Example: `const [form, setForm] = useModalParam('form')` →
 *   setForm('new') → `?form=new`; setForm('5') → `?form=5`; setForm(null) → removed.
 *
 * Use for create / edit / form modals. Do NOT use for delete/confirm prompts.
 */

import { useCallback } from 'react';
import { useSearchParams } from 'react-router-dom';

export function useModalParam( key: string ): [ string | null, ( value: string | null ) => void ] {
	const [ params, setParams ] = useSearchParams();
	const value = params.get( key );

	const setValue = useCallback(
		( next: string | null ): void => {
			setParams(
				( prev ) => {
					const out = new URLSearchParams( prev );
					if ( next === null ) {
						out.delete( key );
					} else {
						out.set( key, next );
					}
					return out;
				},
				{ replace: false }
			);
		},
		[ key, setParams ]
	);

	return [ value, setValue ];
}
