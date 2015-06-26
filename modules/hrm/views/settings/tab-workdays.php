<?php
$working_days = erp_company_get_working_days();
$options = array(
    '8' => __( 'Full Day', 'wp-erp' ),
    '4' => __( 'Half Day', 'wp-erp' ),
    '0' => __( 'Non-working Day', 'wp-erp' )
);
$days = array(
    'mon' => __( 'Monday', 'wp-erp' ),
    'tue' => __( 'Tuesday', 'wp-erp' ),
    'wed' => __( 'Wednesday', 'wp-erp' ),
    'thu' => __( 'Thursday', 'wp-erp' ),
    'fri' => __( 'Friday', 'wp-erp' ),
    'sat' => __( 'Saturday', 'wp-erp' ),
    'sun' => __( 'Sunday', 'wp-erp' )
);
?>

<form action="" method="post">

    <ul class="erp-list separated">
    <?php
    foreach( $days as $key => $day ) {
        erp_html_form_input( array(
            'label'    => $day,
            'name'     => 'day[' . $key . ']',
            'value'    => $working_days[ $key ],
            'type'     => 'select',
            'tag'      => 'li',
            'options'  => $options
        ) );
    }
    ?>
    </ul>

    <input type="hidden" name="erp-action" value="settings-save-work-days">

    <?php wp_nonce_field( 'erp-settings' ); ?>
    <?php submit_button( __( 'Save Changes', 'wp-erp' ), 'primary' ); ?>
</form>