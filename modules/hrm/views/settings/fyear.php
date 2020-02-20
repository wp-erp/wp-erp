<?php
use \WeDevs\ERP\HRM\Models\Financial_Year;

if ( isset( $_POST['_wpnonce'] ) && ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] )) ) {
    // die('with pain');
}

$fnames = isset( $_POST['fyear-name'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['fyear-name'] ) ) : [];
$starts = isset( $_POST['fyear-start'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['fyear-start'] ) ) : [];
$ends   = isset( $_POST['fyear-end'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['fyear-end'] ) ) : [];

$created_by = get_current_user_id();

$fin_years = array();

foreach ( $fnames as $key => $fname ) {
    $fin_years[] = array(
        'fy_name'    => $fname,
        'start_date' => erp_mysqldate_to_phptimestamp( $starts[$key] ),
        'end_date'   => erp_mysqldate_to_phptimestamp( $ends[$key] ),
        'created_by' => $created_by
    );
}

if ( isset( $_POST['erp-hr-fyears-setting'] ) ) {
    Financial_Year::query()->truncate();
    Financial_Year::insert( $fin_years );
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
            <?php foreach( $f_years as $year ) : ?>
            <tr class="fyear-clone">
                <td>
                    <input
                        name="fyear-name[]"
                        class="fyear-name"
                        type="text"
                        value="<?php echo $year['fy_name'] ?>"
                        autocomplete="off">
                </td>
                <td>
                    <input
                        name="fyear-start[]"
                        id="fyear-start1"
                        class="fyear-start-date erp-date-field"
                        value="<?php echo $year['start_date'] ?>"
                        type="text">
                </td>
                <td>
                    <input
                        name="fyear-end[]"
                        id="fyear-end1"
                        class="fyear-end-date erp-date-field"
                        value="<?php echo $year['end_date'] ?>"
                        type="text">
                </td>
                <td>
                    <i class="fa fa-times-circle erp-settings-fyear-remove"></i>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <input type="hidden" name="erp-hr-fyears-setting">

    <button type="button" class="button-secondary erp-fyear-add-more-btn">
        <?php esc_attr_e( 'Add More', 'erp' ); ?>
    </button>
</div>

<script>
    function getDateFieldId( period ) {
        var $div = jQuery('.fyear-clone:last input[id^="fyear-'+period+'"]');
        var num = parseInt( $div.prop('id').match(/\d+/g), 10 ) + 1;

        return 'fyear-' + period + num;
    }

    // Duplicate
    jQuery('.erp-fyear-add-more-btn').on('click', function(e) {
        var clonedRow = jQuery('.fyear-clone:first').clone();

        clonedRow.find('.fyear-name').val('');
        clonedRow.find('.fyear-start-date').attr('id', getDateFieldId('start'));
        clonedRow.find('.fyear-end-date').attr('id', getDateFieldId('end'));
        jQuery('tbody').append(clonedRow);

        e.preventDefault();
    });

    jQuery(document).on('focus', ".erp-date-field", function() {
        jQuery(this).removeClass('hasDatepicker')
        jQuery(this).datepicker();
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
