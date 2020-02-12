<div class="wrap">
    <form class="leave-policy-form" action="<?php echo esc_url( erp_hr_new_policy_url() ); ?>" method="POST">

        <!-- show error message -->
        <?php global $policy_create_error;
            if ( is_wp_error( $policy_create_error ) ) {
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
                    'value'    => 'red',
                    'type'     => 'select',
                    'required' => true,
                    'options'  => array(
                        'white' => esc_html__( 'White', 'erp' ),
                        'blue'  => esc_html__( 'Blue', 'erp' ),
                        'red'   => esc_html__( 'Red', 'erp' ),
                    )
                ) ); ?>
            </div>

            <div class="row">
                <?php erp_html_form_input( array(
                    'label'       => esc_html__( 'Days', 'erp' ),
                    'name'        => 'days',
                    'value'       => '',
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
                    'value'       => '-1',
                    'class'       => 'erp-hrm-select2-add-more erp-hr-dept-drop-down',
                    'custom_attr' => array( 'data-id' => 'erp-new-dept' ),
                    'type'        => 'select',
                    'options'     => erp_hr_get_departments_dropdown_raw( esc_html__( 'All Department', 'erp' ) )
                ) ); ?>
            </div>

            <div class="row">
                <?php erp_html_form_input( array(
                    'label'       => esc_html__( 'Designation', 'erp' ),
                    'name'        => 'designation',
                    'value'       => '-1',
                    'class'       => 'erp-hrm-select2-add-more erp-hr-desi-drop-down',
                    'custom_attr' => array( 'data-id' => 'erp-new-designation' ),
                    'type'        => 'select',
                    'options'     => erp_hr_get_designation_dropdown_raw( esc_html__( 'All Designations', 'erp' ) )
                ) ); ?>
            </div>

            <div class="row">
                <?php erp_html_form_input( array(
                    'label'   => esc_html__( 'Location', 'erp' ),
                    'name'    => 'location',
                    'value'   => '-1',
                    'type'    => 'select',
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
                    'value'    => '#009688',
                    'required' => true,
                    'class'    => 'erp-color-picker'
                ) ); ?>
            </div>

            <div class="row">
                <?php erp_html_form_input( array(
                    'label'       => esc_html__( 'Gender', 'erp' ),
                    'name'        => 'gender',
                    'value'       => '-1',
                    'type'        => 'select',
                    'options' => erp_hr_get_genders( esc_html__( 'All', 'erp' ) )
                ) ); ?>
            </div>

            <div class="row">
                <?php erp_html_form_input( array(
                    'label'   => esc_html__( 'Marital Status', 'erp' ),
                    'name'    => 'maritial',
                    'value'   => '-1',
                    'class'   => 'erp-hrm-select2-add-more erp-hr-desi-drop-down',
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
                    'value'    => date('Y'),
                    'required' => true,
                    'class'    => 'erp-hrm-select2-add-more erp-hr-desi-drop-down',
                    'type'     => 'select',
                    'options'  => array_combine( $range, $range )
                ) ); ?>
            </div>
        </div> <!-- .form-group -->

        <?php wp_nonce_field( 'erp-leave-policy' ); ?>
        <input type="hidden" name="erp-action" value="hr-leave-policy-create">
        <input type="hidden" name="policy-id" value="">

        <input type="submit"
            value="<?php echo esc_attr('Save Changes', 'erp'); ?>"
            class="button button-primary">
    </form>
</div>