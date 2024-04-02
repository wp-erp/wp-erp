<div class="wrap erp erp-company-single">
    <h2><?php esc_html_e( 'Company Details', 'erp' ); ?></h2>

    <div class="metabox-holder company-accounts">
        <?php
        $company = new \WeDevs\ERP\Company();

        $company->get_locations();
        ?>
        <div class="postbox account">
            <div class="inside clearfix">
                <div class="logo-area">
                    <?php echo wp_kses_post( $company->get_logo() ); ?>
                </div><!-- .logo-area -->

                <div class="content-area">
                    <h2><?php echo esc_html( $company->name ); ?> <a href="<?php echo esc_url( $company->get_edit_url() ); ?>"><?php esc_html_e( 'Edit', 'erp' ); ?></a></h2>

                    <address class="address">
                        <?php echo wp_kses_post( $company->get_formatted_address() ); ?>
                    </address>
                </div><!-- .content-area -->
            </div><!-- .inside -->
        </div><!-- .account -->

        <div class="company-location-wrap">
            <h2>
                <?php esc_html_e( 'Locations', 'erp' ); ?>

                <a href="#" id="erp-company-new-location" class="add-new-h2 erp-add-new-location" data-title="<?php esc_attr_e( 'New Location', 'erp' ); ?>" data-id="<?php echo esc_attr( $company->id ); ?>"><?php esc_attr_e( 'Create New Location', 'erp' ); ?></a>
            </h2>

            <div id="company-locations">
                <div id="company-locations-inside">
                <?php
                $locations = $company->get_locations();
                $country   = \WeDevs\ERP\Countries::instance();

                if ( $locations ) {
                    foreach ( $locations as $num => $location ) {
                        ?>
                        <div class="company-location postbox">
                            <h3 class="hndle"><?php echo wp_kses_post( $location['name'] ); ?></h3>

                            <div class="inside">
                                <address class="address">
                                    <?php
                                    echo wp_kses_post( $country->get_formatted_address( [
                                        'address_1' => $location['address_1'],
                                        'address_2' => $location['address_2'],
                                        'city'      => $location['city'],
                                        'state'     => $location['state'],
                                        'postcode'  => $location['zip'],
                                        'country'   => $location['country'],
                                    ] ) ); ?>
                                </address>
                            </div><!-- .inside -->

                            <div class="actions">
                                <a href="#" class="edit-location" data-data='<?php echo esc_attr( wp_json_encode( $location ) ); ?>'><span class="dashicons dashicons-edit"></span></a>
                                <a href="#" class="remove-location" data-id="<?php echo esc_attr( $location['id'] ); ?>"><span class="dashicons dashicons-trash"></span></a>
                            </div>
                        </div>

                        <?php
                    }
                } else {
                    esc_html_e( 'No extra locations found!', 'erp' );
                }
                ?>
                </div><!-- #company-locations-inside -->
            </div><!-- #company-locations -->
        </div><!-- .company-location-wrap -->
    </div><!-- .metabox-holder -->

</div>
