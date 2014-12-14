<div class="wrap">
    <h2><?php _e( 'Add-ons', 'wp-erp' ); ?></h2>

    <?php
    $add_ons = get_transient( 'erp_addons' );

    if ( false === $add_ons ) {
        $response = wp_remote_get( 'http://wedevs.com/api/wpuf/addons.php', array('timeout' => 15) );
        $update = wp_remote_retrieve_body( $response );

        if ( is_wp_error( $response ) || $response['response']['code'] != 200 ) {
            return false;
        }

        set_transient( 'erp_addons', $update, 12 * HOUR_IN_SECONDS );
        $add_ons = $update;
    }

    $add_ons = json_decode( $add_ons );

    if ( count( $add_ons ) ) {
        foreach ($add_ons as $addon) {
            ?>

            <div class="erp-addon">
                <div class="erp-addon-thumb">
                    <a href="<?php echo $addon->url; ?>" target="_blank">
                        <img src="<?php echo $addon->thumbnail; ?>" alt="<?php echo esc_attr( $addon->title ); ?>" />
                    </a>
                </div>

                <div class="erp-detail">
                    <h3 class="title">
                        <a href="<?php echo $addon->url; ?>" target="_blank"><?php echo $addon->title; ?></a>
                    </h3>

                    <div class="text"><?php echo $addon->desc; ?></div>
                </div>

                <div class="erp-links">
                    <?php if ( class_exists( $addon->class ) ) { ?>
                        <a class="button button-disabled" href="<?php echo $addon->url; ?>" target="_blank">Installed</a>
                    <?php } else { ?>
                        <a class="button" href="<?php echo $addon->url; ?>" target="_blank">View Details</a>
                    <?php } ?>
                </div>
            </div>

            <?php
        }
    } else {
        echo '<div class="error"><p>Error fetching add-ons</p></div>';
    }
    ?>

    <style type="text/css">
        .erp-addon {
            width: 220px;
            float: left;
            margin: 10px;
            border: 1px solid #E6E6E6;
        }

        .erp-addon-thumb img {
            max-width: 220px;
            max-height: 140px;
        }

        .erp-detail {
            padding: 6px 10px 10px;
            min-height: 110px;
        }

        .erp-detail h3.title {
            margin: 5px 0 10px;
            padding: 0;
        }

        .erp-detail h3.title a {
            text-decoration: none;
            color: #111;
        }

        .erp-links {
            padding: 10px;
            background: #F5F5F5;
            border-top: 1px solid #E6E6E6;
        }

        a.button.disabled {
            background: #eee;
        }
    </style>

</div>