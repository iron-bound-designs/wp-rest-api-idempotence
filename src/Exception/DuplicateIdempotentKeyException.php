<?php
/**
 * Fires when a user provides an idempotency key that is used by a different request.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\Exception;

use IronBound\WP_API_Idempotence\IdempotentRequest;

/**
 * Class DuplicateIdempotentKeyException
 *
 * @package IronBound\WP_API_Idempotence\Exception
 */
class DuplicateIdempotentKeyException extends \InvalidArgumentException implements Exception {

	/** @var IdempotentRequest */
	private $request;

	/**
	 * @inheritDoc
	 */
	public function __construct( IdempotentRequest $request, $message = '' ) {
		parent::__construct( $message, 0, null );

		$this->request = $request;
	}

	/**
	 * Get the request used.
	 *
	 * @since 1.0.0
	 *
	 * @return IdempotentRequest
	 */
	public function get_request() {
		return $this->request;
	}
}
