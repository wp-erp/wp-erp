<?php

// show the erros
if ( isset( $_GET['error'] ) && $_GET['error'] != '' ) {
    $errors = new \WeDevs\ERP\ERP_Errors( sanitize_text_field( wp_unslash( $_GET['error'] ) ) );
    echo $errors->display();
}
?>

<div class="erp-hr-financial-years">
    <table>
        <thead>
            <tr>
                <td>Name</td>
                <td>Start Date</td>
                <td>End Date</td>
                <td></td>
            </tr>
        </thead>

        <tbody>
            <?php if ( empty( $f_years ) ) : ?>
                <tr class="fyear-clone">
                    <td>
                        <input
                            name="fyear-name[]"
                            class="fyear-name"
                            type="text"
                            value=""
                            autocomplete="off" required>
                    </td>
                    <td>
                        <input
                            name="fyear-start[]"
                            class="fyear-start-date hr-fyear-date-field"
                            value=""
                            type="text"
                            autocomplete="off" required>
                    </td>
                    <td>
                        <input
                            name="fyear-end[]"
                            class="fyear-end-date hr-fyear-date-field"
                            value=""
                            type="text"
                            autocomplete="off" required>
                    </td>
                    <td></td>
                </tr>
            <?php else: ?>
                <?php foreach( $f_years as $year ) : ?>
                <tr class="fyear-clone">
                    <td>
                        <input
                            name="fyear-name[<?php echo 'id-' . $year['id']; ?>]"
                            class="fyear-name"
                            type="text"
                            value="<?php echo $year['fy_name'] ?>"
                            autocomplete="off" required>
                    </td>
                    <td>
                        <input
                            name="fyear-start[<?php echo 'id-' . $year['id']; ?>]"
                            class="fyear-start-date hr-fyear-date-field"
                            value="<?php echo erp_current_datetime()->setTimestamp( $year['start_date'] )->format('Y-m-d'); ?>"
                            type="text"
                            autocomplete="off" required>
                    </td>
                    <td>
                        <input
                            name="fyear-end[<?php echo 'id-' . $year['id']; ?>]"
                            class="fyear-end-date hr-fyear-date-field"
                            value="<?php echo erp_current_datetime()->setTimestamp( $year['end_date'] )->format('Y-m-d'); ?>"
                            type="text"
                            autocomplete="off" required>
                    </td>
                    <td></td>
                </tr>
                <?php endforeach; ?>

            <?php endif; ?>
        </tbody>
    </table>

    <input type="hidden" name="action" value="erp-hr-fyears-setting">

    <button type="button" class="button-secondary erp-fyear-add-more-btn">
        <?php esc_attr_e( 'Add More', 'erp' ); ?>
    </button>
</div>

<script>
    // Clone
    jQuery('.erp-fyear-add-more-btn').on('click', function(e) {
        var fyear_first = jQuery('.fyear-clone:first');

        fyear_first.find('.fyear-start-date').datepicker('destroy').removeAttr('id');
        fyear_first.find('.fyear-end-date').datepicker('destroy').removeAttr('id');

        var clonedRow = fyear_first.clone();

        clonedRow.find('.fyear-name').attr('name', 'fyear-name[]').val('');
        clonedRow.find('.fyear-start-date').attr('name', 'fyear-start[]').val('');
        clonedRow.find('.fyear-end-date').attr('name', 'fyear-end[]').val('');
        clonedRow.find('td').eq(3).append('<i class="fa fa-times-circle erp-settings-fyear-remove"></i>');

        jQuery('tbody').append(clonedRow);

        e.preventDefault();
    });

    // Re-initiate datepicker every time
    jQuery(document).on('focus', '.hr-fyear-date-field', function() {
        jQuery(this)
            .datepicker({
                dateFormat : 'yy-mm-dd',
                changeMonth: true,
                changeYear : true,
                yearRange  : '-10:+5'
            });
    });

    // Copied from accounting financial years
    jQuery(document).on('change', '.hr-fyear-date-field', function(e) {
        e.preventDefault();

        var vals = [];

        jQuery(this).each(function() {
            vals.push(jQuery(this).val());
        });

        for ( let i = 2; i < vals.length; i += 2 ) {
            if ( ( Date.parse( vals[i]) >= Date.parse( vals[i-2] ) ) && ( Date.parse( vals[i] ) <= Date.parse( vals[i-1] ) ) ) {
                alert( wpErpHr.fin_overlap_msg );
                $(this).val('');
            }

            if ( Date.parse( vals[i+1] ) < Date.parse( vals[i]) ) {
                alert( wpErpHr.fin_val_compare_msg );
                $(this).val('');
            }
        }
    });

    // Remove row
    jQuery(document).on('click', '.erp-settings-fyear-remove', function(e) {
        jQuery(this).parent().parent().remove();
    });
</script>

<style>
    .erp-hr-financial-years thead td {
        font-weight: bold;
    }

    .erp-fyear-add-more-btn {
        margin-left: 4px !important;
    }

    .erp-settings-fyear-remove {
        cursor: pointer;
    }
</style>
