<?php
/**
 * RequestHasher interface.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\RequestHasher;

use IronBound\WP_API_Idempotence\Exception\RequestHashFailedException;
use IronBound\WP_API_Idempotence\IdempotentRequest;

/**
 * Class RequestHasher
 *
 * @package IronBound\WP_API_Idempotence
 */
interface RequestHasher {

	/**
	 * Generate a unique hash for a given request.
	 *
	 * The hash will be unique to request objects with the same state.
	 *
	 * @since 1.0.0
	 *
	 * @param IdempotentRequest $request
	 *
	 * @return string
	 *
	 * @throws RequestHashFailedException
	 */
	public function hash( IdempotentRequest $request );
}