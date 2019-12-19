<div class="wrap">
    <h1><?php esc_html_e( 'Reports', 'erp' ); ?></h1>

    <div id="dashboard-widgets-wrap">

        <div id="dashboard-widgets" class="metabox-holder">

        <?php
            $reports  = erp_hr_get_reports();
            $sections = count( $reports );

            if ( $sections ) {
                $left_column = array_slice( $reports, 0, $sections / 2 );
                $right_column = array_slice( $reports, $sections / 2 );
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
                        <p><a class="button button-primary" href="admin.php?page=erp-hr&section=report&type=<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'View Report', 'erp' ); ?></a></p>
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
            ?>
                <div class="postbox">
                    <h2 class="hndle"><span><?php echo esc_html( $report['title'] ); ?></span></h2>
                    <div class="inside">
                        <p><?php echo esc_html( $report['description'] ); ?></p>
                        <p><a class="button button-primary" href="admin.php?page=erp-hr&section=report&type=<?php echo esc_html( $key ); ?>"><?php esc_html_e( 'View Report', 'erp' ); ?></a></p>
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
