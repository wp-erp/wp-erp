<?php
/**
 * Setup wizard class
 *
 * Walkthrough to the basic setup upon installation
 */

namespace WeDevs\ERP\Admin;

/**
 * The class
 */
class SetupWizard {

    /** @var string Currenct Step */
    private $step   = '';

    /** @var array Steps for the setup wizard */
    private $steps  = [];

    /**
     * Hook in tabs.
     */
    public function __construct() {

        // NOTE: Don't set 'erp_setup_wizard_ran' here in constructor!
        // This flag should only be set when onboarding is COMPLETED (via API),
        // not when the wizard page is accessed. Setting it here prevents re-accessing the wizard.
        // See OnboardingController.php line 181 where it's properly set on completion.
        // update_option( 'erp_setup_wizard_ran', '1' ); // REMOVED - causes redirect bug

        if ( apply_filters( 'erp_enable_setup_wizard', true ) && current_user_can( 'manage_options' ) ) {
            add_action( 'admin_menu', [ $this, 'admin_menus' ] );
            add_action( 'admin_init', [ $this, 'setup_wizard' ] );
        }
    }

    /**
     * Add admin menus/screens.
     */
    public function admin_menus() {
        add_dashboard_page( '', '', 'manage_options', 'erp-setup', '' );
    }

    /**
     * Show the setup wizard
     */
    public function setup_wizard() {
        if ( empty( $_GET['page'] ) || 'erp-setup' !== $_GET['page'] ) {
            return;
        }

        // Allow resetting onboarding for testing: ?page=erp-setup&reset=1
        if ( isset( $_GET['reset'] ) && $_GET['reset'] === '1' && current_user_can( 'manage_options' ) ) {
            // Perform complete data reset - removes all ERP data including:
            // - Employees
            // - Leave policies and leave types
            // - Financial years
            // - Departments and designations
            // - All other ERP data
            $reset_result = erp_reset_data();
            
            if ( is_wp_error( $reset_result ) ) {
                wp_die( 
                    esc_html__( 'Failed to reset ERP data. Please try again or use Tools > Reset from the main menu.', 'erp' ),
                    esc_html__( 'Reset Failed', 'erp' ),
                    [ 'back_link' => true ]
                );
            }
            
            // Clean up any remaining onboarding-specific options
            delete_option( 'erp_onboarding_completed' );
            delete_option( 'erp_onboarding_completed_at' );
            delete_option( 'erp_enable_leave_management' );
            delete_option( 'erp_working_hours' );
            
            // After successful reset, redirect to setup wizard
            wp_safe_redirect( admin_url( 'index.php?page=erp-setup' ) );
            exit;
        }

        $this->steps = [
            'basic' => [
                'name'    => __( 'Company Profile', 'erp' ),
                'view'    => [ $this, 'setup_step_basic' ],
                'handler' => [ $this, 'setup_step_basic_save' ],
            ],
            'organization' => [
                'name'    => __( 'Teams & Roles', 'erp' ),
                'view'    => [ $this, 'setup_step_organization' ],
                'handler' => [ $this, 'setup_step_organization_save' ],
            ],
            'import_employees' => [
                'name'    => __( 'Add Employees', 'erp' ),
                'view'    => [ $this, 'setup_step_import_employees' ],
                'handler' => [ $this, 'setup_step_import_employees_save' ],
            ],
            'module' => [
                'name'    => __( 'Work Schedule', 'erp' ),
                'view'    => [ $this, 'setup_step_module' ],
                'handler' => [ $this, 'setup_step_module_save' ],
            ],
            'complete' => [
                'name'    => __( 'Ready!', 'erp' ),
                'view'    => [ $this, 'setup_step_ready' ],
                'handler' => '',
            ],
        ];

        $this->step = isset( $_GET['step'] ) ? sanitize_text_field( wp_unslash( $_GET['step'] ) ) : current( array_keys( $this->steps ) );

        // Enqueue React onboarding app
        $onboarding_url = WPERP_URL . '/includes/Admin/Onboarding/assets/dist';

        wp_enqueue_style( 'wperp-onboarding', $onboarding_url . '/onboarding.css', [], WPERP_VERSION );
        wp_enqueue_script( 'wperp-onboarding', $onboarding_url . '/onboarding.js', [], WPERP_VERSION, true );

        // Localize script with necessary data
        $page           = '?page=erp-hr&section=people&sub-section=employee&action=download_sample&type=employee';
        $csv_nonce      = 'erp-import-export-nonce';
        $csv_sample_url = wp_nonce_url( $page, $csv_nonce );

        wp_localize_script( 'wperp-onboarding', 'wpErpOnboarding', [
            'nonce'         => wp_create_nonce( 'wp_rest' ),
            'importNonce'   => wp_create_nonce( 'erp-import-export-nonce' ),
            'apiUrl'        => rest_url( 'erp/v1' ),
            'adminUrl'      => admin_url(),
            'logoUrl'       => file_exists( WPERP_PATH . '/assets/images/wperp-logo.png' )
                               ? WPERP_ASSETS . '/images/wperp-logo.png'
                               : '',
            'sampleCsvUrl'  => admin_url( 'admin.php' . $csv_sample_url ),
            'docsUrl'       => 'https://wperp.com/documentation/',
            'congratulationImageUrl' => $onboarding_url . '/images/congratulation.png',
        ] );

        ob_start();
        $this->setup_wizard_header_react();
        $this->setup_wizard_footer();
        exit;
    }

    public function get_next_step_link() {
        $keys = array_keys( $this->steps );

        return add_query_arg( 'step', $keys[ array_search( $this->step, array_keys( $this->steps ) ) + 1 ], remove_query_arg( 'translation_updated' ) );
    }

    /**
     * Setup Wizard Header
     */
    public function setup_wizard_header() {
        ?>
        <!DOCTYPE html>
        <html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
        <head>
            <meta name="viewport" content="width=device-width" />
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title><?php esc_html_e( 'WP ERP &rsaquo; Setup Wizard', 'erp' ); ?></title>
            <?php
                wp_print_scripts( 'erp-setup' );
                wp_enqueue_emoji_styles();
                do_action( 'admin_print_styles' );
            ?>
        </head>
        <body class="erp-setup wp-core-ui">
            <a href="<?php echo esc_url( admin_url() ); ?>" class="erp-skip-tour">
                <?php esc_html_e( 'Skip Tour', 'erp' ); ?>
            </a>
            <h1 class="erp-logo">
                <?php
                // Check for logo file (PNG or SVG)
                $logo_svg = WPERP_ASSETS . '/images/wperp-logo.svg';
                $logo_png = WPERP_ASSETS . '/images/wperp-logo.png';
                $logo_path_svg = WPERP_PATH . '/assets/images/wperp-logo.svg';
                $logo_path_png = WPERP_PATH . '/assets/images/wperp-logo.png';
                
                if ( file_exists( $logo_path_svg ) ) {
                    echo '<img src="' . esc_url( $logo_svg ) . '" alt="' . esc_attr__( 'WP ERP', 'erp' ) . '" />';
                } elseif ( file_exists( $logo_path_png ) ) {
                    echo '<img src="' . esc_url( $logo_png ) . '" alt="' . esc_attr__( 'WP ERP', 'erp' ) . '" />';
                } else {
                    // Fallback to text logo
                    echo '<span style="color: #3B82F6; font-size: 24px; font-weight: 700;">';
                    echo '<span style="color: #1E40AF;">WP</span> ERP';
                    echo '</span>';
                }
                ?>
            </h1>
        <?php
    }

    /**
     * Setup Wizard Header for React App
     */
    public function setup_wizard_header_react() {
        ?>
        <!DOCTYPE html>
        <html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
        <head>
            <meta name="viewport" content="width=device-width" />
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title><?php esc_html_e( 'WP ERP &rsaquo; Setup Wizard', 'erp' ); ?></title>
            <?php
                wp_print_styles( 'wperp-onboarding' );
                wp_print_scripts( 'wperp-onboarding' );
            ?>
        </head>
        <body class="wperp-setup-root">
            <div id="wperp-onboarding-root"></div>
        <?php
    }

