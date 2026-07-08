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
	DropdownMenuGroup,
	DropdownMenuItem,
	DropdownMenuLabel,
	DropdownMenuSeparator,
	DropdownMenuTrigger,
} from '@wedevs/plugin-ui';
import { LogOut, PanelLeft, PanelTop, UserCircle2 } from 'lucide-react';
import type { JSX } from 'react';
import { useNavigate } from 'react-router-dom';

import { __ } from '@/shared/i18n';
import { useBoot } from '@/shared/hooks/useBoot';
import { useNavLayout } from '@/shared/hooks/useNavLayout';

export function UserMenu(): JSX.Element {
	const boot = useBoot();
	const navigate = useNavigate();
	const { layout, toggle } = useNavLayout();

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
					<Button variant="ghost" size="sm" className="px-1.5 h-9">
						<Avatar className="size-8">
							{ boot.avatarUrl ? (
								<AvatarImage src={ boot.avatarUrl } alt="" />
							) : null }
							<AvatarFallback>{ initials }</AvatarFallback>
						</Avatar>
					</Button>
				}
			/>
			<DropdownMenuContent align="end" className="min-w-56">
				<DropdownMenuGroup>
					<DropdownMenuLabel className="text-muted-foreground">
						{ boot.email || boot.displayName }
					</DropdownMenuLabel>
				</DropdownMenuGroup>
				<DropdownMenuSeparator />
				<DropdownMenuItem className="gap-2" onClick={ () => navigate( '/my-profile' ) }>
					<UserCircle2 size={ 16 } aria-hidden="true" />
					{ __( 'My profile', 'erp' ) }
				</DropdownMenuItem>
				<DropdownMenuItem className="gap-2" onClick={ toggle }>
					{ layout === 'sidebar' ? (
						<PanelTop size={ 16 } aria-hidden="true" />
					) : (
						<PanelLeft size={ 16 } aria-hidden="true" />
					) }
					{ layout === 'sidebar'
						? __( 'Top navigation', 'erp' )
						: __( 'Sidebar navigation', 'erp' ) }
				</DropdownMenuItem>
				<DropdownMenuSeparator />
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
