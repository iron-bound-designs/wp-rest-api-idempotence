<?php
/**
 * Config class.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence;

/**
 * Class Config
 *
 * @package IronBound\WP_API_Idempotence
 */
final class Config {

	const LOCATION_HEADER = 'header';
	const LOCATION_BODY = 'body';

	/** @var string */
	private $key_location;

	/** @var string */
	private $key_name;

	/** @var string[] */
	private $applicable_methods = [];

	/**
	 * Build a Config object from the stored settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings
	 *
	 * @return Config
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function from_settings( array $settings ) {

		$config = new Config();
		$config->set_settings( $settings );

		return $config;
	}

	/**
	 * Set multiple values from a settings array.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings
	 */
	public function set_settings( array $settings ) {

		$settings = wp_parse_args( $settings, [
			'key_location'       => self::LOCATION_HEADER,
			'key_name'           => 'WP-Idempotency-Key',
			'applicable_methods' => [ 'POST', 'PUT', 'PATCH' ],
		] );

		$this->set_key_location( $settings['key_location'] );
		$this->set_key_name( $settings['key_name'] );
		$this->set_applicable_methods( $settings['applicable_methods'] );
	}

	/**
	 * Get the location they idempotency key is stored in.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_key_location() {
		return $this->key_location;
	}

	/**
	 * Set the location the idempotency key is stored.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key_location
	 *
	 * @throws \InvalidArgumentException
	 */
	public function set_key_location( $key_location ) {

		if ( ! array_key_exists( $key_location, static::key_locations() ) ) {
			throw new \InvalidArgumentException( 'Non-supported idempotency key location.' );
		}

		$this->key_location = $key_location;
	}

	/**
	 * Get the idempotency key name.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_key_name() {
		return $this->key_name;
	}

	/**
	 * Set the idempotency key name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key_name
	 */
	public function set_key_name( $key_name ) {
		$this->key_name = $key_name;
	}

	/**
	 * Get the methods that idempotency keys are valid for.
	 *
	 * @since 1.0.0
	 *
	 * @return string[]
	 */
	public function get_applicable_methods() {
		return $this->applicable_methods;
	}

	/**
	 * Set the methods that idempotency keys are valid for.
	 *
	 * @since 1.0.0
	 *
	 * @param array $applicable_methods Upper-cased list of HTTP methods.
	 */
	public function set_applicable_methods( array $applicable_methods ) {
		$this->applicable_methods = $applicable_methods;
	}

	/**
	 * Get the locations they idempotency key can be stored in.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function key_locations() {
		return [
			self::LOCATION_HEADER => __( 'Request Header', 'wp-api-idempotence' ),
			self::LOCATION_BODY   => __( 'Request Body', 'wp-api-idempotence' ),
		];
	}
}