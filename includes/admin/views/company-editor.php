<div class="wrap erp erp-company-single">
    <h2><?php esc_html_e( 'Company', 'erp' ); ?></h2>

    <?php if ( isset( $_GET['msg'] ) && $_GET['msg'] == 'updated' ) { ?>
        <div class="updated">
            <p><?php esc_html_e( 'Company information has been updated!', 'erp' ); ?></p>
        </div>
    <?php } else if ( isset( $_GET['msg'] ) && $_GET['msg'] == 'error' ) { ?>
        <?php
            if ( ! empty( $_GET['error-company'] ) ) {
                $errors[] = esc_html__( 'Company name is required', 'erp' );
            }

            if ( ! empty( $_GET['error-country'] ) ) {
                $errors[] = esc_html__( 'Country is required', 'erp' );
            }

            foreach ( $errors as $error ) {
                printf( '<div class="error"><p>%s</p></div>', esc_html( $error ) );
            }
        ?>
    <?php } ?>

    <?php $country = \WeDevs\ERP\Countries::instance(); ?>

    <form action="" method="post" id="erp-new-company">
        <div class="erp-single-container">
            <div class="erp-area-left">
                <div id="titlediv" style="margin-bottom: 20px;">
                    <div id="titlewrap">
                        <label class="screen-reader-text" id="title-prompt-text" for="title"><?php esc_html_e( 'Enter company name here', 'erp' ); ?></label>
                        <input type="text" name="name" size="30" value="<?php echo esc_attr( $company->name ); ?>" id="title" autocomplete="off">
                    </div>
                </div>

                <div class="postbox company-postbox">
                    <h3 class="hndle"><span><?php esc_html_e( 'Company Information', 'erp' ); ?></span></h3>
                    <div class="inside">

                        <table class="form-table">
                            <tr>
                                <td><label for="address_1"><?php esc_html_e( 'Address Line 1 ', 'erp' ); ?></label></td>
                                <td>
                                    <?php erp_html_form_input(array(
                                        'name'  => 'address[address_1]',
                                        'value' => $company->address['address_1'],
                                        'class' => 'regular-text'
                                    )); ?>
                                </td>
                            </tr>

                            <tr>
                                <td><label for="address_2"><?php esc_html_e( 'Address Line 2 ', 'erp' ); ?></label></td>
                                <td>
                                    <?php erp_html_form_input(array(
                                        'name'  => 'address[address_2]',
                                        'value' => $company->address['address_2'],
                                        'class' => 'regular-text'
                                    )); ?>
                                </td>
                            </tr>

                            <tr>
                                <td><label for="city"><?php esc_html_e( 'City', 'erp' ); ?></label></td>
                                <td>
                                    <?php erp_html_form_input(array(
                                        'name'  => 'address[city]',
                                        'value' => $company->address['city'],
                                        'class' => 'regular-text'
                                    )); ?>
                                </td>
                            </tr>

                             <tr>
                                <td><label for="erp-country"><?php esc_html_e( 'Country', 'erp' ); ?></label> <span class="required">*</span></td>
                                <td>
                                    <select name="address[country]" id="erp-country" data-parent="table" class="erp-country-select select2" required="required">
                                        <?php echo wp_kses( $country->country_dropdown( $company->address['country'] ), array(
                                            'option' => array(
                                                'value' => array(),
                                                'selected' => array()
                                            ),
                                        ) ); ?>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td><label for="state"><?php esc_html_e( 'Province / State', 'erp' ); ?></label></td>
                                <td class="state-field">
                                    <select name="address[state]" id="erp-state" class="erp-state-select">
                                        <?php
                                        if ( $company->address['country'] ) {
                                            $states = $country->get_states( $company->address['country'] );
                                            echo wp_kses( erp_html_generate_dropdown( $states, $company->address['state'] ), array(
                                                'option' => array(
                                                    'value' => array(),
                                                    'selected' => array()
                                                ),
                                            ) );
                                        } else {
                                            ?>
                                            <option value="-1"><?php esc_html_e( '- Select -', 'erp' ); ?></option>
                                        <?php } ?>

                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td><label for="zip"><?php esc_html_e( 'Postal / Zip Code', 'erp' ); ?></label></td>
                                <td>
                                    <?php erp_html_form_input(array(
                                        'name'  => 'address[zip]',
                                        'value' => ( isset( $company->address['zip'] ) ? $company->address['zip'] : '' ),
                                        'class' => 'regular-text'
                                    )); ?>
                                </td>
                            </tr>

                            <tr>
                                <td><label for="phone"><?php esc_html_e( 'Phone', 'erp' ); ?></label></td>
                                <td>
                                    <?php erp_html_form_input(array(
                                        'name'  => 'phone',
                                        'value' => $company->phone,
                                        'class' => 'regular-text'
                                    )); ?>
                                </td>
                            </tr>

                            <tr>
                                <td><label for="fax"><?php esc_html_e( 'Fax', 'erp' ); ?></label></td>
                                <td>
                                    <?php erp_html_form_input(array(
                                        'name'  => 'fax',
                                        'value' => $company->fax,
                                        'class' => 'regular-text'
                                    )); ?>
                                </td>
                            </tr>

                            <tr>
                                <td><label for="mobile"><?php esc_html_e( 'Mobile', 'erp' ); ?></label></td>
                                <td>
                                    <?php erp_html_form_input(array(
                                        'name'  => 'mobile',
                                        'value' => $company->mobile,
                                        'class' => 'regular-text'
                                    )); ?>
                                </td>
                            </tr>

                            <tr>
                                <td><label for="website"><?php esc_html_e( 'Website', 'erp' ); ?></label></td>
                                <td>
                                    <?php erp_html_form_input(array(
                                        'name'  => 'website',
                                        'type'  => 'url',
                                        'value' => $company->website,
                                        'class' => 'regular-text'
                                    )); ?>
                                </td>
                            </tr>
                            <tr>
                                <td scope="row">
                                    <label for="business_type"><?php esc_html_e( 'What sort of business do you do?', 'erp' ); ?></label>
                                </td>
                                <td>
                                    <?php erp_html_form_input([
                                        'name'    => 'business_type',
                                        'type'    => 'select',
                                        'value'   => $company->business_type,
                                        'options' => [
                                            ''                 => '-- select --',
                                            'Freelance'        => 'Freelance',
                                            'FreelanceDev'     => 'Freelance (Developer)',
                                            'FreelanceDes'     => 'Freelance (Design)',
                                            'SmallBLocal'      => 'Small Business: Local Service (e.g. Hairdresser)',
                                            'SmallBWeb'        => 'Small Business: Web Business',
                                            'SmallBOther'      => 'Small Business (Other)',
                                            'ecommerceWoo'     => 'eCommerce (WooCommerce)',
                                            'ecommerceShopify' => 'eCommerce (Shopify)',
                                            'ecommerceOther'   => 'eCommerce (Other)',
                                            'Other'            => 'Other'
                                        ]
                                    ]); ?>
                                </td>
                            </tr>
                        </table>
                    </div><!-- .inside -->
                </div><!-- .postbox -->
            </div><!-- .erp-area-left -->

            <div class="erp-area-right">
                <div class="postbox company-logo" id="postimagediv">
                    <h3 class="hndle"><span><?php esc_html_e( 'Company Logo', 'erp' ); ?></span></h3>
                    <div class="inside">

                        <?php echo wp_kses_post( $company->get_logo() ); ?>

                        <?php if ( $company->has_logo() ) { ?>

                            <p class="hide-if-no-js">
                                <input type="hidden" name="company_logo_id" value="<?php echo esc_attr( $company->logo ); ?>">
                                <a href="#" class="remove-logo"><?php esc_html_e( 'Remove company logo', 'erp' ); ?></a>
                            </p>

                        <?php } else { ?>

                            <p class="hide-if-no-js">
                                <a href="<?php echo esc_url( get_upload_iframe_src('image' ) ); ?>" id="set-company-thumbnail" class="thickbox"><?php esc_html_e( 'Upload company logo', 'erp' ); ?></a>
                            </p>

                        <?php } ?>
                    </div>
                </div><!-- .postbox -->

                <div class="postbox company-postbox">
                    <h3 class="hndle"><span><?php esc_html_e( 'Actions', 'erp' ); ?></span></h3>
                    <div class="inside">
                        <div class="submitbox" id="submitbox">
                            <div id="major-publishing-actions">

                                <div id="publishing-action">

                                    <?php wp_nonce_field( 'erp-new-company' ); ?>
                                    <input type="hidden" name="erp-action" value="create_new_company">
                                    <input type="hidden" name="company_id" value="<?php echo esc_attr( $company->id ); ?>">
                                    <input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php echo esc_html__( 'Update Company', 'erp' ); ?>">
                                </div>

                                <div class="clear"></div>
                            </div>
                        </div>
                    </div>
                </div><!-- .postbox -->
            </div><!-- .leads-right -->
        </div><!-- .erp-single-container -->
    </form>
</div>
