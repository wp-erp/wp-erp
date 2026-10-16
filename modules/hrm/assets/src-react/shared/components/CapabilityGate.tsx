/**
 * Capability gate wrapper.
 *
 * Renders children when the current user has every required capability;
 * otherwise renders <Forbidden />. Empty `caps` array → always allowed.
 */

import type { JSX, ReactNode } from 'react';

import { useCan } from '@/shared/hooks/useCan';
import type { Capability } from '@/types/global';

import { Forbidden } from './Forbidden';

interface CapabilityGateProps {
	readonly caps:     readonly Capability[];
	readonly fallback?: ReactNode;
	readonly children: ReactNode;
}

export function CapabilityGate( { caps, fallback, children }: CapabilityGateProps ): JSX.Element {
	const allowed = useCan( caps.length > 0 ? caps : [] );

	if ( caps.length === 0 || allowed ) {
		return <>{ children }</>;
	}

	return <>{ fallback ?? <Forbidden /> }</>;
}
