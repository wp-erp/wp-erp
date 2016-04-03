<?php
    $life_stages = erp_crm_get_life_stages_dropdown_raw();
    $users = get_users();
?>
<form method="post" name="contact_from_user" id="contact_from_user">
    <div class="wrap">
        <h2><?php _e( 'Create CRM Contact(s) from User(s)', 'erp' ); ?></h2>
        <br />
        <?php wp_nonce_field( 'erp_create_contact_from_user' ); ?>
        <label for="contact_owner">Assign Contact Owner:</label>
        <select name="contact_owner" id="contact_owner" class="">
            <?php
            foreach ( $users as $user ) {
                echo '<option value="' . $user->ID . '">' . $user->display_name . '</option>';
            }
            ?>
        </select>
        <br />
        <label for="life_stage">Select Life stage:</label>
        <select name="life_stage" id="life_stage">
            <?php
            foreach ( $life_stages as $key => $value ) {
                echo '<option value="' . $key . '">' . $value . '</option>';
            }
            ?>
        </select>
        <input type="hidden" name="action" value="process_crm_contact">
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Create Contact"></p>
    </div>
</form>