<div class="erp-acct-ob-settings-wrap">
    <div class="erp-ac-multiple-ob-field">
        <div class="erp-ac-ob-fields">
            <?php if ( ! $rows ) { ?>
                <div class="row">
                    <?php
                    erp_html_form_input(
                        [
                            'label' => __( 'Name', 'erp' ),
                            'name'  => 'ob_names[]',
                            'type'  => 'text',
                        ]
                    );
                    ?>
                    <?php
                    erp_html_form_input(
                        [
                            'label' => __( 'Start Date', 'erp' ),
                            'name'  => 'ob_starts[]',
                            'type'  => 'date',
                        ]
                    );
                    ?>
                    <?php
                    erp_html_form_input(
                        [
                            'label' => __( 'End Date', 'erp' ),
                            'name'  => 'ob_ends[]',
                            'type'  => 'date',
                        ]
                    );
                    ?>
                    <span><i class="fa fa-times-circle erp-ac-ob-remove-field"></i></span>
                </div>
                <?php
            } else {
                for ( $i = 0; $i < count( $rows ); $i ++ ) {
                    ?>
                    <div class="row">
                        <?php
                        erp_html_form_input(
                            [
                                'label' => __( 'Name', 'erp' ),
                                'name'  => 'ob_names['.$i.']',
                                'data-key' => $i,
                                'type'  => 'text',
                                'value' => $rows[ $i ]['name'],
                            ]
                        ); ?>

                        <?php
                        erp_html_form_input(
                            [
                                'label' => __( 'Start Date', 'erp' ),
                                'name'  => 'ob_starts['.$i.']',
                                'data-key' => $i,
                                'type'  => 'text',
                                'class' => 'erp-date-field',
                                'value' => $rows[ $i ]['start_date'],
                            ]
                        ); ?>

                        <?php
                        erp_html_form_input(
                            [
                                'label' => __( 'End Date', 'erp' ),
                                'name'  => 'ob_ends['.$i.']',
                                'data-key' => $i,
                                'type'  => 'text',
                                'class' => 'erp-date-field',
                                'value' => $rows[ $i ]['end_date'],
                            ]
                        ); ?>
                        <span><i class="fa fa-times-circle erp-ac-ob-remove-field"></i></span>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>
    <br>
    <input type="hidden" name="erp-ac-ob-fyears-add" value="">
    <a href="#" class="button-secondary erp-ac-ob-add-more"><?php esc_attr_e( 'Add More', 'erp' ); ?></a>
</div>
<script>
    // Re
    // -initiate datepicker every time
    (function($) {

        // Re-initiate datepicker every time
        $(document).on('focus', '.erp-date-field', function() {
            $(this)
                .datepicker({
                    dateFormat : 'yy-mm-dd',
                    changeMonth: true,
                    changeYear : true,
                    yearRange  : '-10:+5'
                });
        });

    })( jQuery );
</script>

<style>
    .erp-date-field{
        margin-bottom: 10px
    }
</style>
