<?php
/**
 * Exception class when a response failed to be serialized.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\Exception;

/**
 * Class ResponseSerializeFailedException
 *
 * @package IronBound\WP_API_Idempotence\Exception
 */
class ResponseSerializeFailedException extends \UnexpectedValueException implements ResponseSerializationException {

	/** @var \WP_REST_Response */
	private $response;

	/**
	 * RequestHashFailedException constructor.
	 *
	 * @param string            $message
	 * @param \WP_REST_Response $response
	 */
	public function __construct( $message, \WP_REST_Response $response ) {

		$formatted = sprintf(
		/* translators: %1$s is the response route. %2$s is the error message. */
			__( 'Failed to serialize response from %1$s: %2$s', 'wp-rest-api-idempotence' ),
			$response->get_matched_route(),
			$message
		);

		parent::__construct( $formatted, 0, null );

		$this->response = $response;
	}

	/**
	 * Get the response that failed to serialize.
	 *
	 * @since 1.0.0
	 *
	 * @return \WP_REST_Response
	 */
	public function get_response() {
		return $this->response;
	}
}