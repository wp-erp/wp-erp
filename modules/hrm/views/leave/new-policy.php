<?php

use WeDevs\ERP\ErpErrors;
use WeDevs\ERP\HRM\Models\FinancialYear;
use WeDevs\ERP\HRM\Models\LeavePolicy;

$id            = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
$action        = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
$disabled      = false;
$leave_policy  = [];
$submit_button = esc_attr__( 'Save', 'erp' );

$leave_names   = [
                    '' => '&mdash; ' . esc_attr__( 'select leave type', 'erp' ) . ' &mdash;',
                ] + wp_list_pluck( erp_hr_get_leave_policy_names()->toArray(), 'name', 'id' );

// edit / copy
if ( $id ) {
    $leave_policy = LeavePolicy::find( $id );

    if ( $action === 'edit' ) {
        $disabled      = true;
        $submit_button = esc_attr__( 'Update', 'erp' );
    } elseif ( $action === 'copy' ) {
        $disabled      = false;
        $submit_button = esc_attr__( 'Copy', 'erp' );
    }
}

$financial_years = [];
$current_f_year  = erp_hr_get_financial_year_from_date();

foreach ( FinancialYear::all() as $f_year ) {
    if ( $f_year['start_date'] < $current_f_year->start_date ) {
        continue;
    }
    $financial_years[ $f_year['id'] ] = $f_year['fy_name'];
}

$leave_help_text = esc_html__( 'Select A Leave Type', 'erp' ) . ' ' . esc_attr__( 'Or', 'erp' ) . ' ' . sprintf( '<a href="?page=erp-hr&section=leave&sub-section=policies&type=policy-name">%s</a>', __( 'Add New', 'erp' ) );

$f_year_help_text = __( 'Select Year', 'erp' ) . ' ' . esc_attr__( 'Or', 'erp' ) . ' ' . sprintf( '<a href="?page=erp-settings#/erp-hr/financial">%s</a>', __( 'Add New', 'erp' ) );

// get error data
$errors         = new ErpErrors( 'policy_create_error' );
$error_messages = '';
$form_data      = [];

if ( $errors->has_error() ) {
    $error_messages = $errors->display();
    $form_data      = $errors->get_form_data();
}

// populate form data
$f_year = ! empty( $form_data ) ? $form_data['f-year'] : ( ! empty( $leave_policy ) ? $leave_policy->f_year : ( ! empty( $current_f_year ) ? $current_f_year->id : '' ) );

