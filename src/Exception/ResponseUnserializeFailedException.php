<?php
/**
 * Exception class when a response failed to be unserialized.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\Exception;

/**
 * Class ResponseUnserializeFailedException
 *
 * @package IronBound\WP_API_Idempotence\Exception
 */
class ResponseUnserializeFailedException extends \UnexpectedValueException implements ResponseSerializationException {

	/**
	 * RequestHashFailedException constructor.
	 *
	 * @param string $message
	 */
	public function __construct( $message ) {

		$formatted = sprintf(
		/* translators: %1$s is the error message. */
			__( 'Failed to unserialize response: %1$s', 'wp-api-idempotence' ),
			$message
		);

		parent::__construct( $formatted, 0, null );
	}
}