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

        // if we are here, we assume we don't need to run the wizard again
        // and the user doesn't need to be redirected here
        update_option( 'erp_setup_wizard_ran', '1' );

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

        $this->steps = [
            'introduction' => [
                'name'    => __( 'Introduction', 'erp' ),
                'view'    => [ $this, 'setup_step_introduction' ],
                'handler' => '',
            ],
            'basic' => [
                'name'    => __( 'Basic', 'erp' ),
                'view'    => [ $this, 'setup_step_basic' ],
                'handler' => [ $this, 'setup_step_basic_save' ],
            ],
            'module' => [
                'name'    => __( 'Module', 'erp' ),
                'view'    => [ $this, 'setup_step_module' ],
                'handler' => [ $this, 'setup_step_module_save' ],
            ],
            'email' => [
                'name'    => __( 'E-Marketing', 'erp' ),
                'view'    => [ $this, 'setup_step_email' ],
                'handler' => [ $this, 'setup_step_email_save' ],
            ],
            'department' => [
                'name'    => __( 'Departments', 'erp' ),
                'view'    => [ $this, 'setup_step_departments' ],
                'handler' => [ $this, 'setup_step_departments_save' ],
            ],
            'designation' => [
                'name'    => __( 'Designations', 'erp' ),
                'view'    => [ $this, 'setup_step_designation' ],
                'handler' => [ $this, 'setup_step_designation_save' ],
            ],
            'workdays' => [
                'name'    => __( 'Work Days', 'erp' ),
                'view'    => [ $this, 'setup_step_workdays' ],
                'handler' => [ $this, 'setup_step_workdays_save' ],
            ],
            // 'newsletter' => array(
            //     'name'    =>  __( 'Newsletter', 'erp' ),
            //     'view'    => array( $this, 'setup_step_newsletter' ),
            //     'handler' => array( $this, 'setup_step_newsletter_save' ),
            // ),
            'next_steps' => [
                'name'    => __( 'Ready!', 'erp' ),
                'view'    => [ $this, 'setup_step_ready' ],
                'handler' => '',
            ],
        ];

        $this->step = isset( $_GET['step'] ) ? sanitize_text_field( wp_unslash( $_GET['step'] ) ) : current( array_keys( $this->steps ) );
        $suffix     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '';

        wp_enqueue_style( 'jquery-ui', WPERP_ASSETS . '/vendor/jquery-ui/jquery-ui-1.9.1.custom.css' );
        wp_enqueue_style( 'erp-setup', WPERP_ASSETS . '/css/setup.css', [ 'dashicons', 'install' ] );

        wp_register_script( 'erp-select2', WPERP_ASSETS . '/vendor/select2/select2.full.min.js', false, false, true );
        wp_register_script( 'erp-setup', WPERP_ASSETS . "/js/erp$suffix.js", [ 'jquery', 'jquery-ui-datepicker', 'erp-select2' ], gmdate( 'Ymd' ), true );

        if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
            call_user_func( $this->steps[ $this->step ]['handler'] );
        }

        ob_start();
        $this->setup_wizard_header();
        $this->setup_wizard_steps();
        $this->setup_wizard_content();
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
            <h1 class="erp-logo"><a href="http://wperp.com/"><?php esc_html_e( 'WP ERP', 'erp' ); ?></a></h1>
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
     * Output the steps
     */
    public function setup_wizard_steps() {
        $output_steps = $this->steps;
        array_shift( $output_steps ); ?>
        <ol class="erp-setup-steps">
            <?php foreach ( $output_steps as $step_key => $step ) { ?>
                <li class="<?php
                    if ( $step_key === $this->step ) {
                        echo 'active';
                    } elseif ( array_search( $this->step, array_keys( $this->steps ) ) > array_search( $step_key, array_keys( $this->steps ) ) ) {
                        echo 'done';
                    }
                    ?>"><a href="<?php echo esc_url( admin_url( 'index.php?page=erp-setup&step=' . $step_key ) ); ?>"><?php echo esc_html( $step['name'] ); ?></a>
                </li>
            <?php } ?>
        </ol>
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
        <p class="erp-setup-actions step">
            <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'erp' ); ?>" name="save_step" />
            <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-large button-next"><?php esc_html_e( 'Skip this step', 'erp' ); ?></a>
            <?php wp_nonce_field( 'erp-setup' ); ?>
        </p>
        <?php
    }

    /**
     * Introduction step
     */
    public function setup_step_introduction() {
        ?>
        <h1><?php esc_html_e( 'Welcome to WP ERP!', 'erp' ); ?></h1>
        <p><?php echo wp_kses_post( __( 'Thank you for choosing WP-ERP. An easier way to manage your company! This quick setup wizard will help you configure the basic settings. <strong>It’s completely optional and shouldn’t take longer than three minutes.</strong>', 'erp' ) ); ?></p>
        <p><?php esc_html_e( 'No time right now? If you don’t want to go through the wizard, you can skip and return to the WordPress dashboard. Come back anytime if you change your mind!', 'erp' ); ?></p>
        <p class="erp-setup-actions step">
            <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button-primary button button-large button-next"><?php esc_html_e( 'Let\'s Go!', 'erp' ); ?></a>
            <a href="<?php echo esc_url( wp_get_referer() ? wp_get_referer() : admin_url( 'plugins.php' ) ); ?>" class="button button-large"><?php esc_html_e( 'Not right now', 'erp' ); ?></a>
        </p>
        <?php
    }

    /**
     * Basic setup steps
     *
     * @return void
     */
    public function setup_step_basic() {
        $general         = get_option( 'erp_settings_general', [] );
        $company         = new \WeDevs\ERP\Company();
        $business_type   = $company->business_type;

        $financial_month = isset( $general['gen_financial_month'] ) ? sanitize_text_field( wp_unslash( $general['gen_financial_month'] ) ) : '1';
        $company_started = isset( $general['gen_com_start'] ) ? sanitize_text_field( wp_unslash( $general['gen_com_start'] ) ) : ''; ?>
        <h1><?php esc_html_e( 'Basic Settings', 'erp' ); ?></h1>

        <form method="post">

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="company_name"><?php esc_html_e( 'Company Name', 'erp' ); ?></label></th>
                    <td>
                        <?php erp_html_form_input( [
                            'name'    => 'company_name',
                            'id'      => 'company_name',
                            'type'    => 'text',
                            'value'   => $company->name,
                            'help'    => __( 'This name will be shown as your company name.', 'erp' ),
                        ] ); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="gen_financial_month"><?php esc_html_e( 'Financial Year Starts', 'erp' ); ?></label></th>
                    <td>
                        <?php erp_html_form_input( [
                            'name'    => 'gen_financial_month',
                            'id'      => 'gen_financial_month',
                            'type'    => 'select',
                            'value'   => $financial_month,
                            'options' => erp_months_dropdown(),
                            'help'    => esc_html__( 'Financial and tax calculation starts from this month of every year.', 'erp' ),
                        ] ); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="gen_com_start"><?php esc_html_e( 'Company Start Date', 'erp' ); ?></label></th>
                    <td>
                        <?php erp_html_form_input( [
                            'name'        => 'gen_com_start',
                            'type'        => 'text',
                            'value'       => $company_started,
                            'help'        => esc_html__( 'The date the company officially started.', 'erp' ),
                            'class'       => 'erp-date-field',
                            'placeholder' => 'YYYY-MM-DD',
                        ] ); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="base_currency"><?php esc_html_e( 'Currency', 'erp' ); ?></label></th>
                    <td>
                        <?php erp_html_form_input( [
                            'name'    => 'base_currency',
                            'type'    => 'select',
                            'value'   => '1',
                            'options' => erp_get_currencies_for_dropdown(),
                            'desc'    => esc_html__( 'Format of date to show accross the system.', 'erp' ),
                        ] ); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="date_format"><?php esc_html_e( 'Date Format', 'erp' ); ?></label></th>
                    <td>
                        <?php erp_html_form_input( [
                            'name'    => 'date_format',
                            'type'    => 'select',
                            'value'   => 'd-m-Y',
                            'options' => [
                                'm-d-Y' => 'mm-dd-yyyy',
                                'd-m-Y' => 'dd-mm-yyyy',
                                'm/d/Y' => 'mm/dd/yyyy',
                                'd/m/Y' => 'dd/mm/yyyy',
                                'Y-m-d' => 'yyyy-mm-dd',
                            ],
                            'help' => esc_html__( 'Format of date to show accross the system.', 'erp' ),
                        ] ); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="business_type"><?php esc_html_e( 'What sort of business do you do?', 'erp' ); ?></label></th>
                    <td>
                        <?php
                        erp_html_form_input(
                            [
                                'name'    => 'business_type',
                                'type'    => 'select',
                                'value'   => $business_type,
                                'options' => [
                                    ''                 => '--' . __( 'select', 'erp' ) . '--',
                                    'Freelance'        => __( 'Freelance', 'erp' ),
                                    'FreelanceDev'     => __( 'Freelance (Developer)', 'erp' ),
                                    'FreelanceDes'     => __( 'Freelance (Design)', 'erp' ),
                                    'SmallBLocal'      => __( 'Small Business: Local Service (e.g. Hairdresser)', 'erp' ),
                                    'SmallBWeb'        => __( 'Small Business: Web Business', 'erp' ),
                                    'SmallBOther'      => __( 'Small Business (Other)', 'erp' ),
                                    'ecommerceWoo'     => __( 'eCommerce (WooCommerce)', 'erp' ),
                                    'ecommerceShopify' => __( 'eCommerce (Shopify)', 'erp' ),
                                    'ecommerceOther'   => __( 'eCommerce (Other)', 'erp' ),
                                    'Other'            => __( 'Other', 'erp' ),
                                ],
                            ]
                        ); ?>
                    </td>
                </tr>

                <?php if ( \WeDevs\ERP\Tracker::get_instance()->not_allowed() ) { ?>

                <tr>
                    <th scope="row"><label for="share_essentials"><?php esc_html_e( 'Share Essentials', 'erp' ); ?></label></th>

                    <td class="updated">
                        <input type="checkbox" name="share_essentials" id="share_essentials" class="switch-input">
                        <label for="share_essentials" class="switch-label">
                            <span class="toggle--on"><?php esc_html_e( 'On', 'erp' ); ?></span>
                            <span class="toggle--off"><?php esc_html_e( 'Off', 'erp' ); ?></span>
                        </label>
                        <span class="description">
                        <?php esc_html_e( 'Want to help make WP ERP even more awesome? Allow weDevs to collect non-sensitive diagnostic data and usage information.', 'erp' ); ?>
                        <a class="insights-data-we-collect" href="#"> <?php esc_html_e( 'what we collect', 'erp' ); ?></a>
                        </span>
                        <p class="description" style="display:none;">
                        <?php
                        printf(
                            esc_html__(
                                /* translators: 1) opening anchor tag with link, 2) closing anchor tag */
                                'Server environment details (php, mysql, server, WordPress versions), Number of users in your site, Site language, Number of active and inactive plugins, Site name and url, Your name and email address. No sensitive data is tracked. We are using Appsero to collect your data. %1$sLearn more%2$s about how Appsero collects and handle your data.',
                                'erp'
                            ),
                            '<a href="https://appsero.com/privacy-policy/">',
                            '</a>'
                        );
                        ?>
                        </p>
                    </td>

                    <script type="text/javascript">
                        jQuery('.insights-data-we-collect').on('click', function(e) {
                            e.preventDefault();
                            jQuery(this).parents('.updated').find('p.description').slideToggle('fast');
                        });
                    </script>
                </tr>
                <?php } ?>
            </table>

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
        $business_type    = isset( $_POST['business_type'] ) ? sanitize_text_field( wp_unslash( $_POST['business_type'] ) ) : '';
        $financial_month  = isset( $_POST['gen_financial_month'] ) ? sanitize_text_field( wp_unslash( $_POST['gen_financial_month'] ) ) : '';
        $company_started  = isset( $_POST['gen_com_start'] ) ? sanitize_text_field( wp_unslash( $_POST['gen_com_start'] ) ) : '';
        $base_currency    = isset( $_POST['base_currency'] ) ? sanitize_text_field( wp_unslash( $_POST['base_currency'] ) ) : '';
        $date_format      = isset( $_POST['date_format'] ) ? sanitize_text_field( wp_unslash( $_POST['date_format'] ) ) : '';
        $share_essentials = isset( $_POST['share_essentials'] ) ? sanitize_text_field( wp_unslash( $_POST['share_essentials'] ) ) : '';
        $company          = new \WeDevs\ERP\Company();
        $allowed          = '1';

        if ( $share_essentials === $allowed ) {
            // Appsero Tracker allow
            \WeDevs\ERP\Tracker::get_instance()->optin();
        } else {
            // Appsero Tracker disallow
            \WeDevs\ERP\Tracker::get_instance()->optout();
        }

        $company->update( [
            'name'          => $company_name,
            'business_type' => $business_type,
        ] );

        update_option( 'erp_settings_general', [
            'gen_financial_month' => $financial_month,
            'gen_com_start'       => $company_started,
            'date_format'         => $date_format,
            'erp_currency'        => $base_currency,
            'erp_debug_mode'      => 0,
        ] );

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

        <h1><?php esc_html_e( 'Active Modules', 'erp' ); ?></h1>

        <form method="post">
            <table class="form-table">
            <?php
            foreach ( $modules as $slug => $module ) {
                $checked = array_key_exists( $slug, $all_active_modules ) ? $slug : ''; ?>
                <tr>
                    <th scope="row">
                        <label for="erp_module_<?php echo esc_attr( $slug ); ?>"><?php echo isset( $module['title'] ) ? esc_html( $module['title'] ) : ''; ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="modules[]" id="erp_module_<?php echo esc_attr( $slug ); ?>" class="switch-input" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $slug, $checked ); ?>>
                        <label for="erp_module_<?php echo esc_attr( $slug ); ?>" class="switch-label">
                            <span class="toggle--on"><?php esc_html_e( 'On', 'erp' ); ?></span>
                            <span class="toggle--off"><?php esc_html_e( 'Off', 'erp' ); ?></span>
                        </label>
                        <span class="description"><?php echo isset( $module['description'] ) ? esc_html( $module['description'] ) : ''; ?></span>
                    </td>
                </tr>
                <?php
            } ?>
            </table>

            <span class="plugin-install-info">
                <span class="plugin-install-info-label">
                    <?php esc_html_e( 'The following plugin will be installed and activated for you', 'erp' ); ?>:
                </span>
                <br>
                <span class="plugin-install-info-list">
                    <span class="plugin-install-info-list-item">
                        <a href="https://wordpress.org/plugins/wedevs-project-manager/" target="_blank"><?php echo esc_html( 'WP Project Manager' ); ?></a>
                    </span>
                </span>
            </span>

            <script type="text/javascript">
                var erpModulePm       = jQuery('#erp_module_pm');
                var pluginInstallInfo = jQuery('.plugin-install-info');

                <?php if ( 'no' == $include_pm ) { ?>
                    pluginInstallInfo.css('display', 'none');
                <?php } ?>

                // toggle project manager on/off
                erpModulePm.on('click', function(e) {
                    if ( erpModulePm.is(':checked') ) {
                        pluginInstallInfo.css('display', 'block');
                    } else {
                        pluginInstallInfo.css('display', 'none');
                    }
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
     * Wemail setup step
     *
     * @since 1.7.1
     *
     * @return void
     */
    public function setup_step_email() {
        // Should `weMail` plugin installs by default?
        $include_wm  = get_option( 'include_wemail' );
        ?>
        <h1><?php esc_html_e( 'Email Marketing Setup', 'erp' ); ?></h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <td>
                        <label for="wemail_install">
                            <span class="description">
                                <?php
                                printf(
                                    /* translators: weMail plugin name with style */
                                    esc_html__( 'To collect and create your CRM leads and subscriers, we recommend installing %s plugin. ', 'erp' ),
                                    '<em style="color: #19ACB8;">weMail</em>'
                                );
                                ?>
                                <br/>
                                <?php
                                printf(
                                    /* translators: WP ERP plugin name with style */
                                    esc_html__( 'It simplifies email marketing inside the WordPress dashboard and it has tight integration with %s plugin.', 'erp' ),
                                    '<em style="color: #19ACB8;">WP ERP</em>'
                                );
                                ?>
                            </span>
                        </label><br/><br/>
                        <input type="checkbox" name="wemail_install" id="wemail_install" class="switch-input" value="yes" checked>
                        <label for="wemail_install" class="switch-label">
                            <span class="toggle--on"></span>
                            <span class="toggle--off"></span>
                            <?php esc_html_e( 'Install weMail plugin for email marketing', 'erp' ); ?>
                        </label>
                    </td>
                </tr>
            </table>

            <span class="plugin-install-info">
                <span class="plugin-install-info-label"><?php esc_html_e( 'The following plugin will be installed and activated for you: ', 'erp' ); ?></span>
                <br>
                <span class="plugin-install-info-list">
                    <span class="plugin-install-info-list-item">
                        <a href="https://wordpress.org/plugins/wemail/" target="_blank"><?php echo esc_html( 'weMail' ); ?></a>
                    </span>
                </span>
            </span>

            <script type="text/javascript">
                var weMailIstall      = jQuery('#wemail_install');
                var weMailInstallInfo = jQuery('.plugin-install-info');

                <?php if ( 'no' == $include_wm ) { ?>
                    weMailInstallInfo.css('display', 'none');
                <?php } ?>

                // toggle project manager on/off
                weMailIstall.on('click', function(e) {
                    if ( weMailIstall.is(':checked') ) {
                        weMailInstallInfo.css('display', 'block');
                    } else {
                        weMailInstallInfo.css('display', 'none');
                    }
                });
            </script>

            <?php $this->next_step_buttons(); ?>
        </form>
        <?php
    }

    /**
     * WeMail setup step save
     *
     * @since 1.7.1
     *
     * @return void
     */
    public function setup_step_email_save() {
        check_admin_referer( 'erp-setup' );

        $install_wemail   = isset( $_POST['wemail_install'] ) ? sanitize_text_field( wp_unslash( $_POST['wemail_install'] ) ) : 'no';

        // if `weMail` plugin needs to be installed
        if ( 'yes' === $install_wemail ) {
            $wemail_plugin_id = 'wemail';
            $wemail_plugin    = [
                'name'      => __( 'weMail', 'erp' ),
                'repo-slug' => 'wemail',
                'file'      => 'wemail.php',
            ];

            $this->background_installer( $wemail_plugin_id, $wemail_plugin );
        }

        update_option( 'include_wemail', $install_wemail );

        wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    /**
     * Departments setup step
     *
     * @return void
     */
    public function setup_step_departments() {
        ?>
        <h1><?php esc_html_e( 'Departments Setup', 'erp' ); ?></h1>
        <form method="post" class="form-table">

            <div class="two-col">
                <div class="col-first">
                    <p><?php esc_html_e( 'Create some departments for your company. e.g. HR, Engineering, Marketing, Support, etc. ', 'erp' ); ?></p>
                    <p><?php esc_html_e( 'Leave empty for not to create any departments.', 'erp' ); ?></p>
                </div>

                <div class="col-last">
                    <ul class="unstyled">
                        <li>
                            <input type="text" name="departments[]" class="regular-text" placeholder="<?php esc_attr_e( 'Department name', 'erp' ); ?>">
                        </li>
                        <li>
                            <input type="text" name="departments[]" class="regular-text" placeholder="<?php esc_attr_e( 'Department name', 'erp' ); ?>">
                        </li>
                        <li class="add-new"><a href="#" class="button"><?php esc_html_e( 'Add New', 'erp' ); ?></a></li>
                    </ul>
                </div>
            </div>

            <?php $this->next_step_buttons(); ?>
        </form>
        <?php

        $this->script_input_duplicator();
    }

    /**
     * Input duplicator script
     *
     * @return void
     */
    public function script_input_duplicator() {
        ?>
        <script type="text/javascript">
            jQuery(function($) {
                $('.add-new').on('click', 'a', function(e) {
                    e.preventDefault();

                    var self = $(this),
                        parent = self.closest('li');

                    parent.prev().clone().insertBefore( parent ).find('input').val('');
                });
            });
        </script>
        <?php
    }

    /**
     * Saves departments setup step data
     *
     * @return void
     */
    public function setup_step_departments_save() {
        check_admin_referer( 'erp-setup' );

        $departments = isset( $_POST['departments'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['departments'] ) ) : [];

        if ( $departments ) {
            foreach ( $departments as $department ) {
                if ( ! empty( $department ) ) {
                    erp_hr_create_department( [
                        'title' => $department,
                    ] );
                }
            }
        }

        wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    /**
     * Designations setup step
     *
     * @return void
     */
    public function setup_step_designation() {
        ?>
        <h1><?php esc_html_e( 'Designation Setup', 'erp' ); ?></h1>
        <form method="post" class="form-table">

            <div class="two-col">
                <div class="col-first">
                    <p><?php esc_html_e( 'Create some designations for your company. e.g. Manager, Senior Developer, Marketing Manager, Support Executive, etc. ', 'erp' ); ?></p>
                    <p><?php esc_html_e( 'Leave empty for not to create any designations.', 'erp' ); ?></p>
                </div>

                <div class="col-last">
                    <ul class="unstyled">
                        <li>
                            <input type="text" name="designations[]" class="regular-text" placeholder="<?php esc_attr_e( 'Designation name', 'erp' ); ?>">
                        </li>
                        <li>
                            <input type="text" name="designations[]" class="regular-text" placeholder="<?php esc_attr_e( 'Designation name', 'erp' ); ?>">
                        </li>
                        <li class="add-new"><a href="#" class="button"><?php esc_html_e( 'Add New', 'erp' ); ?></a></li>
                    </ul>
                </div>
            </div>

            <?php $this->next_step_buttons(); ?>
        </form>
        <?php

        $this->script_input_duplicator();
    }

    /**
     * Saves departments setup step data
     *
     * @return void
     */
    public function setup_step_designation_save() {
        check_admin_referer( 'erp-setup' );

        $designations = isset( $_POST['designations'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['designations'] ) ) : [];

        if ( $designations ) {
            foreach ( $designations as $designation ) {
                if ( ! empty( $designation ) ) {
                    erp_hr_create_designation( [
                        'title' => $designation,
                    ] );
                }
            }
        }

        wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    /**
     * Step to set workdays
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function setup_step_workdays() {
        $working_days = erp_company_get_working_days();
        $options      = [
            '8' => __( 'Full Day', 'erp' ),
            '4' => __( 'Half Day', 'erp' ),
            '0' => __( 'Non-working Day', 'erp' ),
        ];
        $days = [
            'mon' => __( 'Monday', 'erp' ),
            'tue' => __( 'Tuesday', 'erp' ),
            'wed' => __( 'Wednesday', 'erp' ),
            'thu' => __( 'Thursday', 'erp' ),
            'fri' => __( 'Friday', 'erp' ),
            'sat' => __( 'Saturday', 'erp' ),
            'sun' => __( 'Sunday', 'erp' ),
        ]; ?>
        <h1><?php esc_html_e( 'Workdays Setup', 'erp' ); ?></h1>

        <form method="post">
            <table class="form-table">

                <?php
                foreach ( $days as $key => $day ) {
                    ?>
                    <tr>
                        <th scope="row"><label for="gen_financial_month"><?php echo esc_html( $day ); ?></label></th>
                        <td>
                            <?php echo wp_kses_post( erp_html_form_input( [
                                'name'     => 'day[' . $key . ']',
                                'value'    => $working_days[ $key ],
                                'type'     => 'select',
                                'options'  => $options,
                            ] ).'' ); ?>
                        </td>
                    </tr>
                <?php
                } ?>

            </table>

            <?php $this->next_step_buttons(); ?>
        </form>
        <?php
    }

    /**
     * Save company working days data
     *
     * @since 1.0.0
     * @since 1.1.14 Saving data in sun, mon etc keys, instead of `erp_settings_erp-hr_workdays`
     *               since ERP Settings saves data with these keys
     *
     * @return void
     */
    public function setup_step_workdays_save() {
        check_admin_referer( 'erp-setup' );

        if ( isset( $_POST['day'] ) && 7 === count( $_POST['day'] ) ) {
            $days = array_map( 'sanitize_text_field', wp_unslash( $_POST['day'] ) );

            foreach ( $days as $day => $hour_limit ) {
                update_option( $day, $hour_limit );
            }
        }

        wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    /**
     * Newsletter setup step
     *
     * @since 1.3.4
     */
    public function setup_step_newsletter() {
        ?>
        <h1><?php esc_html_e( 'Newsletter Setup', 'erp' ); ?></h1>

        <?php
        $subscription = new \WeDevs\ERP\CRM\Subscription(); ?>

        <form method="post">

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="full_name"><?php esc_html_e( 'Your Name', 'erp' ); ?></label>
                    </th>
                    <td>
                        <?php erp_html_form_input( [
                            'name'     => 'full_name',
                            'value'    => '',
                            'type'     => 'text',
                            'help'     => __( 'This name will be used for newsletter name.', 'erp' ),
                        ] ); ?>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="subs_email"><?php esc_html_e( 'Your Email', 'erp' ); ?></label>
                    </th>
                    <td>
                        <?php erp_html_form_input( [
                            'name'     => 'full_name',
                            'value'    => '',
                            'type'     => 'email',
                            'help'     => __( 'Email to get update critical updates.', 'erp' ),
                        ] ); ?>
                    </td>
                </tr>
            </table>

            <?php $this->next_step_buttons(); ?>
        </form>
        <?php
    }

    /**
     * Newsletter setup step save
     *
     * @since 1.3.4
     */
    public function setup_step_newsletter_save() {
        check_admin_referer( 'erp-setup' );

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

        <div class="final-step">
            <h1><?php esc_html_e( 'Your Site is Ready!', 'erp' ); ?></h1>

            <div class="erp-setup-next-steps">
                <div class="erp-setup-next-steps-first">
                    <h2><?php esc_html_e( 'Next Steps &rarr;', 'erp' ); ?></h2>
                    <?php
                        $is_hrm_activated = erp_is_module_active( 'HRM' );

                        if ( $is_hrm_activated ) { ?>
                            <a class="button button-primary button-large btn-add-employees"
                                href="<?php echo esc_url( admin_url( 'admin.php?page=erp-hr&section=people&sub-section=employee' ) ); ?>">
                                <?php esc_html_e( 'Add your employees!', 'erp' ); ?>
                            </a>
                        <?php } ?>

                        <a class="button button-primary button-large"
                            href="<?php echo esc_url( admin_url( 'admin.php?page=erp' ) ); ?>">
                            <?php esc_html_e( 'Go to ERP Dashboard!', 'erp' ); ?>
                        </a>
                </div>
            </div>
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
