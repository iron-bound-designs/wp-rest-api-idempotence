<?php
/**
 * Nonce field for formidable.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\Helpers;

use Gregwar\Formidable\Fields\Field;

/**
 * Class NonceField
 *
 * @package IronBound\WP_API_Idempotence\Helpers
 */
class NonceField extends Field {

	protected $required = true;
	protected $type = 'nonce';

	protected $nonce_action;

	/**
	 * @inheritDoc
	 */
	public function push( $name, $value = null ) {

		switch ( $name ) {
			case 'action':
				$this->nonce_action         = $value;
				$this->attributes['action'] = $value;
				break;
		}

		parent::push( $name, $value );
	}

	public function __sleep() {
		return array_merge( parent::__sleep(), array(
			'nonce_action'
		) );
	}

	/**
	 * @inheritDoc
	 */
	public function check() {

		$value = $this->value;

		if ( ! wp_verify_nonce( $value, $this->nonce_action ) ) {
			return __( 'Request Expired. Please try again.', 'wp-rest-api-idempotence' );
		}
	}

	public function getHtml() {
		return wp_nonce_field( $this->nonce_action, $this->getName(), false, false );
	}
}
