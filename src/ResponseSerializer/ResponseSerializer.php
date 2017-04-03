<?php
/**
 * Response Serializer interface.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\ResponseSerializer;
use IronBound\WP_API_Idempotence\Exception\ResponseSerializeFailedException;
use IronBound\WP_API_Idempotence\Exception\ResponseUnserializeFailedException;

/**
 * Interface ResponseSerializer
 *
 * @package IronBound\WP_API_Idempotence\ResponseSerializer
 */
interface ResponseSerializer {

	/**
	 * Serialize a response to a string.
	 *
	 * This string must provide enough data to reconstruct the response object.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Response|\WP_Error $response
	 *
	 * @return string
	 *
	 * @throws ResponseSerializeFailedException
	 */
	public function serialize( $response );

	/**
	 * Unserialize a serialized response to its original state.
	 *
	 * @since 1.0.0
	 *
	 * @param string $serialized
	 *
	 * @return \WP_REST_Response|\WP_Error|null
	 *
	 * @throws ResponseUnserializeFailedException
	 */
	public function unserialize( $serialized );
}