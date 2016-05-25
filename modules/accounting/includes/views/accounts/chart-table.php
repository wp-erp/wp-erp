<div class="account-chart">

    <h3><?php echo $title; ?></h3>

    <table class="table widefat striped ac-chart-table">
        <thead>
            <tr>
                <th class="col-code"><?php _e( 'Code', 'accounting' ); ?></th>
                <th class="col-name"><?php _e( 'Name', 'accounting' ); ?></th>
                <th class="col-type"><?php _e( 'Type', 'accounting' ); ?></th>
                <th class="col-transactions"><?php _e( 'Entries', 'accounting' ); ?></th>
                <th class="col-action"><?php _e( 'Actions', 'accounting' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php

            if ( $charts ) {

                $chart_details = admin_url( 'admin.php?page=erp-accounting-charts&action=view&id=' );

                foreach( $charts as $chart ) {
                    ?>
                    <tr>
                        <td class="col-code"><?php echo $chart->code; ?></td>
                        <td class="col-name">
                            <?php
                            if ( erp_ac_view_single_account() ) {
                                ?>
                                <a href="<?php echo erp_ac_get_account_url( $chart->id ); ?>"><?php echo esc_html( $chart->name ); ?></a>
                                <?php
                            } else {
                                echo esc_html( $chart->name );
                            }
                            ?>
                        </td>
                        <td class="col-type"><?php echo $chart->type_name; ?></td>
                        <td class="col-transactions">
                            <?php
                            if ( erp_ac_view_single_account() ) {
                                ?>
                                <a href="<?php echo erp_ac_get_account_url( $chart->id ); ?>"><?php echo intval( $chart->entries ); ?></a>
                                <?php
                            } else {
                                echo intval( $chart->entries );
                            }
                            ?>
                            
                        </td>
                        <td class="col-action">
                            <?php if ( $chart->system ) {
                                _e( 'System Account', 'accounting' );
                            } else {
                                if( erp_ac_edit_account() ) {
                                    ?>
                                    <a href="<?php echo $edit_url . $chart->id; ?>"><?php _e( 'Edit', 'accounting' ); ?></a>
                                    <?php
                                }

                                if ( erp_ac_delete_account() ) {
                                    ?>
                                    <a data-id="<?php echo intval( $chart->id ); ?>" class="erp-ac-remove-account" href="#"><?php _e( 'Delete', 'accounting' ); ?></a>
                                    <?php
                                }
                            } ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="5"><?php _e( 'No chart found!', 'accounting' ); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>