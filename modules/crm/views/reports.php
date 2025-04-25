<div class="wrap">
    <h2><?php esc_html_e( 'Reports', 'erp' ); ?></h2>

    <div id="dashboard-widgets-wrap">

        <div id="dashboard-widgets" class="metabox-holder">

        <?php
            $reports  = erp_crm_get_reports();
            $sections = count( $reports );

            if ( $sections ) {
                $mid_point = (int) floor( $sections / 2 );
                $left_column  = array_slice( $reports, 0, $mid_point );
                $right_column = array_slice( $reports, $mid_point );
            }
        ?>

        <div class="postbox-container">
            <div class="meta-box-sortables">

            <?php
            foreach ( $left_column as $key => $report ) {
                ?>
                <div class="postbox">
                    <h2 class="hndle"><span><?php echo esc_html( $report['title'] ); ?></span></h2>
                    <div class="inside">
                        <p><?php echo esc_html( $report['description'] ); ?></p>
                        <p><a class="button button-primary" href="admin.php?page=erp-crm&section=reports&type=<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'View Report', 'erp' ); ?></a></p>
                    </div>
                </div><!-- .postbox -->
            <?php
            }
            ?>

            </div><!-- .meta-box-sortables -->
        </div><!-- .postbox-container -->

        <div class="postbox-container">
            <div class="meta-box-sortables">

            <?php
            foreach ( $right_column as $key => $report ) {
                if ( empty( $report['title'] ) ) {
                    return;
                } ?>
                <div class="postbox">
                    <h2 class="hndle"><span><?php echo esc_html( $report['title'] ); ?></span></h2>
                    <div class="inside">
                        <p><?php echo esc_html( $report['description'] ); ?></p>
                        <p><a class="button button-primary" href="admin.php?page=erp-crm&section=reports&type=<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'View Report', 'erp' ); ?></a></p>
                    </div>
                </div><!-- .postbox -->
            <?php
            }
            ?>

            </div><!-- .meta-box-sortables -->
        </div><!-- .postbox-container -->

        </div><!-- .metabox-holder -->
    </div><!-- .dashboar-widget-wrap -->

</div>
