<?php
$life_stages = erp_crm_get_life_stages_dropdown_raw();
$users       = erp_crm_get_crm_user();
?>
<form method="post" name="contact_from_user" id="contact_from_user">
    <div class="wrap">

        <h2><?php esc_attr_e( 'Import as Contact', 'erp' ); ?></h2>

        <table class="form-table">
            <tbody>
                <tr>
                    <th>
                        <label for="contact_owner"><?php esc_attr_e( 'Assign Contact Owner', 'erp' ); ?></label>
                    </th>
                    <td>
                        <select name="contact_owner" id="contact_owner" class="">
                            <option value=""><?php esc_attr_e( '&mdash; Select Owner &mdash;', 'erp' ); ?></option>
                            <?php
                            foreach ( $users as $user ) {
                                echo esc_html( '<option value="' . $user->ID . '">' . $user->display_name . ' &lt;' . $user->user_email . '&gt;' . '</option>' );
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th>
                        <label for="life_stage"><?php esc_attr_e( 'Life Stage', 'erp' ); ?></label>
                    </th>
                    <td>
                        <select name="life_stage" id="life_stage">
                        <?php
                        foreach ( $life_stages as $key => $value ) {
                            echo esc_html( '<option value="' . $key . '">' . $value . '</option>' );
                        }
                        ?>
                    </select>
                    </td>
                </tr>

            </tbody>
        </table>

        <?php wp_nonce_field( 'erp_create_contact_from_user' ); ?>
        <input type="hidden" name="action" value="process_crm_contact">
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Import Contacts', 'erp' ); ?>"></p>
    </div>
</form>
