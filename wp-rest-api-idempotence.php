<?php
/*
Plugin Name: WP REST API - Idempotence
Plugin URI: https://timothybjacobs.com/wp-api-idempotence/
Description: Allow API clients to specify an idempotency key for API requests.
Version: 1.0
Author: Timothy B Jacobs
Author URI: https://timothybjacobs.com
License: GPLv2
*/

if ( version_compare( PHP_VERSION, '5.4', '<' ) ) {

	/**
	 * Add notice specifying PHP Version 5.4+ is required.
	 *
	 * @since 1.0.0
	 */
	function ironbound_wp_rest_api_idempotence_php_version_notice() {
		?>
        <div class="notice notice-error">
            <p><?php _e( 'WP REST API - Idempotence requires PHP version 5.4 or greater.', 'wp-rest-api-idempotence' ) ?></p>
        </div>
		<?php
	}

	add_action( 'admin_notices', 'ironbound_wp_rest_api_idempotence_php_version_notice' );
} else {
	define( 'WP_API_IDEMPOTENCE_FILE', __FILE__ );

	/**
	 * Load the plugin on plugins loaded.
	 *
	 * @since 1.0.0
	 */
	function ironbound_wp_rest_api_idempotence_load_plugin() {
		require_once dirname( __FILE__ ) . '/load.php';
	}

	add_action( 'plugins_loaded', 'ironbound_wp_rest_api_idempotence_load_plugin' );
}