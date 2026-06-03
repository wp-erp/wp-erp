/**
 * User menu — plugin-ui Avatar inside a DropdownMenu.
 *
 * Default items: "My profile" (route slot), "Log out" (wp-login.php).
 * Pro injects items via `wp.hooks.applyFilters('erp_hr.user_menu.items', items)`
 * — wired in the next iteration.
 */

import {
	Avatar,
	AvatarFallback,
	AvatarImage,
	Button,
	DropdownMenu,
	DropdownMenuContent,
	DropdownMenuItem,
	DropdownMenuLabel,
	DropdownMenuSeparator,
	DropdownMenuTrigger,
} from '@wedevs/plugin-ui';
import { LogOut, UserCircle2 } from 'lucide-react';
import type { JSX } from 'react';

import { __ } from '@/shared/i18n';
import { useBoot } from '@/shared/hooks/useBoot';

export function UserMenu(): JSX.Element {
	const boot = useBoot();

	const initials =
		boot.displayName
			?.split( /\s+/ )
			.map( ( part ) => part.charAt( 0 ).toUpperCase() )
			.slice( 0, 2 )
			.join( '' ) || 'U';

	return (
		<DropdownMenu>
			<DropdownMenuTrigger
				render={
					<Button variant="ghost" size="sm" className="gap-2 px-1.5 h-9">
						<Avatar className="size-8">
							{ boot.avatarUrl ? (
								<AvatarImage src={ boot.avatarUrl } alt="" />
							) : null }
							<AvatarFallback>{ initials }</AvatarFallback>
						</Avatar>
						<span className="text-sm text-foreground hidden md:inline">
							{ boot.displayName }
						</span>
					</Button>
				}
			/>
			<DropdownMenuContent align="end" className="min-w-56">
				<DropdownMenuLabel className="text-muted-foreground">
					{ boot.email || boot.displayName }
				</DropdownMenuLabel>
				<DropdownMenuSeparator />
				<DropdownMenuItem disabled className="gap-2">
					<UserCircle2 size={ 16 } aria-hidden="true" />
					{ __( 'My profile', 'erp' ) }
				</DropdownMenuItem>
				<DropdownMenuItem
					className="gap-2 text-destructive focus:text-destructive"
					onClick={ () => {
						window.location.assign( '/wp-login.php?action=logout' );
					} }
				>
					<LogOut size={ 16 } aria-hidden="true" />
					{ __( 'Log out', 'erp' ) }
				</DropdownMenuItem>
			</DropdownMenuContent>
		</DropdownMenu>
	);
}
