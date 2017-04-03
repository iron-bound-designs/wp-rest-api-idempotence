<?php
/**
 * Factory for creating IdempotentRequest objects.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence;

/**
 * Class IdempotentRequestFactory
 *
 * @package IronBound\WP_API_Idempotence
 */
class IdempotentRequestFactory {

	/**
	 * Make an idempotent request object.
	 *
	 * @since 1.0.0
	 *
	 * @param string           $idempotency_key
	 * @param \WP_REST_Request $request
	 * @param \WP_User|null    $user
	 *
	 * @return IdempotentRequest
	 */
	public function make( $idempotency_key, \WP_REST_Request $request, \WP_User $user = null ) {
		return new IdempotentRequest( $idempotency_key, $request, $user );
	}
}