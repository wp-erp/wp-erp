<?php

use WeDevs\ERP\ERP_Errors;
use \WeDevs\ERP\HRM\Models\Leave_Policy;
use \WeDevs\ERP\HRM\Models\Financial_Year;

if ( isset( $_POST['_wpnonce'] ) && ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-settings-nonce' ) ) {
    die('Nonce failed.');
}

$fnames = isset( $_POST['fyear-name'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['fyear-name'] ) ) : [];
$starts = isset( $_POST['fyear-start'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['fyear-start'] ) ) : [];
$ends   = isset( $_POST['fyear-end'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['fyear-end'] ) ) : [];

$current_user_id = get_current_user_id();
$url = admin_url('?page=erp-settings&tab=erp-hr&section=financial');

$fin_years = array();

if ( isset( $_POST['erp-hr-fyears-setting'] ) ) {

    $errors = new ERP_Errors( 'leave_financial_years_create' );

    foreach ( $fnames as $key => $fname ) {
        if ( strpos($key, 'id-') !== false ) {
            // we have existing record

            $f_id = explode( 'id-', $key )[1]; // id-3 => 3

            $policy_exist = Leave_Policy::where('f_year', $f_id)->first();

            if ( $policy_exist ) {
                $errors->add( esc_html__(
                    sprintf('Existing financial year associated with policy won\'t be updated. e.g. %s', $fname)
                , 'erp') );

                continue;
            }

            // update an existing one
            Financial_Year::find($f_id)->update([
                'fy_name'    => $fname,
                'start_date' => erp_mysqldate_to_phptimestamp( $starts[$key] ),
                'end_date'   => erp_mysqldate_to_phptimestamp( $ends[$key] ),
                'description'=> 'Financial year for leave',
                'updated_by' => $current_user_id
            ]);

            continue;
        }

        // or create a new one
        Financial_Year::create([
            'fy_name'    => $fname,
            'start_date' => erp_mysqldate_to_phptimestamp( $starts[$key] ),
            'end_date'   => erp_mysqldate_to_phptimestamp( $ends[$key] ),
            'description'=> 'Financial year for leave',
            'created_by' => $current_user_id
        ]);
    }

    if ( $errors->has_error() ) {
        $errors->save();
        $url = add_query_arg( array( 'error' => 'leave_financial_years_create' ), $url );
    }

    wp_safe_redirect( $url );
    exit();
}

// show the erros
if ( isset( $_GET['error'] ) && $_GET['error'] != '' ) {
    $errors = new ERP_Errors( sanitize_text_field( wp_unslash( $_GET['error'] ) ) );
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
                            value="<?php echo erp_format_date( $year['start_date'] ) ?>"
                            type="text"
                            autocomplete="off" required>
                    </td>
                    <td>
                        <input
                            name="fyear-end[<?php echo 'id-' . $year['id']; ?>]"
                            class="fyear-end-date hr-fyear-date-field"
                            value="<?php echo erp_format_date( $year['end_date'] ) ?>"
                            type="text"
                            autocomplete="off" required>
                    </td>
                    <td></td>
                </tr>
                <?php endforeach; ?>

            <?php endif; ?>
        </tbody>
    </table>

    <input type="hidden" name="erp-hr-fyears-setting">

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
