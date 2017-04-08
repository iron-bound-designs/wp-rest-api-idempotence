<form method="POST" id="settings-form">

    <div class="field">
        <label for="key_name" class="label"><?php _e( 'Idempotency Key Field Name', 'wp-api-idempotence' ); ?></label>
        <input type="text" name="key_name" id="key_name" required
               prettyname="<?php esc_attr_e( 'Field Name', 'wp-api-idempotence' ); ?>">
    </div>

    <div class="field">
        <label for="key_name" class="label"><?php _e( 'Idempotency Key Location', 'wp-api-idempotence' ); ?></label>
        <select name="key_location" required prettyname="<?php esc_attr_e( 'Key Location', 'wp-api-idempotence' ); ?>">
            <options source="key_locations"></options>
        </select>
    </div>

    <div class="field">
        <span class="label"><?php _e( 'Applicable HTTP Methods', 'wp-api-idempotence' ); ?></span>
        <!--suppress HtmlFormInputWithoutLabel -->
        <input type="multicheckbox" name="applicable_methods" source="applicable_methods" required
               prettyname="<?php esc_attr_e( 'Applicable HTTP Methods', 'wp-api-idempotence' ); ?>">
    </div>

    <div class="field">
        <label for="allow_logged_out_users" class="label"><?php _e( 'Allow Logged-out Idempotent Requests', 'wp-api-idempotence' ); ?></label>
        <input type="checkbox" name="allow_logged_out_users" id="allow_logged_out_users" value="1">
    </div>

	<?php submit_button( __( 'Save', 'wp-api-idempotence' ) ); ?>
    <input type="nonce" nonce_action="wp_api_idempotence_settings" name="nonce">
</form>