<?php
/**
 * WP-ERP HR — first-run "new HR experience" notice.
 *
 * Announces the redesigned React admin once, on the HR pages, while the React
 * engine is active. Reassures that the previous (legacy) UI is one click away
 * via the existing per-page engine switch (UiEngineResolver). Dismissed per user
 * (user-meta), so it never nags after the first acknowledgement.
 *
 * Closes the "silent cutover" gap — see revamp-observations §4.2 / matrix #10.
 * The legacy affordance itself already exists (AdminMenu renders the switch);
 * this only adds the one-time announcement.
 */

namespace WeDevs\ERP\HRM\Admin;

defined( 'ABSPATH' ) || exit;

final class WelcomeNotice {

	public const DISMISS_ACTION = 'dismiss_hr_welcome';
	public const NONCE_NAME     = 'erp_hr_welcome';
	public const USERMETA_KEY   = 'erp_hr_welcome_dismissed';

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	private function __construct() {}

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function register_hooks(): void {
		add_action( 'admin_init', [ $this, 'handle_dismiss' ] );
		add_action( 'admin_notices', [ $this, 'render' ] );
	}

	/**
	 * Persist the dismissal for the current user, then redirect back to the bare
	 * HR page (drops the dismiss params so a refresh doesn't re-fire).
	 */
	public function handle_dismiss(): void {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ( $_GET['erp_action'] ?? '' ) !== self::DISMISS_ACTION ) {
			return;
		}

		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE_NAME ) ) {
			return;
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}

		update_user_meta( $user_id, self::USERMETA_KEY, 1 );

		// Background request from the notice's X button: no redirect, no page.
		if ( ! empty( $_GET['erp_silent'] ) ) {
			status_header( 204 );
			exit;
		}

		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : 'erp-hr';
		wp_safe_redirect( add_query_arg( 'page', $page, admin_url( 'admin.php' ) ) );
		exit;
		// phpcs:enable
	}

	/**
	 * Render the dismissible notice — only on an HR page, only while React is the
	 * active engine, only for users who can see HR, and only once per user.
	 */
	public function render(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		if ( strpos( $page, 'erp-hr' ) !== 0 ) {
			return;
		}

		if ( ! current_user_can( 'erp_list_employee' ) ) {
			return;
		}

		$resolver = UiEngineResolver::instance();
		if ( UiEngineResolver::ENGINE_REACT !== $resolver->resolve_engine( $page ) ) {
			return;
		}

		$user_id = get_current_user_id();
		if ( ! $user_id || get_user_meta( $user_id, self::USERMETA_KEY, true ) ) {
			return;
		}

		// The notice promises the user can switch back at any time. Under a
		// site-wide force that is not true, so say nothing rather than mislead.
		if ( $resolver->is_engine_forced() ) {
			return;
		}

		$legacy_url  = $resolver->switch_url( $page, 'legacy' );
		$dismiss_url = wp_nonce_url(
			add_query_arg(
				[ 'page' => $page, 'erp_action' => self::DISMISS_ACTION ],
				admin_url( 'admin.php' )
			),
			self::NONCE_NAME
		);
		?>
		<div id="erp-hr-welcome-notice" class="notice notice-info is-dismissible">
			<p>
				<strong><?php esc_html_e( 'Welcome to the new HR experience.', 'erp' ); ?></strong>
				<?php esc_html_e( 'This is the redesigned HR admin. You can switch back to the previous version anytime.', 'erp' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( $legacy_url ); ?>" class="button button-secondary"><?php esc_html_e( 'View previous version', 'erp' ); ?></a>
				<a href="<?php echo esc_url( $dismiss_url ); ?>" class="button-link" style="margin-left:8px;"><?php esc_html_e( 'Got it, dismiss', 'erp' ); ?></a>
			</p>
		</div>
		<?php
		// The core X only hides the notice. Record the dismissal too, the same as
		// the "Got it" link, so it does not come back on the next page load.
		$script = sprintf(
			'document.addEventListener("click",function(e){var b=e.target&&e.target.closest?e.target.closest("#erp-hr-welcome-notice .notice-dismiss"):null;if(b&&window.fetch){window.fetch(%s,{credentials:"same-origin"});}});',
			wp_json_encode(
				esc_url_raw(
					add_query_arg(
						[
							'page'       => $page,
							'erp_action' => self::DISMISS_ACTION,
							'erp_silent' => 1,
							'_wpnonce'   => wp_create_nonce( self::NONCE_NAME ),
						],
						admin_url( 'admin.php' )
					)
				)
			)
		);

		if ( function_exists( 'wp_print_inline_script_tag' ) ) {
			wp_print_inline_script_tag( $script );
		} else {
			echo '<script>' . $script . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from wp_json_encode( esc_url_raw() ).
		}
	}
}
