<?php
/**
 * Showing accounting wp error message
 *
 * @since  1.1.7
 *
 * @return void
 */
function erp_ac_view_error_message() {
    $messages = isset( $_REQUEST['message'] ) && isset( $_REQUEST['message']['errors'] ) ? $_REQUEST['message']['errors'] : array();

    foreach( $messages as $key => $error ) { ?>
        <div id="message" class="error notice notice-success is-dismissible">
            <p><?php echo reset( $error ); ?></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e( 'Dismiss this notice.', 'erp'); ?></span></button>
        </div>
    <?php }
}

/**
 * Report filter form
 *
 * @since  1.1.7
 *
 * @return void
 */
function erp_ac_report_filter_form($start = true, $end = true) {
    $start = $start ? erp_format_date( date( 'Y-m-d', strtotime( erp_financial_start_date() ) ) ) : false;
    $end   = $end ? erp_format_date( date( 'Y-m-d', strtotime( erp_financial_end_date() ) ) ) : false;
    echo '<form class="erp-ac-report-filter-form" action="" method="post">';

    if ( $start ) {
        erp_html_form_input( array(
            'name'        => 'start',
            'type'        => 'text',
            'class'       => 'erp-date-picker-from',
            'placeholder' => __( 'Form', 'erp' ),
            'value'       => isset( $_GET['start'] ) ? $_GET['start'] : $start
        ) );
    }

    if ( $end ) {
        erp_html_form_input( array(
            'name'        => 'end',
            'type'        => 'text',
            'class'       => 'erp-date-picker-to',
            'placeholder' => __( 'To', 'erp' ),
            'value'       => isset( $_GET['end'] ) ? $_GET['end'] : $end
        ) );
    }

    wp_nonce_field('erp_ac_nonce_report');

    submit_button( __( 'Filter', 'erp' ), 'secondary', 'erp_ac_report_filter', false );

    echo '</form>';
}
