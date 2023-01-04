<?php

use WeDevs\ERP\HRM\Models\FinancialYear;

$employee_types     = erp_hr_get_assign_policy_from_entitlement( get_current_user_id() );
$types              = $employee_types ? array_unique( $employee_types ) : [];
$financial_years    = [];

$current_f_year = erp_hr_get_financial_year_from_date();

if ( null === $current_f_year ) {
    erp_html_show_notice( __( 'No leave assigned for current year. Please contact HR.', 'erp' ), 'error', true );

    return;
}

foreach ( FinancialYear::all() as $f_year ) {
    if ( $f_year['start_date'] < $current_f_year->start_date ) {
        continue;
    }
    $financial_years[ $f_year['id'] ] = $f_year['fy_name'];
}
?>
<div class="erp-hr-leave-request-new erp-hr-leave-reqs-wrap">
    <?php
    if ( count( $financial_years ) === 1 ) { ?>
        <input type="hidden" name="f_year" id="f_year" class="f_year" value="<?php echo esc_attr( key( $financial_years ) ); ?>" />
        <?php
    } else {
        echo '<div class="row">';
        erp_html_form_input( [
            'label'    => esc_html__( 'Year', 'erp' ),
            'name'     => 'f_year',
            'value'    => '',
            'required' => true,
            'class'    => 'f_year',
            'type'     => 'select',
            'options'  => $financial_years,
        ] );
        echo '</div>';
    }?>

    <div class="row erp-hide erp-hr-leave-type-wrapper"></div>

    <?php do_action( 'erp_hr_leave_request_form_middle' ); ?>

    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'From', 'erp' ),
            'name'        => 'leave_from',
            'id'          => 'erp-hr-leave-req-from-date',
            'value'       => '',
            'required'    => true,
            'class'       => 'erp-leave-date-field',
            'custom_attr' => [
                'autocomplete' => 'off',
            ],
        ] ); ?>
    </div>

    <div class="row erp-leave-to-date">
        <?php erp_html_form_input( [
            'label'       => __( 'To', 'erp' ),
            'name'        => 'leave_to',
            'id'          => 'erp-hr-leave-req-to-date',
            'value'       => '',
            'required'    => true,
            'class'       => 'erp-leave-date-field',
            'custom_attr' => [
                'autocomplete' => 'off',
            ],
        ] ); ?>
    </div>

    <div class="erp-hr-leave-req-show-days show-days" style="margin:20px 0px;"></div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'Reason', 'erp' ),
            'name'        => 'leave_reason',
            'type'        => 'textarea',
            'required'    => true,
            'custom_attr' => [ 'cols' => 25, 'rows' => 3 ],
        ] ); ?>
    </div>

    <div class="row">
        <label for="leave_document"><?php echo esc_html__( 'Document', 'erp' ); ?></label>
        <input type="file" name="leave_document[]" id="leave_document" multiple>
    </div>

    <input type="hidden" name="employee_id" id="erp-hr-leave-req-employee-id" value="<?php echo esc_attr( get_current_user_id() ); ?>">
    <input type="hidden" name="action" value="erp-hr-leave-req-new">
    <?php wp_nonce_field( 'erp-leave-req-new' ); ?>
</div>