    /**
     * Setup Wizard Footer
     */
    public function setup_wizard_footer() {
        ?>
            <?php if ( 'next_steps' === $this->step ) { ?>
                <a class="erp-return-to-dashboard" href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Return to the WordPress Dashboard', 'erp' ); ?></a>
            <?php } ?>
            </body>
        </html>
        <?php
    }

    /**
     * Output the steps (progress bars)
     */
    public function setup_wizard_steps() {
        $all_steps = array_keys( $this->steps );
        $current_step_index = array_search( $this->step, $all_steps );
        
        // Handle case where step is not found
        if ( $current_step_index === false ) {
            return;
        }
        
        $step_number = $current_step_index + 1; // Display as 1-based
        $total_steps = count( $all_steps ); // Total steps count (dynamically calculated)
        
        // Don't show progress on introduction or final step
        if ( $this->step === 'introduction' || $this->step === 'next_steps' ) {
            return;
        }
        
        // Ensure we have valid step data
        if ( $step_number < 1 || $step_number > $total_steps ) {
            return;
        }
        
        ?>
        <div class="erp-progress-container">
            <div class="erp-progress-wrapper">
                <div class="erp-progress-bars">
                    <?php for ( $i = 1; $i <= $total_steps; $i++ ) : ?>
                        <div class="erp-progress-bar <?php echo $i <= $step_number ? 'active' : ''; ?>"></div>
                    <?php endfor; ?>
                </div>
                <div class="erp-progress-counter">
                    <?php echo esc_html( $step_number . '/' . $total_steps ); ?>
                </div>
            </div>
            <?php if ( isset( $this->steps[ $this->step ]['name'] ) ) : ?>
                <div class="erp-step-label">
                    <?php echo esc_html( $step_number . '. ' . $this->steps[ $this->step ]['name'] ); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Output the content for the current step
     */
    public function setup_wizard_content() {
        echo '<div class="erp-setup-content">';
        call_user_func( $this->steps[ $this->step ]['view'] );
        echo '</div>';
    }

    /**
     * Renders next step buttons
     *
     * @return void
     */
    public function next_step_buttons() {
        ?>
        <div class="erp-button-container">
            <button type="submit" class="erp-btn-continue" name="save_step" value="1">
                <?php esc_html_e( 'Continue', 'erp' ); ?>
            </button>
            <?php wp_nonce_field( 'erp-setup' ); ?>
        </div>
        <?php
    }

    /**
     * Basic setup steps (Company Details)
     *
     * @return void
     */
    public function setup_step_basic() {
        $general         = get_option( 'erp_settings_general', [] );
        $company         = new \WeDevs\ERP\Company();

        $financial_month = isset( $general['gen_financial_month'] ) ? sanitize_text_field( wp_unslash( $general['gen_financial_month'] ) ) : '1';
        $company_started = isset( $general['gen_com_start'] ) ? sanitize_text_field( wp_unslash( $general['gen_com_start'] ) ) : '';
        ?>
        <h1><?php esc_html_e( 'Company Details', 'erp' ); ?></h1>
        <p class="subtitle"><?php esc_html_e( 'Enter you company name, start date and financial year starts', 'erp' ); ?></p>

        <form method="post" class="erp-setup-form">
            <div class="erp-form-group">
                <label for="company_name"><?php esc_html_e( 'Company Name', 'erp' ); ?></label>
                <input type="text" id="company_name" name="company_name" value="<?php echo esc_attr( $company->name ); ?>" required />
            </div>

            <div class="erp-form-row">
                <div class="erp-form-group">
                    <label for="gen_com_start"><?php esc_html_e( 'Company Start Date', 'erp' ); ?></label>
                    <input type="date" id="gen_com_start" name="gen_com_start" value="<?php echo esc_attr( $company_started ); ?>" placeholder="June 01, 2000" />
                </div>

                <div class="erp-form-group">
                    <label for="gen_financial_month"><?php esc_html_e( 'Financial Year Starts', 'erp' ); ?></label>
                    <select id="gen_financial_month" name="gen_financial_month">
                        <?php
                        $months = erp_months_dropdown();
                        foreach ( $months as $key => $month ) {
                            printf(
                                '<option value="%s"%s>%s</option>',
                                esc_attr( $key ),
                                selected( $financial_month, $key, false ),
                                esc_html( $month )
                            );
                        }
                        ?>
                    </select>
                </div>
            </div>

            <?php $this->next_step_buttons(); ?>
        </form>
        <?php
    }


    /**
     * Saves data from basic step
     *
     * @return void
     */
    public function setup_step_basic_save() {
        check_admin_referer( 'erp-setup' );

        $company_name     = isset( $_POST['company_name'] ) ? sanitize_text_field( wp_unslash( $_POST['company_name'] ) ) : '';
        $financial_month  = isset( $_POST['gen_financial_month'] ) ? sanitize_text_field( wp_unslash( $_POST['gen_financial_month'] ) ) : '1';
        $company_started  = isset( $_POST['gen_com_start'] ) ? sanitize_text_field( wp_unslash( $_POST['gen_com_start'] ) ) : '';
        
        $company = new \WeDevs\ERP\Company();

        // Update company name
        $company->update( [
            'name' => $company_name,
        ] );

        // Get existing settings to preserve other fields
        $existing_settings = get_option( 'erp_settings_general', [] );
        
        // Only update the fields from Step 1
        $updated_settings = array_merge( $existing_settings, [
            'gen_financial_month' => $financial_month,
            'gen_com_start'       => $company_started,
        ] );
        
        update_option( 'erp_settings_general', $updated_settings );

        wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    /**
     * Module setup step
     *
     * @since 1.3.4
     */
    public function setup_step_module() {
        $modules            = wperp()->modules->get_query_modules();
        $all_active_modules = wperp()->modules->get_active_modules();
        
        // Get saved leave management settings
        $saved_leave_year = get_option( 'erp_setup_leave_year', date( 'Y' ) );
        $saved_leave_start_date = get_option( 'erp_setup_leave_start_date', '1' );
        $saved_leave_end_date = get_option( 'erp_setup_leave_end_date', '' );
        $saved_generate_policies = get_option( 'erp_setup_generate_default_policies', '1' );
        
        // Get saved workday settings
        $working_days = erp_company_get_working_days();
        
        // Override with individual saved day settings if they exist
        $days_keys = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        foreach ( $days_keys as $day ) {
            $saved_day = get_option( $day, null );
            if ( $saved_day !== null ) {
                $working_days[ $day ] = $saved_day;
            }
        }

        // Add `WP Project Manager` plugin as a module
        $modules['pm'] = [
            'title'       => 'WP Project Manager',
            'slug'        => 'wp-project-manager',
            'description' => __( 'Project, Task Management & Team Collaboration Software', 'erp' ),
        ];

        // Should `WP Project Manager` plugin installs by default?
        $include_pm = get_option( 'include_project_manager' );

        if ( false == $include_pm || 'yes' == $include_pm ) {
            $all_active_modules['pm'] = [
                'slug' => 'wp-project-manager',
            ];
        } ?>

        <h1><?php esc_html_e( 'Leave and Workday Setup', 'erp' ); ?></h1>
        <p class="subtitle"><?php esc_html_e( 'Enter you company name and start date.', 'erp' ); ?></p>

        <form method="post" class="erp-setup-form">
            <div class="erp-card-selection">
                <div class="erp-card" data-target="leave_management">
                    <div class="erp-card-icon">
                    <svg width="30" height="32" viewBox="0 0 30 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18.75 11.1992V4.94922C18.75 2.87815 17.0711 1.19922 15 1.19922L5 1.19922C2.92893 1.19922 1.25 2.87815 1.25 4.94922L1.25 27.4492C1.25 29.5203 2.92893 31.1992 5 31.1992H15C17.0711 31.1992 18.75 29.5203 18.75 27.4492V21.1992M23.75 21.1992L28.75 16.1992M28.75 16.1992L23.75 11.1992M28.75 16.1992L7.5 16.1992" stroke="#0F172A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    </div>
                    <h3><?php esc_html_e( 'Leave Management', 'erp' ); ?></h3>
                    <input type="checkbox" name="modules[]" value="hrm" id="erp_module_hrm_card1" style="display: none;" />
                </div>

                <div class="erp-card" data-target="workday_setup">
                    <div class="erp-card-icon">
                    <svg width="33" height="32" viewBox="0 0 33 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7.75 1.19922V4.94922M25.25 1.19922V4.94922M1.5 27.4492V8.69922C1.5 6.62815 3.17893 4.94922 5.25 4.94922H27.75C29.8211 4.94922 31.5 6.62815 31.5 8.69922V27.4492M1.5 27.4492C1.5 29.5203 3.17893 31.1992 5.25 31.1992H27.75C29.8211 31.1992 31.5 29.5203 31.5 27.4492M1.5 27.4492V14.9492C1.5 12.8782 3.17893 11.1992 5.25 11.1992H27.75C29.8211 11.1992 31.5 12.8782 31.5 14.9492V27.4492M16.5 17.4492H16.5125V17.4617H16.5V17.4492ZM16.5 21.1992H16.5125V21.2117H16.5V21.1992ZM16.5 24.9492H16.5125V24.9617H16.5V24.9492ZM12.75 21.1992H12.7625V21.2117H12.75V21.1992ZM12.75 24.9492H12.7625V24.9617H12.75V24.9492ZM9 21.1992H9.0125V21.2117H9V21.1992ZM9 24.9492H9.0125V24.9617H9V24.9492ZM20.25 17.4492H20.2625V17.4617H20.25V17.4492ZM20.25 21.1992H20.2625V21.2117H20.25V21.1992ZM20.25 24.9492H20.2625V24.9617H20.25V24.9492ZM24 17.4492H24.0125V17.4617H24V17.4492ZM24 21.1992H24.0125V21.2117H24V21.1992Z" stroke="#0F172A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    </div>
                    <h3><?php esc_html_e( 'Workday Setup', 'erp' ); ?></h3>
                    <input type="checkbox" name="modules[]" value="hrm" id="erp_module_hrm_card2" style="display: none;" />
                </div>
            </div>

            <!-- Leave Management Form Section -->
            <div id="leave-management-section" class="erp-section-content" style="display:none;">
                <div class="erp-leave-form">
                    <div class="erp-form-group">
                        <label for="leave_year"><?php esc_html_e( 'Leave Year', 'erp' ); ?></label>
                        <input type="text" id="leave_year" name="leave_year" value="<?php echo esc_attr( $saved_leave_year ); ?>" placeholder="<?php esc_attr_e( 'Enter leave year', 'erp' ); ?>" />
                    </div>

                    <div class="erp-form-row">
                        <div class="erp-form-group">
                            <label for="leave_start_date"><?php esc_html_e( 'Start Date', 'erp' ); ?></label>
                            <select id="leave_start_date" name="leave_start_date">
                                <?php
                                $months = erp_months_dropdown();
                                foreach ( $months as $key => $month ) {
                                    printf(
                                        '<option value="%s"%s>%s</option>',
                                        esc_attr( $key ),
                                        selected( $key, $saved_leave_start_date, false ),
                                        esc_html( $month )
                                    );
                                }
                                ?>
                            </select>
                        </div>

                        <div class="erp-form-group">
                            <label for="leave_end_date"><?php esc_html_e( 'End Date', 'erp' ); ?></label>
                            <input type="date" id="leave_end_date" name="leave_end_date" value="<?php echo esc_attr( $saved_leave_end_date ); ?>" placeholder="dd/mm/yy" />
                        </div>
                    </div>

                    <div class="erp-add-new-section">
                        <button type="button" class="erp-btn-add-new" id="add-new-leave-period">
                            <svg width="13" height="14" viewBox="0 0 13 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.5 0.199219C6.99508 0.199219 7.4 0.604138 7.4 1.09922V5.69922H11.0001C11.4951 5.69922 11.9001 6.10414 11.9001 6.59922C11.9001 7.0943 11.4951 7.49922 11.0001 7.49922H7.4V12.0992C7.4 12.5943 6.99508 12.9992 6.5 12.9992C6.00492 12.9992 5.6 12.5943 5.6 12.0992V7.49922H2C1.50493 7.49922 1.1 7.0943 1.1 6.59922C1.1 6.10414 1.50493 5.69922 2 5.69922H5.6V1.09922C5.6 0.604138 6.00492 0.199219 6.5 0.199219Z" fill="white"/>
                            </svg>
                            <?php esc_html_e( 'Add New', 'erp' ); ?>
                        </button>
                    </div>

                    <div class="erp-toggle-section">
                        <div class="erp-toggle-container">
                            <div>
                                <input type="checkbox" name="generate_default_policies" id="generate_default_policies" class="switch-input" <?php checked( $saved_generate_policies, '1' ); ?>>
                                <label for="generate_default_policies" class="switch-label">
                                    <div class="switch-track">
                                        <div class="switch-handle"></div>
                                    </div>
                                </label>
                            </div>
                            <div class="toggle-description">
                                <?php esc_html_e( 'Generate pre-default leave policies for the current year (WPERP will automatically assign predefined leaves like Sick Leave/Casual leave to the current year)', 'erp' ); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Workday Setup Form Section -->
            <div id="workday-setup-section" class="erp-section-content" style="display:none;">
                <div class="erp-workday-form">
                    <?php
                    $days = [
                        'mon' => __( 'Monday', 'erp' ),
                        'tue' => __( 'Tuesday', 'erp' ),
                        'wed' => __( 'Wednesday', 'erp' ),
                        'thu' => __( 'Thursday', 'erp' ),
                        'fri' => __( 'Friday', 'erp' ),
                        'sat' => __( 'Saturday', 'erp' ),
                        'sun' => __( 'Sunday', 'erp' ),
                    ];
                    
                    foreach ( $days as $key => $day ) {
                        $current_value = isset( $working_days[ $key ] ) ? $working_days[ $key ] : '8';
                        ?>
                        <div class="erp-workday-row">
                            <div class="erp-day-label">
                                <?php echo esc_html( $day ); ?>
                            </div>
                            <div class="erp-day-options">
                                <label class="erp-day-option">
                                    <input type="radio" name="workday[<?php echo esc_attr( $key ); ?>]" value="8" class="workday-radio" <?php checked( $current_value, '8' ); ?>>
                                    <span class="erp-option-btn <?php echo $current_value == '8' ? 'selected' : ''; ?>">
                                        <?php esc_html_e( 'Full Day', 'erp' ); ?>
                                    </span>
                                </label>
                                <label class="erp-day-option">
                                    <input type="radio" name="workday[<?php echo esc_attr( $key ); ?>]" value="4" class="workday-radio" <?php checked( $current_value, '4' ); ?>>
                                    <span class="erp-option-btn <?php echo $current_value == '4' ? 'selected' : ''; ?>">
                                        <?php esc_html_e( 'Half Day', 'erp' ); ?>
                                    </span>
                                </label>
                                <label class="erp-day-option">
                                    <input type="radio" name="workday[<?php echo esc_attr( $key ); ?>]" value="0" class="workday-radio" <?php checked( $current_value, '0' ); ?>>
                                    <span class="erp-option-btn <?php echo $current_value == '0' ? 'selected' : ''; ?>">
                                        <?php esc_html_e( 'Non-working Day', 'erp' ); ?>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>

            <!-- Hidden form fields for all modules to maintain proper functionality -->
            <?php
            foreach ( $modules as $slug => $module ) {
                $checked = array_key_exists( $slug, $all_active_modules );
                $input_id = 'erp_module_' . esc_attr( $slug );
                if ( $slug === 'hrm' ) {
                    // Only add one HRM checkbox for the form submission
                    ?>
                    <input type="checkbox" name="modules[]" value="<?php echo esc_attr( $slug ); ?>" id="erp_module_hrm_main" style="display: none;" />
                    <?php
                } else {
                    ?>
                    <input type="checkbox" name="modules[]" value="<?php echo esc_attr( $slug ); ?>" id="erp_module_<?php echo esc_attr( $slug ); ?>" style="display: none;" <?php checked( $checked ); ?> />
                    <?php
                }
            }
            ?>

            <script type="text/javascript">
                jQuery(function($) {
                    // Card selection handling
                    $('.erp-card').on('click', function() {
                        var $clickedCard = $(this);
                        var $allCards = $('.erp-card');
                        var $mainHrmCheckbox = $('#erp_module_hrm_main');
                        var target = $clickedCard.data('target');
                        var isCurrentlySelected = $clickedCard.hasClass('selected');
                        
                        // Hide all form sections first
                        $('.erp-section-content').hide();
                        
                        if (isCurrentlySelected) {
                            // If clicking the already selected card, deselect it
                            $clickedCard.removeClass('selected');
                            $clickedCard.find('input[type="checkbox"]').prop('checked', false);
                            $mainHrmCheckbox.prop('checked', false);
                        } else {
                            // Deselect all cards first (this ensures only one card is selected at a time)
                            $allCards.removeClass('selected');
                            $allCards.find('input[type="checkbox"]').prop('checked', false);
                            
                            // Select the clicked card and show its form
                            $clickedCard.addClass('selected');
                            $clickedCard.find('input[type="checkbox"]').prop('checked', true);
                            $mainHrmCheckbox.prop('checked', true);
                            
                            // Show the corresponding form section
                            if (target === 'leave_management') {
                                $('#leave-management-section').show();
                            } else if (target === 'workday_setup') {
                                $('#workday-setup-section').show();
                            }
                        }
                    });

                    // Initialize form visibility based on current state
                    var $leaveCard = $('.erp-card[data-target="leave_management"]');
                    var $workdayCard = $('.erp-card[data-target="workday_setup"]');
                    if ($leaveCard.hasClass('selected')) {
                        $('#leave-management-section').show();
                    }
                    if ($workdayCard.hasClass('selected')) {
                        $('#workday-setup-section').show();
                    }

                    // Handle workday radio button clicks
                    $('.workday-radio').on('change', function() {
                        var $row = $(this).closest('.erp-workday-row');
                        var $allButtons = $row.find('.erp-option-btn');
                        var $selectedButton = $(this).siblings('.erp-option-btn');
                        
                        // Remove selected class from all buttons in this row
                        $allButtons.removeClass('selected').css({
                            'background': '#FFFFFF',
                            'color': '#000000'
                        });
                        
                        // Add selected class to clicked button
                        $selectedButton.addClass('selected').css({
                            'background': '#3B82F6',
                            'color': '#FFFFFF'
                        });
                    });

                    // Handle label clicks for workday options
                    $('.erp-day-option').on('click', function(e) {
                        e.preventDefault();
                        var $radio = $(this).find('.workday-radio');
                        $radio.prop('checked', true).trigger('change');
                    });

                    // Handle toggle switch animation
                    $('#generate_default_policies').on('change', function() {
                        var isChecked = $(this).is(':checked');
                        var $switchTrack = $(this).next('.switch-label').find('.switch-track');
                        var $switchHandle = $(this).next('.switch-label').find('.switch-handle');
                        
                        if (isChecked) {
                            $switchHandle.css({
                                'left': '16px',
                                'top': '-2px',
                                'width': '20px',
                                'height': '20px',
                                'background': '#FFFFFF',
                                'border': '1px solid #CBD5E1',
                                'z-index': '10',
                                'position': 'absolute'
                            });
                            $switchTrack.css({
                                'background': '#3B82F6',
                                'width': '36px',
                                'height': '16px',
                                'border-radius': '8px'
                            });
                        } else {
                            $switchHandle.css({
                                'left': '-2px',
                                'top': '-2px',
                                'width': '20px',
                                'height': '20px',
                                'background': '#FFFFFF',
                                'border': '1px solid #CBD5E1',
                                'z-index': '10',
                                'position': 'absolute'
                            });
                            $switchTrack.css({
                                'background': '#CBD5E1',
                                'width': '36px',
                                'height': '16px',
                                'border-radius': '8px'
                            });
                        }
                    });
                });
            </script>

            <?php $this->next_step_buttons(); ?>
        </form>
        <?php
    }

    /**
     * Module setup step save
     *
     * @since 1.3.4
     *
     * Add project manager plugin
     * @since 1.4.2
     */
    public function setup_step_module_save() {
        check_admin_referer( 'erp-setup' );

        $all_modules   = wperp()->modules->get_modules();
        $modules       = isset( $_POST['modules'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['modules'] ) ) : [];
        $pm_module_add = 'no';

        // if `WP Project Manager` plugin needs to be installed
        if ( in_array( 'pm', $modules ) ) {
            $pm_plugin_id = 'wedevs-project-manager';
            $pm_plugin    = [
                'name'      => __( 'WP Project Manager', 'erp' ),
                'repo-slug' => 'wedevs-project-manager',
                'file'      => 'cpm.php',
            ];
            $pm_module_add = 'yes';

            $this->background_installer( $pm_plugin_id, $pm_plugin );
        }

        update_option( 'include_project_manager', $pm_module_add );

        foreach ( $all_modules as $key => $module ) {
            if ( ! in_array( $key, $modules ) ) {
                unset( $all_modules[$key] );
            }
        }

        update_option( 'erp_modules', $all_modules );

        // when CRM is inactive hide related steps
        if ( ! in_array( 'crm', $modules ) ) {
            unset( $this->steps['email'] );
        }

        // Handle leave management form data if HRM is active
        if ( in_array( 'hrm', $modules ) ) {
            // Handle leave years array (React onboarding format)
            if ( isset( $_POST['leaveYears'] ) && is_array( $_POST['leaveYears'] ) ) {
                $leave_years = [];
                foreach ( $_POST['leaveYears'] as $year_data ) {
                    if ( ! empty( $year_data['fy_name'] ) && ! empty( $year_data['start_date'] ) && ! empty( $year_data['end_date'] ) ) {
                        $leave_years[] = [
                            'fy_name'     => sanitize_text_field( wp_unslash( $year_data['fy_name'] ) ),
                            'start_date'  => sanitize_text_field( wp_unslash( $year_data['start_date'] ) ),
                            'end_date'    => sanitize_text_field( wp_unslash( $year_data['end_date'] ) ),
                            'description' => 'Year for leave',
                        ];
                    }
                }

                // Save leave years using the same function as Vue settings
                if ( ! empty( $leave_years ) ) {
                    $result = erp_settings_save_leave_years( $leave_years );
                    if ( is_wp_error( $result ) ) {
                        // Log error but continue setup process
                        error_log( 'ERP Setup: Failed to save leave years - ' . $result->get_error_message() );
                    }
                }
            }

            // Handle legacy single leave year format (for backward compatibility)
            $leave_year = isset( $_POST['leave_year'] ) ? sanitize_text_field( wp_unslash( $_POST['leave_year'] ) ) : '';
            $leave_start_date = isset( $_POST['leave_start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['leave_start_date'] ) ) : '';
            $leave_end_date = isset( $_POST['leave_end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['leave_end_date'] ) ) : '';

            if ( ! empty( $leave_year ) && ! empty( $leave_start_date ) && ! empty( $leave_end_date ) ) {
                update_option( 'erp_setup_leave_year', $leave_year );
                update_option( 'erp_setup_leave_start_date', $leave_start_date );
                update_option( 'erp_setup_leave_end_date', $leave_end_date );
            }

            // Handle enable leave management toggle
            $enable_leave_management = isset( $_POST['enableLeaveManagement'] ) ? '1' : '0';
            update_option( 'erp_setup_generate_default_policies', $enable_leave_management );

            // Handle workday settings (React onboarding format with short keys: mon, tue, etc.)
            if ( isset( $_POST['workingDays'] ) && is_array( $_POST['workingDays'] ) ) {
                $valid_days = [ 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun' ];

                foreach ( $_POST['workingDays'] as $day => $value ) {
                    $day = sanitize_text_field( wp_unslash( $day ) );
                    $value = sanitize_text_field( wp_unslash( $value ) );

                    // Save directly if it's already using short keys (mon, tue, etc.)
                    if ( in_array( $day, $valid_days ) ) {
                        update_option( $day, $value );
                    }
                }
            }
            // Legacy workday format (for backward compatibility)
            elseif ( isset( $_POST['workday'] ) && is_array( $_POST['workday'] ) && count( $_POST['workday'] ) === 7 ) {
                $workday_settings = [];
                foreach ( $_POST['workday'] as $day => $value ) {
                    $day = sanitize_text_field( wp_unslash( $day ) );
                    $value = sanitize_text_field( wp_unslash( $value ) );
                    $workday_settings[ $day ] = $value;

                    // Save each day to individual options (matching the original setup wizard behavior)
                    update_option( $day, $value );
                }
            }
        }

        // when HRM is inactive hide related steps
        if ( ! in_array( 'hrm', $modules ) ) {
            unset( $this->steps['department'] );
            unset( $this->steps['designation'] );
            unset( $this->steps['workdays'] );
        }

        wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    /**
     * Organization setup step (Departments and Designations)
     *
     * @return void
     */
    public function setup_step_organization() { 
        ?>
        <h1><?php esc_html_e( 'Make Your Department and Designation', 'erp' ); ?></h1>
        <p class="subtitle"><?php esc_html_e( 'Enter you company name and start date.', 'erp' ); ?></p>

        <form method="post" class="erp-setup-form">
            <div class="erp-card-selection">
                <div class="erp-card" data-target="departments">
                    <div class="erp-card-icon">
                        <svg width="35" height="31" viewBox="0 0 35 31" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M27.5006 26.3978C27.9131 26.4319 28.3304 26.4492 28.7517 26.4492C30.4989 26.4492 32.1764 26.1505 33.7358 25.6013C33.7463 25.4687 33.7517 25.3346 33.7517 25.1992C33.7517 22.4378 31.5131 20.1992 28.7517 20.1992C27.7057 20.1992 26.7346 20.5204 25.9319 21.0696M27.5006 26.3978C27.5007 26.4149 27.5007 26.4321 27.5007 26.4492C27.5007 26.8242 27.4801 27.1944 27.4399 27.5586C24.5119 29.2386 21.1185 30.1992 17.5007 30.1992C13.8829 30.1992 10.4895 29.2386 7.56158 27.5586C7.52137 27.1944 7.50073 26.8242 7.50073 26.4492C7.50073 26.4321 7.50078 26.415 7.50086 26.398M27.5006 26.3978C27.4908 24.4369 26.9165 22.6094 25.9319 21.0696M25.9319 21.0696C24.1554 18.2912 21.0432 16.4492 17.5007 16.4492C13.9587 16.4492 10.8468 18.2907 9.07022 21.0686M9.07022 21.0686C8.26778 20.52 7.29733 20.1992 6.25195 20.1992C3.49053 20.1992 1.25195 22.4378 1.25195 25.1992C1.25195 25.3346 1.25733 25.4687 1.26789 25.6013C2.82728 26.1505 4.50473 26.4492 6.25195 26.4492C6.67252 26.4492 7.08905 26.4319 7.50086 26.398M9.07022 21.0686C8.08524 22.6087 7.5107 24.4365 7.50086 26.398M22.5007 6.44922C22.5007 9.21064 20.2622 11.4492 17.5007 11.4492C14.7393 11.4492 12.5007 9.21064 12.5007 6.44922C12.5007 3.68779 14.7393 1.44922 17.5007 1.44922C20.2622 1.44922 22.5007 3.68779 22.5007 6.44922ZM32.5007 11.4492C32.5007 13.5203 30.8218 15.1992 28.7507 15.1992C26.6797 15.1992 25.0007 13.5203 25.0007 11.4492C25.0007 9.37815 26.6797 7.69922 28.7507 7.69922C30.8218 7.69922 32.5007 9.37815 32.5007 11.4492ZM10.0007 11.4492C10.0007 13.5203 8.3218 15.1992 6.25073 15.1992C4.17966 15.1992 2.50073 13.5203 2.50073 11.4492C2.50073 9.37815 4.17966 7.69922 6.25073 7.69922C8.3218 7.69922 10.0007 9.37815 10.0007 11.4492Z" stroke="#0F172A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3><?php esc_html_e( 'Departments', 'erp' ); ?></h3>
                    <input type="checkbox" name="setup_departments" value="1" id="setup_departments" />
                </div>

                <div class="erp-card" data-target="designations">
                    <div class="erp-card-icon">
                        <svg width="33" height="32" viewBox="0 0 33 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M30.25 19.7824V26.8658C30.25 28.6899 28.9385 30.2591 27.1303 30.4991C23.6524 30.9609 20.104 31.1992 16.5 31.1992C12.896 31.1992 9.34756 30.9609 5.86974 30.4991C4.06149 30.2591 2.75 28.6899 2.75 26.8658V19.7824M30.25 19.7824C31.0365 19.1119 31.5 18.0973 31.5 17.014V10.7087C31.5 8.90713 30.2202 7.35058 28.4384 7.08398C26.5628 6.80334 24.6658 6.5878 22.75 6.43976M30.25 19.7824C29.9273 20.0575 29.5503 20.2746 29.1285 20.4149C25.1589 21.7346 20.9129 22.4492 16.5 22.4492C12.0871 22.4492 7.84116 21.7346 3.87148 20.4148C3.44974 20.2746 3.07268 20.0575 2.75 19.7824M2.75 19.7824C1.96346 19.1119 1.5 18.0973 1.5 17.014V10.7087C1.5 8.90714 2.77984 7.35058 4.56157 7.08399C6.43722 6.80334 8.33415 6.58781 10.25 6.43976M22.75 6.43976V4.94922C22.75 2.87815 21.0711 1.19922 19 1.19922H14C11.9289 1.19922 10.25 2.87815 10.25 4.94922V6.43976M22.75 6.43976C20.6876 6.28039 18.6033 6.19922 16.5 6.19922C14.3967 6.19922 12.3124 6.28039 10.25 6.43976M16.5 17.4492H16.5125V17.4617H16.5V17.4492Z" stroke="#0F172A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3><?php esc_html_e( 'Designations', 'erp' ); ?></h3>
                    <input type="checkbox" name="setup_designations" value="1" id="setup_designations" />
                </div>
            </div>

            <div id="departments-section" class="erp-section-content" style="display:none;">
                <div class="erp-multiselect-container">
                    <input type="text" class="erp-multiselect-input" data-type="departments" placeholder="<?php esc_attr_e( 'Type and press Enter to add department...', 'erp' ); ?>" />
                    <div class="erp-tags-list" data-type="departments"></div>
                    <div class="erp-suggestions-list" data-type="departments">
                        <div class="erp-suggestion-item" data-value="HR and Admin"><?php esc_html_e( 'HR and Admin', 'erp' ); ?></div>
                        <div class="erp-suggestion-item" data-value="Engineering"><?php esc_html_e( 'Engineering', 'erp' ); ?></div>
                        <div class="erp-suggestion-item" data-value="Sales"><?php esc_html_e( 'Sales', 'erp' ); ?></div>
                        <div class="erp-suggestion-item" data-value="Content Marketing"><?php esc_html_e( 'Content Marketing', 'erp' ); ?></div>
                        <div class="erp-suggestion-item" data-value="Design"><?php esc_html_e( 'Design', 'erp' ); ?></div>
                        <div class="erp-suggestion-item" data-value="Digital Marketing"><?php esc_html_e( 'Digital Marketing', 'erp' ); ?></div>
                    </div>
                </div>
            </div>

            <div id="designations-section" class="erp-section-content" style="display:none;">
                <div class="erp-multiselect-container">
                    <input type="text" class="erp-multiselect-input" data-type="designations" placeholder="<?php esc_attr_e( 'Type and press Enter to add designation...', 'erp' ); ?>" />
                    <div class="erp-tags-list" data-type="designations"></div>
                    <div class="erp-suggestions-list" data-type="designations">
                        <div class="erp-suggestion-item" data-value="Manager"><?php esc_html_e( 'Manager', 'erp' ); ?></div>
                        <div class="erp-suggestion-item" data-value="Senior Developer"><?php esc_html_e( 'Senior Developer', 'erp' ); ?></div>
                        <div class="erp-suggestion-item" data-value="Developer"><?php esc_html_e( 'Developer', 'erp' ); ?></div>
                        <div class="erp-suggestion-item" data-value="Designer"><?php esc_html_e( 'Designer', 'erp' ); ?></div>
                        <div class="erp-suggestion-item" data-value="Team Lead"><?php esc_html_e( 'Team Lead', 'erp' ); ?></div>
                        <div class="erp-suggestion-item" data-value="Consultant"><?php esc_html_e( 'Consultant', 'erp' ); ?></div>
                    </div>
                </div>
            </div>

            <script type="text/javascript">
                jQuery(function($) {
                    var selectedItems = {
                        departments: [],
                        designations: []
                    };

                    // Card selection toggle (only one at a time)
                    $('.erp-card').on('click', function() {
                        var $card = $(this);
                        var $checkbox = $card.find('input[type="checkbox"]');
                        var target = $card.data('target');
                        var isCurrentlySelected = $card.hasClass('selected');
                        
                        // Deselect all cards first
                        $('.erp-card').removeClass('selected');
                        $('.erp-card input[type="checkbox"]').prop('checked', false);
                        $('.erp-section-content').hide();
                        
                        // If this card wasn't selected, select it
                        if (!isCurrentlySelected) {
                            $card.addClass('selected');
                            $checkbox.prop('checked', true);
                            $('#' + target + '-section').show();
                        }
                    });

                    // Multi-select input handling
                    $('.erp-multiselect-input').on('keydown', function(e) {
                        if (e.key === 'Enter' || e.keyCode === 13) {
                            e.preventDefault();
                            var value = $(this).val().trim();
                            var type = $(this).data('type');
                            
                            if (value && selectedItems[type].indexOf(value) === -1) {
                                addTag(type, value);
                                $(this).val('');
                                $('.erp-suggestions-list[data-type="' + type + '"]').hide();
                            }
                        }
                    });

                    // Show suggestions on focus
                    $('.erp-multiselect-input').on('focus', function() {
                        var type = $(this).data('type');
                        $('.erp-suggestions-list[data-type="' + type + '"]').show();
                    });

                    // Hide suggestions on blur (with delay for click)
                    $('.erp-multiselect-input').on('blur', function() {
                        var type = $(this).data('type');
                        setTimeout(function() {
                            $('.erp-suggestions-list[data-type="' + type + '"]').hide();
                        }, 200);
                    });

                    // Filter suggestions on input
                    $('.erp-multiselect-input').on('input', function() {
                        var value = $(this).val().toLowerCase();
                        var type = $(this).data('type');
                        var $suggestions = $('.erp-suggestions-list[data-type="' + type + '"]');
                        
                        if (value) {
                            $suggestions.find('.erp-suggestion-item').each(function() {
                                var itemText = $(this).text().toLowerCase();
                                $(this).toggle(itemText.indexOf(value) !== -1);
                            });
                            $suggestions.show();
                        } else {
                            $suggestions.find('.erp-suggestion-item').show();
                        }
                    });

                    // Click on suggestion
                    $('.erp-suggestion-item').on('click', function() {
                        var value = $(this).data('value');
                        var type = $(this).closest('.erp-suggestions-list').data('type');
                        
                        if (selectedItems[type].indexOf(value) === -1) {
                            addTag(type, value);
                            $('.erp-multiselect-input[data-type="' + type + '"]').val('');
                        }
                        
                        $(this).closest('.erp-suggestions-list').hide();
                    });

                    // Add tag function
                    function addTag(type, value) {
                        selectedItems[type].push(value);
                        
                        var $tag = $('<div class="erp-tag">' +
                            '<span class="erp-tag-text">' + value + '</span>' +
                            '<button type="button" class="erp-tag-remove" data-value="' + value + '">' +
                            '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                            '<path d="M10.5 3.5L3.5 10.5M3.5 3.5L10.5 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>' +
                            '</svg>' +
                            '</button>' +
                            '<input type="hidden" name="' + type + '[]" value="' + value + '">' +
                            '</div>');
                        
                        $('.erp-tags-list[data-type="' + type + '"]').append($tag);
                        
                        // Hide the suggestion item
                        $('.erp-suggestions-list[data-type="' + type + '"] .erp-suggestion-item[data-value="' + value + '"]').hide();
                    }

                    // Remove tag on click
                    $(document).on('click', '.erp-tag-remove', function() {
                        var $tag = $(this).closest('.erp-tag');
                        var value = $(this).data('value');
                        var type = $tag.closest('.erp-tags-list').data('type');
                        
                        // Remove from array
                        var index = selectedItems[type].indexOf(value);
                        if (index > -1) {
                            selectedItems[type].splice(index, 1);
                        }
                        
                        // Show the suggestion item again
                        $('.erp-suggestions-list[data-type="' + type + '"] .erp-suggestion-item[data-value="' + value + '"]').show();
                        
                        // Remove tag
                        $tag.remove();
                    });
                });
            </script>

            <?php $this->next_step_buttons(); ?>
        </form>
        <?php
    }

    /**
     * Save organization step data
     *
     * @return void
     */
    public function setup_step_organization_save() {
        check_admin_referer( 'erp-setup' );

        // Save departments if setup was selected
        if ( isset( $_POST['setup_departments'] ) && $_POST['setup_departments'] === '1' ) {
            $departments = isset( $_POST['departments'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['departments'] ) ) : [];
            if ( $departments ) {
                foreach ( $departments as $department ) {
                    if ( ! empty( $department ) ) {
                        erp_hr_create_department( [ 'title' => $department ] );
                    }
                }
            }
        }

        // Save designations if setup was selected
        if ( isset( $_POST['setup_designations'] ) && $_POST['setup_designations'] === '1' ) {
            $designations = isset( $_POST['designations'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['designations'] ) ) : [];
            if ( $designations ) {
                foreach ( $designations as $designation ) {
                    if ( ! empty( $designation ) ) {
                        erp_hr_create_designation( [ 'title' => $designation ] );
                    }
                }
            }
        }

        wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    /**
     * Import Employees setup step
     *
     * @return void
     */
    public function setup_step_import_employees() {
        ?>
        <h1><?php esc_html_e( 'Import Employees', 'erp' ); ?></h1>
        <p class="subtitle"><?php esc_html_e( 'Select one of the available options to populate your employee list', 'erp' ); ?></p>

        <form method="post" class="erp-setup-form" enctype="multipart/form-data">
            <div class="erp-csv-upload-wrapper">
                <div class="erp-csv-upload-box">
                    <input type="file" id="employee_csv" name="employee_csv" accept=".csv" style="display: none;" />
                    <label for="employee_csv" class="erp-csv-upload-label">
                        <div class="erp-upload-icon">
                            <svg width="54" height="42" viewBox="0 0 54 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M27 33.1992L27 15.1992M27 15.1992L35 23.1992M27 15.1992L19 23.1992M13 41.1992C6.37258 41.1992 1 35.8266 1 29.1992C1 23.8847 4.4548 19.3771 9.24107 17.7997C9.08279 16.9571 9 16.0878 9 15.1992C9 7.46723 15.268 1.19922 23 1.19922C29.4833 1.19922 34.9373 5.60616 36.5298 11.5879C37.3078 11.3356 38.138 11.1992 39 11.1992C43.4183 11.1992 47 14.7809 47 19.1992C47 20.1276 46.8419 21.019 46.5511 21.8481C50.3209 23.2804 53 26.927 53 31.1992C53 36.7221 48.5228 41.1992 43 41.1992H13Z" stroke="#0F172A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>

                        </div>
                        <h4><?php esc_html_e( 'Upload a CSV file', 'erp' ); ?></h4>
                        <p class="erp-csv-drag-text"><?php esc_html_e( 'Drag and Droop CSV file here or', 'erp' ); ?></p>
                        <button type="button" class="erp-choose-file-btn">
                            <?php esc_html_e( 'Choose File', 'erp' ); ?>
                            <svg width="13" height="14" viewBox="0 0 13 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M0.900391 12.7988C0.900391 12.357 1.25856 11.9988 1.70039 11.9988H11.3004C11.7422 11.9988 12.1004 12.357 12.1004 12.7988C12.1004 13.2407 11.7422 13.5988 11.3004 13.5988H1.70039C1.25856 13.5988 0.900391 13.2407 0.900391 12.7988ZM3.53471 4.56451C3.22229 4.25209 3.22229 3.74556 3.53471 3.43314L5.93471 1.03314C6.08473 0.883114 6.28822 0.798828 6.50039 0.798828C6.71256 0.798828 6.91605 0.883114 7.06608 1.03314L9.46608 3.43314C9.7785 3.74556 9.7785 4.25209 9.46608 4.56451C9.15366 4.87693 8.64713 4.87693 8.33471 4.56451L7.30039 3.5302V9.59883C7.30039 10.0407 6.94222 10.3988 6.50039 10.3988C6.05856 10.3988 5.70039 10.0407 5.70039 9.59883L5.70039 3.5302L4.66608 4.56451C4.35366 4.87693 3.84713 4.87693 3.53471 4.56451Z" fill="#6366F1"/>
                            </svg>
                        </button>
                        <p class="erp-csv-filename" style="display: none;"></p>
                    </label>
                </div>
                <p class="erp-csv-help-text">
                    <?php esc_html_e( 'Download sample CSV file to see the required format', 'erp' ); ?>
                    <a href="<?php echo esc_url( WPERP_ASSETS . '/sample/wperp_employee_list.csv' ); ?>" class="erp-download-link"><?php esc_html_e( 'Download Sample', 'erp' ); ?></a>
                </p>
            </div>

            <!-- Column Mapping Modal -->
            <div id="erp-column-mapping-modal" class="erp-modal" style="display: none;">
                <div class="erp-modal-content">
                    <div class="erp-modal-header">
                        <h2><?php esc_html_e( 'Map Properties', 'erp' ); ?></h2>
                        <button type="button" class="erp-modal-close">&times;</button>
                    </div>
                    <div class="erp-modal-body">
                        <div class="erp-modal-tabs">
                            <div class="erp-tab active" data-tab="columns">
                                <?php esc_html_e( 'Columns', 'erp' ); ?> 
                                <span class="erp-column-count">(0)</span>
                            </div>
                        </div>
                        <div class="erp-tab-content">
                            <div id="erp-columns-tab" class="erp-tab-pane active">
                                <table class="erp-mapping-table">
                                    <tbody id="erp-mapping-rows">
                                        <!-- Rows will be inserted here via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="erp-modal-footer">
                        <button type="button" class="erp-btn-cancel"><?php esc_html_e( 'Cancel', 'erp' ); ?></button>
                        <button type="button" class="erp-btn-import"><?php esc_html_e( 'Import Employee', 'erp' ); ?></button>
                    </div>
                </div>
            </div>

            <script>
                jQuery(document).ready(function($) {
                    var csvColumns = [];
                    var profileFields = <?php echo json_encode( erp_get_import_export_fields()['employee']['fields'] ); ?>;
                    var requiredFields = <?php echo json_encode( erp_get_import_export_fields()['employee']['required_fields'] ); ?>;

                    // Handle button click to trigger file input
                    $('.erp-choose-file-btn').on('click', function(e) {
                        e.preventDefault();
                        $('#employee_csv').click();
                    });

                    // Handle file selection and show mapping modal
                    $('#employee_csv').on('change', function(e) {
                        var file = e.target.files[0];
                        if (file) {
                            $('.erp-csv-drag-text').hide();
                            $('.erp-choose-file-btn').hide();
                            $('.erp-csv-filename').text(file.name).show();
                            
                            // Read CSV and show mapping modal
                            readCsvColumns(file);
                        }
                    });

                    // Read CSV columns from file
                    function readCsvColumns(file) {
                        var reader = new FileReader();
                        var first5000 = file.slice(0, 5000);
                        
                        reader.readAsText(first5000);
                        reader.onload = function(e) {
                            var csv = reader.result;
                            var lines = csv.split('\n');
                            var columnNamesLine = lines[0];
                            csvColumns = columnNamesLine.split(',').map(function(col) {
                                return col.trim().replace(/"/g, '');
                            });
                            
                            // Update column count
                            $('.erp-column-count').text('(' + csvColumns.length + ')');
                            
                            // Generate mapping rows
                            generateMappingRows();
                            
                            // Show modal
                            $('#erp-column-mapping-modal').fadeIn(200);
                        };
                    }

                    // Generate mapping rows
                    function generateMappingRows() {
                        var html = '';
                        
                        profileFields.forEach(function(field) {
                            var isRequired = requiredFields.indexOf(field) !== -1;
                            var fieldLabel = field.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
                            var requiredSpan = isRequired ? '<span class="required">*</span>' : '';
                            
                            html += '<tr>';
                            html += '<td><label>' + fieldLabel + requiredSpan + '</label></td>';
                            html += '<td>';
                            html += '<select name="fields[' + field + ']" class="erp-field-select"' + (isRequired ? ' required' : '') + '>';
                            html += '<option value="">' + (isRequired ? '<?php esc_html_e( 'Select Field', 'erp' ); ?>' : '&mdash; <?php esc_html_e( 'Select Field', 'erp' ); ?> &mdash;') + '</option>';
                            
                            csvColumns.forEach(function(col, index) {
                                var selected = col.toLowerCase().replace(/[_\s]/g, '') === field.toLowerCase().replace(/[_\s]/g, '') ? ' selected' : '';
                                html += '<option value="' + index + '"' + selected + '>' + col + '</option>';
                            });
                            
                            html += '</select>';
                            html += '</td>';
                            html += '</tr>';
                        });
                        
                        $('#erp-mapping-rows').html(html);
                    }

                    // Modal close handlers
                    $('.erp-modal-close, .erp-btn-cancel').on('click', function() {
                        $('#erp-column-mapping-modal').fadeOut(200);
                    });

                    // Import button handler
                    $('.erp-btn-import').on('click', function() {
                        var $btn = $(this);
                        
                        // Validate required fields
                        var isValid = true;
                        $('.erp-field-select[required]').each(function() {
                            if (!$(this).val()) {
                                isValid = false;
                                $(this).css('border-color', '#ef4444');
                            } else {
                                $(this).css('border-color', '');
                            }
                        });
                        
                        if (!isValid) {
                            return;
                        }
                        
                        // Show loading state
                        $btn.prop('disabled', true).html('<span class="erp-loading-spinner"></span> <?php esc_html_e( 'Importing...', 'erp' ); ?>');
                        
                        // Prepare form data
                        var formData = new FormData();
                        formData.append('action', 'erp_import_csv');
                        formData.append('type', 'employee');
                        formData.append('csv_file', $('#employee_csv')[0].files[0]);
                        formData.append('_wpnonce', '<?php echo wp_create_nonce( 'erp-import-export-nonce' ); ?>');
                        
                        // Add field mappings
                        $('.erp-field-select').each(function() {
                            var fieldName = $(this).attr('name');
                            var fieldValue = $(this).val();
                            if (fieldValue !== '') {
                                formData.append(fieldName, fieldValue);
                            }
                        });
                        
                        // Send AJAX request
                        $.ajax({
                            url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                if (response.success) {
                                    // Show success message
                                    showSuccessMessage(response.data);
                                } else {
                                    // Show error
                                    alert(response.data || '<?php esc_html_e( 'Import failed. Please try again.', 'erp' ); ?>');
                                    $btn.prop('disabled', false).text('<?php esc_html_e( 'Import Employee', 'erp' ); ?>');
                                }
                            },
                            error: function() {
                                alert('<?php esc_html_e( 'Import failed. Please try again.', 'erp' ); ?>');
                                $btn.prop('disabled', false).text('<?php esc_html_e( 'Import Employee', 'erp' ); ?>');
                            }
                        });
                    });
                    
                    // Show success message
                    function showSuccessMessage(message) {
                        // Extract number from message (e.g., "5 items have been imported successfully")
                        var match = message.match(/(\d+)/);
                        var count = match ? match[1] : '0';
                        
                        // Update modal content
                        $('.erp-modal-body').html(`
                            <div class="erp-import-success">
                                <div class="erp-success-icon">
                                <svg width="54" height="55" viewBox="0 0 54 55" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                    <rect y="0.199219" width="54" height="54" fill="url(#pattern0_449_30712)"/>
                                    <defs>
                                    <pattern id="pattern0_449_30712" patternContentUnits="objectBoundingBox" width="1" height="1">
                                    <use xlink:href="#image0_449_30712" transform="scale(0.00444444)"/>
                                    </pattern>
                                    <image id="image0_449_30712" width="225" height="225" preserveAspectRatio="none" xlink:href="data:image/png;base64,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"/>
                                    </defs>
                                </svg>
                                </div>
                                <h3><?php esc_html_e( 'Successfully Imported', 'erp' ); ?></h3>
                                <p>` + count + ` <?php esc_html_e( 'employees has been imported', 'erp' ); ?></p>
                            </div>
                        `);
                        
                        // Update footer button
                        $('.erp-modal-footer').html(`
                            <button type="button" class="erp-btn-continue-setup"><?php esc_html_e( 'Continue', 'erp' ); ?> →</button>
                        `);
                        
                        // Continue button handler
                        $('.erp-btn-continue-setup').on('click', function() {
                            window.location.href = '<?php echo esc_url( $this->get_next_step_link() ); ?>';
                        });
                    }

                    // Handle drag and drop
                    var $label = $('.erp-csv-upload-label');
                    
                    $label.on('dragover', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        $(this).addClass('drag-over');
                    });
                    
                    $label.on('dragleave', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        $(this).removeClass('drag-over');
                    });
                    
                    $label.on('drop', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        $(this).removeClass('drag-over');
                        
                        var files = e.originalEvent.dataTransfer.files;
                        if (files.length > 0 && files[0].name.endsWith('.csv')) {
                            $('#employee_csv')[0].files = files;
                            var file = files[0];
                            $('.erp-csv-drag-text').hide();
                            $('.erp-choose-file-btn').hide();
                            $('.erp-csv-filename').text(file.name).show();
                            readCsvColumns(file);
                        }
                    });
                });
            </script>

            <?php $this->next_step_buttons(); ?>
        </form>
        <?php
    }

    /**
     * Save Import Employees step data
     *
     * @return void
     */
    public function setup_step_import_employees_save() {
        check_admin_referer( 'erp-setup' );

        // Handle CSV upload if file was provided
        if ( isset( $_FILES['employee_csv'] ) && ! empty( $_FILES['employee_csv']['name'] ) ) {
            // TODO: Implement CSV import logic
            // For now, just store that a file was uploaded
            update_option( 'erp_setup_csv_uploaded', 'yes' );
        } else {
            // No file uploaded - skip import
            update_option( 'erp_setup_csv_uploaded', 'no' );
        }

        wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }








    /**
     * Final setup step meaning ready to go
     *
     * @return void
     */
    public function setup_step_ready() {
        ?>
        <h1><?php esc_html_e( 'Your Site is Ready', 'erp' ); ?></h1>
        <p class="subtitle"><?php esc_html_e( 'Enter you company name and start date.', 'erp' ); ?></p>

        <!-- Congratulations Section -->
        <div class="erp-congratulations-card" style="background: white; border-radius: 16px; padding: 95px 65px; text-align: center; margin: 32px auto; max-width: 640px; max-height: 340px; border: 1px solid #CBD5E1">
            <!-- Confetti Icon -->
            <div class="confetti-icon" style="margin-bottom: 24px;">
                <svg width="54" height="55" viewBox="0 0 54 55" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <rect y="0.199219" width="54" height="54" fill="url(#pattern0_449_30761)"/>
                    <defs>
                    <pattern id="pattern0_449_30761" patternContentUnits="objectBoundingBox" width="1" height="1">
                    <use xlink:href="#image0_449_30761" transform="scale(0.00444444)"/>
                    </pattern>
                    <image id="image0_449_30761" width="225" height="225" preserveAspectRatio="none" xlink:href="data:image/png;base64,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"/>
                    </defs>
                </svg>
            </div>
            
            <h2 style="font-size: 20px; font-weight: 500; color: #000000; margin: 0 0 4px 0;"><?php esc_html_e( 'Congratulations', 'erp' ); ?></h2>
            <p style="color: #64748B; font-size: 14px; line-height: 20px; margin: 0; font-weight: 400;">
                <?php esc_html_e( 'Create some designations for your company. e.g. Manager, Senior Developer, Marketing Manager, Support Executive, etc.', 'erp' ); ?>
            </p>
        </div>

        <!-- Action Button -->
        <div style="text-align: center; margin-top: 32px;">
            <a class="button button-primary button-large" 
               href="<?php echo esc_url( admin_url( 'admin.php?page=erp' ) ); ?>"
               style="padding: 12px 24px; font-size: 16px; background: #3B82F6; border: none; border-radius: 6px; color: white; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                <?php esc_html_e( 'Go to Dashboard', 'erp' ); ?>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>
        <?php
    }

    /**
     * Install a plugin from .org in the background via a cron job (used by
     * installer - opt in).
     *
     * @param string $plugin_to_install_id plugin ID
     * @param array  $plugin_to_install    plugin information
     *
     * @throws Exception if unable to proceed with plugin installation
     *
     * @since  1.4.2
     */
    private function background_installer( $plugin_to_install_id, $plugin_to_install ) {
        if ( ! empty( $plugin_to_install['repo-slug'] ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            require_once ABSPATH . 'wp-admin/includes/plugin.php';

            WP_Filesystem();

            $skin              = new \Automatic_Upgrader_Skin();
            $upgrader          = new \WP_Upgrader( $skin );
            $installed_plugins = array_reduce( array_keys( get_plugins() ), [ $this, 'associate_plugin_file' ], [] );
            $plugin_slug       = $plugin_to_install['repo-slug'];
            $plugin_file       = isset( $plugin_to_install['file'] ) ? $plugin_to_install['file'] : $plugin_slug . '.php';
            $installed         = false;
            $activate          = false;

            // See if the plugin is installed already.
            if ( isset( $installed_plugins[ $plugin_file ] ) ) {
                $installed = true;
                $activate  = ! is_plugin_active( $installed_plugins[ $plugin_file ] );
            }

            // Install this thing!
            if ( ! $installed ) {
                // Suppress feedback.
                ob_start();

                try {
                    $plugin_information = plugins_api(
                        'plugin_information',
                        [
                            'slug'   => $plugin_slug,
                            'fields' => [
                                'short_description' => false,
                                'sections'          => false,
                                'requires'          => false,
                                'rating'            => false,
                                'ratings'           => false,
                                'downloaded'        => false,
                                'last_updated'      => false,
                                'added'             => false,
                                'tags'              => false,
                                'homepage'          => false,
                                'donate_link'       => false,
                                'author_profile'    => false,
                                'author'            => false,
                            ],
                        ]
                    );

                    if ( is_wp_error( $plugin_information ) ) {
                        throw new \Exception( $plugin_information->get_error_message() );
                    }

                    $package  = $plugin_information->download_link;
                    $download = $upgrader->download_package( $package );

                    if ( is_wp_error( $download ) ) {
                        throw new \Exception( $download->get_error_message() );
                    }

                    $working_dir = $upgrader->unpack_package( $download, true );

                    if ( is_wp_error( $working_dir ) ) {
                        throw new \Exception( $working_dir->get_error_message() );
                    }

                    $result = $upgrader->install_package(
                        [
                            'source'                      => $working_dir,
                            'destination'                 => WP_PLUGIN_DIR,
                            'clear_destination'           => false,
                            'abort_if_destination_exists' => false,
                            'clear_working'               => true,
                            'hook_extra'                  => [
                                'type'   => 'plugin',
                                'action' => 'install',
                            ],
                        ]
                    );

                    if ( is_wp_error( $result ) ) {
                        throw new \Exception( $result->get_error_message() );
                    }

                    $activate = true;
                } catch ( \Exception $e ) {
                }

                // Discard feedback.
                ob_end_clean();
            }

            wp_clean_plugins_cache();

            // Activate this thing.
            if ( $activate ) {
                try {
                    $result = activate_plugin( $installed ? $installed_plugins[ $plugin_file ] : $plugin_slug . '/' . $plugin_file );

                    // Stop page redirection after project manager activated via erp setup-wizard
                    delete_transient( '_pm_setup_page_redirect' );

                    if ( is_wp_error( $result ) ) {
                        throw new \Exception( $result->get_error_message() );
                    }
                } catch ( \Exception $e ) {
                }
            }
        }
    }

    /**
     * Returns associative plugin files for background installer
     *
     * @param array $plugins
     * @param string $key
     *
     * @return void
     */
    private function associate_plugin_file( $plugins, $key ) {
        $path                 = explode( '/', $key );
        $filename             = end( $path );
        $plugins[ $filename ] = $key;

        return $plugins;
    }
}

return new SetupWizard();
