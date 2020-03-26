<?php

use WeDevs\ERP\HRM\Models\Financial_Year;

$employee_types     = erp_hr_get_assign_policy_from_entitlement( get_current_user_id() );
$types              = $employee_types ? array_unique( $employee_types ) : [];
$financial_years    = array(
    '' => esc_attr__( 'select year', 'erp')
);
$current_start_date = erp_current_datetime()->modify( erp_financial_start_date() )->getTimestamp();
foreach ( Financial_Year::all() as $f_year ) {
    if ( $f_year['start_date'] < $current_start_date ) {
        continue;
    }
    $financial_years[ $f_year['id'] ] = $f_year['fy_name'];
}
?>
<div class="erp-hr-leave-request-new erp-hr-leave-reqs-wrap">

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => esc_html__( 'Year', 'erp' ),
            'name'     => 'f_year',
            'value'    =>  '',
            'required' => true,
            'class'    => 'f_year',
            'type'     => 'select',
            'options'  => $financial_years,
        ) ); ?>
    </div>

    <div class="row erp-hide erp-hr-leave-type-wrapper"></div>

    <?php do_action( 'erp_hr_leave_request_form_middle' ); ?>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'From', 'erp' ),
            'name'     => 'leave_from',
            'id'       => 'erp-hr-leave-req-from-date',
            'value'    => '',
            'required' => true,
            'class'    => 'erp-leave-date-field',
        ) ); ?>
    </div>

    <div class="row erp-leave-to-date">
        <?php erp_html_form_input( array(
            'label'    => __( 'To', 'erp' ),
            'name'     => 'leave_to',
            'id'       => 'erp-hr-leave-req-to-date',
            'value'    => '',
            'required' => true,
            'class'    => 'erp-leave-date-field',
        ) ); ?>
    </div>

    <div class="erp-hr-leave-req-show-days show-days" style="margin:20px 0px;"></div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'       => __( 'Reason', 'erp' ),
            'name'        => 'leave_reason',
            'type'        => 'textarea',
            'required'    => true,
            'custom_attr' => array( 'cols' => 25, 'rows' => 3 )
        ) ); ?>
    </div>

    <div class="row">
        <label for="leave_document"><?php echo esc_html__( 'Document', 'wp-erp' );?></label>
        <input type="file" name="leave_document[]" id="leave_document" multiple>
    </div>

    <input type="hidden" name="employee_id" id="erp-hr-leave-req-employee-id" value="<?php echo esc_html( get_current_user_id() ); ?>">
    <input type="hidden" name="action" value="erp-hr-leave-req-new">
    <?php wp_nonce_field( 'erp-leave-req-new' ); ?>
</div>
