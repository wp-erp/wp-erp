<?php
$id = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
$value = '';

if ( $id ) {
    $employee = new \WeDevs\ERP\HRM\Employee( $id );
    $value = $employee->get_type();
}
?>

<div class="type-form-wrap">
    <div class="row">
        <?php
        erp_html_form_input( [
            'label'    => __( 'Date', 'erp' ),
            'name'     => 'date',
            'value'    => erp_current_datetime()->format('Y-m-d'),
            'required' => true,
            'class'    => 'erp-date-field',
        ] );
        ?>
    </div>

    <div class="row">
        <?php
        erp_html_form_input( [
            'label'       => __( 'Employment Type', 'erp' ),
            'name'        => 'type',
            'value'       => $value,
            'type'        => 'select',
            'custom_attr' => [ 'data-selected' => $value ],
            'options'     => [ 0 => __( '- Select -', 'erp' ) ] + erp_hr_get_employee_types(),
        ] );
        ?>
    </div>

    <div class="row">
        <?php
        erp_html_form_input( [
            'label'       => __( 'Comment', 'erp' ),
            'name'        => 'comment',
            'value'       => '',
            'placeholder' => __( 'Optional comment', 'erp' ),
            'type'        => 'textarea',
            'custom_attr' => [ 'rows' => 4, 'cols' => 25 ],
        ] );
        ?>
    </div>

    <?php wp_nonce_field( 'employee_update_employment' ); ?>
    <input type="hidden" name="action" id="type-action" value="erp-hr-emp-update-type">
    <input type="hidden" name="user_id" id="emp-id" value="{{ data.user_id }}">
</div>