?>
<div class="wrap">
    <h2><?php esc_html_e( 'Leave Policies', 'erp' ); ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=erp-hr&section=leave&sub-section=policies' ) ); ?>" id="erp-leave-policy-new" class="add-new-h2">
            <?php esc_html_e( 'Back To Leave Policies', 'erp' ); ?>
        </a>
    </h2>
    <form class="leave-policy-form" action="<?php echo esc_url( erp_hr_new_policy_url( $id, $action ) ); ?>" method="POST">

        <!-- show error message -->
        <?php echo wp_kses_post( $error_messages ); ?>

        <div class="form-group">
            <div class="row">
                <?php
                erp_html_form_input( [
                    'label'    => esc_html__( 'Year', 'erp' ),
                    'name'     => 'f-year',
                    'required' => true,
                    'class'    => 'leave-policy-input erp-hrm-select2-add-more erp-hr-desi-drop-down',
                    'type'     => 'select',
                    'help'     => $f_year_help_text,
                    'value'    => $f_year,
                    'options'  => [
                                      '' => '&mdash; ' . esc_attr__( 'Select year', 'erp' ) . ' &mdash;',
                                  ] + $financial_years,
                    'disabled' => $disabled,
                ] ); ?>
            </div>

            <div class="row">
                <?php erp_html_form_input( [
                    'label'    => esc_html__( 'Leave Type', 'erp' ),
                    'name'     => 'leave-id',
                    'value'    => ! empty( $leave_policy ) ? $leave_policy->leave_id : '',
                    'type'     => 'select',
                    'class'    => 'leave-policy-input',
                    'required' => true,
                    'options'  => $leave_names,
                    'help'     => $leave_help_text,
                    'disabled' => $disabled,
                ] ); ?>
            </div>

            <div class="row">
                <?php
                erp_html_form_input( [
                    'label'    => __( 'Employee Type', 'erp' ),
                    'name'     => 'employee_type',
                    'value'    => ! empty( $leave_policy ) ? $leave_policy->employee_type : 'permanent',
                    'class'    => 'leave-policy-input erp-hrm-select2',
                    'type'     => 'select',
                    'required' => true,
                    'options'  => [
                            '-1' => esc_html__( 'All', 'erp' ),
                        ] + erp_hr_get_employee_types(),
                    'disabled' => $disabled,
                ] );
                ?>
            </div>

            <div class="row">
                <?php erp_html_form_input( [
                    'label'       => esc_html__( 'Description', 'erp' ),
                    'type'        => 'textarea',
                    'class'       => 'leave-policy-input',
                    'name'        => 'description',
                    'value'       => ! empty( $leave_policy ) ? $leave_policy->description : '',
                    'placeholder' => esc_html__( '(optional)', 'erp' ),
                    'custom_attr' => [
                        'rows' => 3,
                    ],
                ] ); ?>
            </div>

            <div class="row">
                <?php erp_html_form_input( [
                    'label'       => esc_html__( 'Days', 'erp' ),
                    'name'        => 'days',
                    'value'       => ! empty( $leave_policy ) ? $leave_policy->days : '',
                    'class'       => 'leave-policy-input',
                    'required'    => true,
                    'help'        => esc_html__( 'Days in a calendar year.', 'erp' ),
                    'placeholder' => 20,
                    'readonly'    => $disabled, // we need to pass the days to check errors
                ] ); ?>
            </div>

            <div class="row applicable-form-row">
                <?php erp_html_form_input( [
                    'label' => __( 'Applicable After', 'erp' ),
                    'name'  => 'applicable-from',
                    'class' => 'leave-policy-input',
                    'value' => ! empty( $leave_policy ) ? $leave_policy->applicable_from_days : '0',
                    'type'  => 'number',
                ] ); ?>
                <span><?php esc_html_e( 'Days', 'erp' ); ?></span>
                <p class="description"><?php echo esc_html__( 'Based on employee joining date.', 'erp' ); ?></p>
            </div>

            <div class="row">
                <?php erp_html_form_input( [
                    'label'    => esc_html__( 'Calendar Color', 'erp' ),
                    'name'     => 'color',
                    'value'    => ! empty( $leave_policy ) ? $leave_policy->color : '#009688',
                    'required' => true,
                    'class'    => 'erp-color-picker',
                ] ); ?>
            </div>

            <div class="row">
                <?php
                erp_html_form_input( [
                    'label'   => esc_html__( 'Entitle New Employees', 'erp' ),
                    'name'    => 'apply-for-new-users',
                    'type'    => 'checkbox',
                    'value'   => ! empty( $leave_policy ) && $leave_policy->apply_for_new_users == '1' ? 'on' : '0',
                    'help'    => esc_attr__( 'Entitle new employees to this policy after hiring?', 'erp' ),
                    'tooltip' => esc_attr__( 'Check this checkbox if you want to entitle new employees to this policy after hiring.', 'erp' ),
                ] );
                ?>
            </div>

            <?php
            if ( ! $disabled ) {
                echo '<div class="row">';
                erp_html_form_input( [
                    'label' => esc_html__( 'Apply for existing employees', 'erp' ),
                    'name'  => 'apply-for-existing-users',
                    'type'  => 'checkbox',
                    'help'  => esc_attr__( 'Entitle existing employees to this policy?', 'erp' ),
                ] );
                echo '</div>';
            }
            ?>
        </div> <!-- .form-group -->

        <div class="form-group">
            <div class="row">
                <?php erp_html_form_input( [
                    'label'       => esc_html__( 'Department', 'erp' ),
                    'name'        => 'department',
                    'value'       => ! empty( $leave_policy ) ? $leave_policy->department_id : '-1',
                    'class'       => 'leave-policy-input erp-hrm-select2-add-more erp-hr-dept-drop-down',
                    'custom_attr' => [ 'data-id' => 'erp-new-dept' ],
                    'type'        => 'select',
                    'options'     => erp_hr_get_departments_dropdown_raw( esc_html__( 'All Department', 'erp' ) ),
                    'disabled'    => $disabled,
                ] ); ?>
            </div>

            <div class="row">
                <?php erp_html_form_input( [
                    'label'       => esc_html__( 'Designation', 'erp' ),
                    'name'        => 'designation',
                    'value'       => ! empty( $leave_policy ) ? $leave_policy->designation_id : '-1',
                    'class'       => 'leave-policy-input erp-hrm-select2-add-more erp-hr-desi-drop-down',
                    'custom_attr' => [ 'data-id' => 'erp-new-designation' ],
                    'type'        => 'select',
                    'options'     => erp_hr_get_designation_dropdown_raw( esc_html__( 'All Designations', 'erp' ) ),
                    'disabled'    => $disabled,
                ] ); ?>
            </div>

            <div class="row">
                <?php erp_html_form_input( [
                    'label'   => esc_html__( 'Location', 'erp' ),
                    'name'    => 'location',
                    'value'   => ! empty( $leave_policy ) ? $leave_policy->location_id : '-1',
                    'type'    => 'select',
                    'class'   => 'leave-policy-input',
                    'options' => [
                            '-1' => esc_html__( 'All Location', 'erp' ),
                        ] + erp_company_get_location_dropdown_raw(),
                    'disabled' => $disabled,
                ] ); ?>
            </div>
        </div> <!-- .form-group -->

        <div class="form-group">

            <div class="row">
                <?php erp_html_form_input( [
                    'label'    => esc_html__( 'Gender', 'erp' ),
                    'name'     => 'gender',
                    'value'    => ! empty( $leave_policy ) ? $leave_policy->gender : '-1',
                    'class'    => 'leave-policy-input',
                    'type'     => 'select',
                    'options'  => erp_hr_get_genders( esc_html__( 'All', 'erp' ) ),
                    'disabled' => $disabled,
                ] ); ?>
            </div>

            <div class="row">
                <?php erp_html_form_input( [
                    'label'    => esc_html__( 'Marital Status', 'erp' ),
                    'name'     => 'marital',
                    'value'    => ! empty( $leave_policy ) ? $leave_policy->marital : '-1',
                    'class'    => 'leave-policy-input erp-hrm-select2-add-more erp-hr-desi-drop-down',
                    'type'     => 'select',
                    'options'  => erp_hr_get_marital_statuses( esc_html__( 'All', 'erp' ) ),
                    'disabled' => $disabled,
                ] ); ?>
            </div>

        </div> <!-- .form-group -->

        <?php do_action( 'erp-hr-leave-policy-form-bottom', $leave_policy ); ?>

        <?php wp_nonce_field( 'erp-leave-policy' ); ?>
        <input type="hidden" name="erp-action" value="hr-leave-policy-create">
        <input type="hidden" name="policy-id" value="<?php echo $action === 'copy' ? 0 : esc_attr( $id ); ?>">

        <?php submit_button( $submit_button ); ?>
    </form>
</div>
