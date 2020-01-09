<div class="erp-acct-ob-settings-wrap">
    <div class="erp-ac-multiple-ob-field">
        <div class="erp-ac-ob-fields">
            <?php if ( ! $rows ) { ?>
                <div class="row">
                    <?php
                    erp_html_form_input(
                        array(
                            'label' => __( 'Name', 'erp' ),
                            'name'  => 'ob_names[]',
                            'type'  => 'text',
                        )
                    );
                    ?>
                    <?php
                    erp_html_form_input(
                        array(
                            'label' => __( 'Start Date', 'erp' ),
                            'name'  => 'ob_starts[]',
                            'type'  => 'date',
                        )
                    );
                    ?>
                    <?php
                    erp_html_form_input(
                        array(
                            'label' => __( 'End Date', 'erp' ),
                            'name'  => 'ob_ends[]',
                            'type'  => 'date',
                        )
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
                            array(
                                'label' => __( 'Name', 'erp' ),
                                'name'  => 'ob_names[]',
                                'type'  => 'text',
                                'value' => $rows[ $i ]['name'],
                            )
                        );
                        ?>

                        <?php
                        erp_html_form_input(
                            array(
                                'label' => __( 'Start Date', 'erp' ),
                                'name'  => 'ob_starts[]',
                                'type'  => 'text',
                                'class' => 'erp-date-field',
                                'value' => $rows[ $i ]['start_date'],
                            )
                        );
                        ?>

                        <?php
                        erp_html_form_input(
                            array(
                                'label' => __( 'End Date', 'erp' ),
                                'name'  => 'ob_ends[]',
                                'type'  => 'text',
                                'class' => 'erp-date-field',
                                'value' => $rows[ $i ]['end_date'],
                            )
                        );
                        ?>
                        <span><i class="fa fa-times-circle erp-ac-ob-remove-field"></i></span>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>
    <br>
    <a href="#" class="button-secondary erp-ac-ob-add-more"><?php esc_attr_e( 'Add More', 'erp' ); ?></a>
</div>
