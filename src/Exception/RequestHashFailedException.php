<?php
/**
 * Exception class when a request failed to be hashed.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\Exception;

use IronBound\WP_API_Idempotence\IdempotentRequest;

/**
 * Class RequestHashFailedException
 *
 * @package IronBound\WP_API_Idempotence\Exception
 */
class RequestHashFailedException extends \UnexpectedValueException implements Exception {

	/** @var IdempotentRequest */
	private $request;

	/**
	 * RequestHashFailedException constructor.
	 *
	 * @param string            $message
	 * @param IdempotentRequest $request
	 */
	public function __construct( $message, IdempotentRequest $request ) {

		$formatted = sprintf(
		/* translators: %1$s is the request description. %2$s is the error message. */
			__( 'Failed to hash %1$s: %2$s', 'wp-api-idempotence' ),
			$request,
			$message
		);

		parent::__construct( $formatted, 0, null );

		$this->request = $request;
	}

	/**
	 * Get the request object that failed to be hashed.
	 *
	 * @since 1.0.0
	 *
	 * @return IdempotentRequest
	 */
	public function get_request() {
		return $this->request;
	}
}