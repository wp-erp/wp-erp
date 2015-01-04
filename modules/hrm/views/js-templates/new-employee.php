<ol>
    <li class="line name-container">
        <label for="full-name"><?php _e( 'Full Name', 'wp-erp' ); ?></label>

        <ol class="fields-inline">
            <li>
                <label for="first-name"><?php _e( 'First Name', 'wp-erp' ); ?> <span class="required">*</span></label>
                <input type="text" name="name[first_name]" class="" maxlength="30" id="first-name" value="">
            </li>
            <li>
                <label for="middle-name"><?php _e( 'Middle Name', 'wp-erp' ); ?></label>
                <input type="text" name="name[middle_name]" class="block default editable" maxlength="30" id="name_txtEmpMiddleName" >
            </li>
            <li>
                <div class="fieldDescription"><?php _e( 'Last Name', 'wp-erp' ); ?> <span class="required">*</span></div>
                <input value="Zahid" type="text" name="name[last_name]" class="block default editable" maxlength="30" title="Last Name" id="name_txtEmpLastName" disabled="disabled">
            </li>
        </ol>
    </li>
</ol>