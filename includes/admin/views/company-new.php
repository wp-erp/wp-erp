<div class="wrap erp">
    <h2><?php _e( 'Company', 'wp-erp' ); ?> <a href="<?php echo admin_url( 'admin.php?page=erp-company&action=new' ); ?>" class="add-new-h2"><?php _e( 'New Company', 'wp-erp' ); ?></a></h2>

    <form action="" method="post" id="erp-new-company">
        <div class="erp-single-container">
            <div class="erp-area-left">
                <div id="titlediv" style="margin-bottom: 20px;">
                    <div id="titlewrap">
                        <label class="screen-reader-text" id="title-prompt-text" for="title"><?php _e( 'Enter company name here', 'wp-erp' ); ?></label>
                        <input type="text" name="company_name" size="30" value="" id="title" autocomplete="off">
                    </div>
                </div>

                <div class="postbox company-postbox">
                    <h3 class="hndle"><span><?php _e( 'Company Address', 'wp-erp' ); ?></span></h3>
                    <div class="inside">
                        <table class="form-table">
                            <tr>
                                <td><label for="address_1"><?php _e( 'Address Line 1 ', 'wp-erp' ); ?></label></td>
                                <td><input type="text" id="address_1" class="regular-text" name="address_1" value=""></td>
                            </tr>

                            <tr>
                                <td><label for="address_2"><?php _e( 'Address Line 2 ', 'wp-erp' ); ?></label></td>
                                <td><input type="text" id="address_2" class="regular-text" name="address_2" value=""></td>
                            </tr>

                            <tr>
                                <td><label for="city"><?php _e( 'City', 'wp-erp' ); ?></label></td>
                                <td><input type="text" id="city" class="regular-text" name="city" value=""></td>
                            </tr>

                            <tr>
                                <td><label for="erp-country"><?php _e( 'Country', 'wp-erp' ); ?></label> <span class="required">*</span></td>
                                <td>
                                    <?php $country = \WeDevs\ERP\Countries::instance(); ?>
                                    <select name="country" id="erp-country">
                                        <?php echo $country->country_dropdown(); ?>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td><label for="state"><?php _e( 'Province / State', 'wp-erp' ); ?></label></td>
                                <td>
                                    <select name="state" id="erp-state">
                                        <option value="-1"><?php _e( '- Select -', 'wp-erp' ); ?></option>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td><label for="zip"><?php _e( 'Postal / Zip Code', 'wp-erp' ); ?></label></td>
                                <td><input type="number" id="zip" class="regular-text" name="zip" value=""></td>
                            </tr>

                            <tr>
                                <td><label for="phone"><?php _e( 'Phone', 'wp-erp' ); ?></label></td>
                                <td><input type="text" id="phone" class="regular-text" name="phone" value=""></td>
                            </tr>

                            <tr>
                                <td><label for="fax"><?php _e( 'Fax', 'wp-erp' ); ?></label></td>
                                <td><input type="text" id="fax" class="regular-text" name="fax" value=""></td>
                            </tr>

                            <tr>
                                <td><label for="mobile"><?php _e( 'Mobile', 'wp-erp' ); ?></label></td>
                                <td><input type="text" id="mobile" class="regular-text" name="mobile" value=""></td>
                            </tr>

                            <tr>
                                <td><label for="website"><?php _e( 'Website', 'wp-erp' ); ?></label></td>
                                <td><input type="url" id="website" class="regular-text" name="website" value=""></td>
                            </tr>

                            <tr>
                                <td><label for="currency"><?php _e( 'Main Currency', 'wp-erp' ); ?></label></td>
                                <td>
                                    <select name="currency" id="currency">
                                        <?php echo erp_get_currencies_dropdown(); ?> ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div><!-- .inside -->
                </div><!-- .postbox -->
            </div><!-- .erp-area-left -->

            <div class="erp-area-right">
                <div class="postbox company-logo" id="postimagediv">
                    <h3 class="hndle"><span><?php _e( 'Company Logo', 'wp-erp' ); ?></span></h3>
                    <div class="inside">
                        <p class="hide-if-no-js">
                            <a href="<?php echo get_upload_iframe_src('image' ); ?>" id="set-company-thumbnail" class="thickbox"><?php _e( 'Upload company logo', 'wp-erp' ); ?></a>
                        </p>
                    </div>
                </div><!-- .postbox -->

                <div class="postbox company-postbox">
                    <h3 class="hndle"><span><?php _e( 'Actions', 'wp-erp' ); ?></span></h3>
                    <div class="inside">
                        <div class="submitbox" id="submitbox">
                            <div id="major-publishing-actions">

                                <div id="publishing-action">
                                    <?php wp_nonce_field( 'erp-new-company' ); ?>
                                    <input type="hidden" name="erp-action" value="create_new_company">
                                    <input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e( 'Create Company', 'wp-erp' ); ?>">
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