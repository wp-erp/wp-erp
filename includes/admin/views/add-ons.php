<div class="wrap">
    <h1><?php esc_html_e( 'Add-ons', 'erp' ); ?> <a href="http://wperp.com/downloads/" target="_blank" class="page-title-action"><?php esc_html_e( 'View all Add-ons', 'erp' ); ?></a></h1>

    <?php
    $add_ons = get_transient( 'wperp_addons' );

    if ( false === $add_ons ) {
        $help_url  = 'https://api.bitbucket.org/2.0/snippets/wedevs/nrq8z/files/addons.json';
        $response  = wp_remote_get( $help_url, array('timeout' => 15) );
        $add_ons = wp_remote_retrieve_body( $response );

        if ( is_wp_error( $response ) || $response['response']['code'] != 200 ) {
            $add_ons = '[]';
        }

        set_transient( 'wperp_addons', $add_ons, 12 * HOUR_IN_SECONDS );
    }

    $add_ons = json_decode( $add_ons );

    if ( count( $add_ons ) ) {
        foreach ($add_ons as $addon) {
            $addon_url = $addon->url . '?utm_source=wp-admin&utm_medium=link&utm_campaign=addons-page';
            ?>

            <div class="erp-addon">
                <div class="erp-addon-thumb">
                    <a href="<?php echo esc_url( $addon_url ); ?>" target="_blank">
                        <img src="<?php echo esc_url( $addon->thumb ); ?>" alt="<?php echo esc_attr( $addon->title ); ?>" />
                    </a>
                </div>

                <div class="erp-detail">
                    <h3 class="title">
                        <a href="<?php echo esc_url( $addon->url ); ?>" target="_blank"><?php echo esc_html( $addon->title ); ?></a>
                    </h3>

                    <div class="text"><?php echo wp_kses_post( $addon->desc ); ?></div>
                </div>

                <div class="erp-links">
                    <?php if ( class_exists( $addon->class ) ) { ?>
                        <a class="button button-disabled" href="<?php echo esc_url( $addon_url ); ?>" target="_blank"><?php esc_html_e( 'Installed', 'erp' ); ?></a>
                    <?php } else { ?>
                        <a class="button-primary" href="<?php echo esc_url( $addon_url ); ?>" target="_blank"><?php esc_html_e( 'View Details', 'erp' ); ?></a>
                    <?php } ?>
                </div>
            </div>

            <?php
        }
    } else {
        esc_html_e( '<div class="error"><p>Error fetching add-ons</p></div>', 'erp' );
    }
    ?>
</div>

<style type="text/css">
    .erp-addon {
        width: 240px;
        float: left;
        margin: 10px 25px 20px 0;
        border: 1px solid #E6E6E6;
        background-color: #fff;
    }

    .erp-addon-thumb img {
        width: 100%;
        height: auto;
    }

    .erp-detail {
        padding: 6px 10px 10px;
        min-height: 125px;
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
        text-align: center;
    }

    a.button.disabled {
        background: #eee;
    }
</style>
