<div class="wrap erp erp-employee-single">
    <h2>Employee <a href="#" class="add-new-h2">Add New</a></h2>

    <div class="erp-single-container">
        <div class="erp-area-left">
            <div class="erp-profile-top">
                <div class="erp-avatar">
                    <?php echo get_avatar( 'john@doe.com', 128 ); ?>
                </div>

                <div class="erp-user-info">
                    <h3><span class="title">John Doe</span></h3>

                    <ul class="lead-info">
                        <li>
                            Software Engineer - Engineering
                        </li>

                        <li>
                            <a href="mailto:john@doe.com">john@doe.com</a>
                        </li>

                        <li>
                            <a href="tel:+8802457821"><span class="dashicons dashicons-smartphone"></span></a> +8802457821
                        </li>
                    </ul>
                </div><!-- .leads-user-info -->
            </div><!-- .leads-profile-view -->

            <?php
            $active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
            $tabs = apply_filters( 'erp_hr_employee_single_tabs', array(
                'general' => array(
                    'title'    => __( 'General', 'wp-erp' ),
                    'callback' => 'erp_hr_employee_single_tab_general'
                )
            ) );
            ?>

            <h2 class="nav-tab-wrapper" style="margin-bottom: 15px;">
                <?php foreach ($tabs as $key => $tab) {
                    $active_class = ( $key == $active_tab ) ? ' nav-tab-active' : '';
                    ?>
                    <a href="<?php echo add_query_arg( array( 'tab' => $key ), erp_hr_url_single_employee(1) ); ?>" class="nav-tab<?php echo $active_class; ?>"><?php echo $tab['title'] ?></a>
                <?php } ?>
            </h2>

            <?php
            $employee = new stdClass();
            // call the tab callback function
            if ( array_key_exists( $active_tab, $tabs ) && is_callable( $tabs[$active_tab]['callback'] ) ) {
                call_user_func_array( $tabs[$active_tab]['callback'], array( $employee ) );
            }
            ?>


        </div><!-- .leads-left -->

        <div class="erp-area-right">
            <div class="postbox leads-actions">
                <h3 class="hndle"><span>Actions</span></h3>
                <div class="inside">
                    <a class="button button-primary" href="#">Edit</a>
                    <a class="button" href="#">Terminate</a>
                    <a class="button" href="#">Print</a>
                </div>
            </div><!-- .postbox -->
        </div><!-- .leads-right -->
    </div><!-- .erp-leads-wrap -->
</div>