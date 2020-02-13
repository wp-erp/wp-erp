<?php 

use \WeDevs\ERP\HRM\Models\Leave;
use \WeDevs\ERP\HRM\Models\Leave_Policy;

$id            = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
$leaves        = Leave::all();
$leave_names   = [];
$submit_button = esc_attr('Save', 'erp');

foreach ( $leaves as $leave ) {
    $leave_names[$leave->id] = $leave->name;
}

if ( $id ) {
    $leave_policy = Leave_Policy::find( $id );
    $submit_button = esc_attr('Update', 'erp');
}

?>
<div class="wrap">
    <form class="leave-policy-form" action="<?php echo esc_url( erp_hr_new_policy_url( $id ) ); ?>" method="POST">

        <!-- show error message -->
        <?php global $policy_create_error;
            if ( isset( $policy_create_error ) && count( $policy_create_error->errors ) ) {
                echo '<ul>';
                foreach ( $policy_create_error->get_error_messages() as $error ) {
                    echo '<li style="color: #ef5350">* ' . $error . '</li>';
                }
                echo '</ul>';
            }
        ?>

        <div class="form-group">
            <div class="row">
                <?php erp_html_form_input( array(
                    'label'    => esc_html__( 'Policy Name', 'erp' ),
                    'name'     => 'leave_id',
                    'value'    => ! empty( $leave_policy ) ? $leave_policy->leave_id : '',
                    'type'     => 'select',
                    'class'    => 'leave-policy-input',
                    'required' => true,
                    'options'  => $leave_names
                ) ); ?>
            </div>

            <div class="row">
                <?php erp_html_form_input( array(
                    'label'       => esc_html__( 'Description', 'erp' ),
                    'type'        => 'textarea',
                    'class'       => 'leave-policy-input',
                    'name'        => 'description',
                    'value'       => ! empty( $leave_policy ) ? $leave_policy->description : '',
                    'placeholder' => esc_html__( '(optional)', 'erp' ),
                    'custom_attr' => [
                        'rows' => 3
                    ]
                ) ); ?>
            </div>

            <div class="row">
                <?php erp_html_form_input( array(
                    'label'       => esc_html__( 'Days', 'erp' ),
                    'name'        => 'days',
                    'value'       => ! empty( $leave_policy ) ? $leave_policy->days : '',
                    'class'       => 'leave-policy-input',
                    'required'    => true,
                    'help'        => esc_html__( 'Days in a calendar year.', 'erp' ),
                    'placeholder' => 20
                ) ); ?>
            </div>
        </div> <!-- .form-group -->

        <div class="form-group">
            <div class="row">
                <?php erp_html_form_input( array(
                    'label'       => esc_html__( 'Department', 'erp' ),
                    'name'        => 'department',
                    'value'       => ! empty( $leave_policy ) ? $leave_policy->department_id : '-1',
                    'class'       => 'leave-policy-input erp-hrm-select2-add-more erp-hr-dept-drop-down',
                    'custom_attr' => array( 'data-id' => 'erp-new-dept' ),
                    'type'        => 'select',
                    'options'     => erp_hr_get_departments_dropdown_raw( esc_html__( 'All Department', 'erp' ) )
                ) ); ?>
            </div>

            <div class="row">
                <?php erp_html_form_input( array(
                    'label'       => esc_html__( 'Designation', 'erp' ),
                    'name'        => 'designation',
                    'value'       => ! empty( $leave_policy ) ? $leave_policy->designation_id : '-1',
                    'class'       => 'leave-policy-input erp-hrm-select2-add-more erp-hr-desi-drop-down',
                    'custom_attr' => array( 'data-id' => 'erp-new-designation' ),
                    'type'        => 'select',
                    'options'     => erp_hr_get_designation_dropdown_raw( esc_html__( 'All Designations', 'erp' ) )
                ) ); ?>
            </div>

            <div class="row">
                <?php erp_html_form_input( array(
                    'label'   => esc_html__( 'Location', 'erp' ),
                    'name'    => 'location',
                    'value'   => ! empty( $leave_policy ) ? $leave_policy->location_id : '-1',
                    'type'    => 'select',
                    'class'   => 'leave-policy-input',
                    'options' => array(
                        '-1' => esc_html__( 'All Location', 'erp' )
                    ) + erp_company_get_location_dropdown_raw()
                ) ); ?>
            </div>
        </div> <!-- .form-group -->

        <div class="form-group">
            <div class="row">
                <?php erp_html_form_input( array(
                    'label'    => esc_html__( 'Calendar Color', 'erp' ),
                    'name'     => 'color',
                    'value'    => ! empty( $leave_policy ) ? $leave_policy->color : '#009688',
                    'required' => true,
                    'class'    => 'erp-color-picker'
                ) ); ?>
            </div>

            <div class="row">
                <?php erp_html_form_input( array(
                    'label'   => esc_html__( 'Gender', 'erp' ),
                    'name'    => 'gender',
                    'value'   => ! empty( $leave_policy ) ? $leave_policy->gender : '-1',
                    'class'   => 'leave-policy-input',
                    'type'    => 'select',
                    'options' => erp_hr_get_genders( esc_html__( 'All', 'erp' ) )
                ) ); ?>
            </div>

            <div class="row">
                <?php erp_html_form_input( array(
                    'label'   => esc_html__( 'Marital Status', 'erp' ),
                    'name'    => 'marital',
                    'value'   => ! empty( $leave_policy ) ? $leave_policy->marital : '-1',
                    'class'   => 'leave-policy-input erp-hrm-select2-add-more erp-hr-desi-drop-down',
                    'type'    => 'select',
                    'options' => erp_hr_get_marital_statuses( esc_html__( 'All', 'erp' ) )
                ) ); ?>
            </div>

            <div class="row">
                <?php
                $range = range( date('Y'), date('Y', strtotime('+5 years')) );
                
                erp_html_form_input( array(
                    'label'    => esc_html__( 'Financial Year', 'erp' ),
                    'name'     => 'f_year',
                    'value'    => ! empty( $leave_policy ) ? $leave_policy->f_year : date('Y'),
                    'required' => true,
                    'class'    => 'leave-policy-input erp-hrm-select2-add-more erp-hr-desi-drop-down',
                    'type'     => 'select',
                    'options'  => array_combine( $range, $range )
                ) ); ?>
            </div>
        </div> <!-- .form-group -->

        <?php wp_nonce_field( 'erp-leave-policy' ); ?>
        <input type="hidden" name="erp-action" value="hr-leave-policy-create">
        <input type="hidden" name="policy-id" value="<?php echo esc_attr( $id ); ?>">

        <?php submit_button( $submit_button ); ?>
    </form>
</div>