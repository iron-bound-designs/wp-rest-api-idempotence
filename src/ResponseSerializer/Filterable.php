<?php
/**
 * Response Serializers that can have their output filtered.
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
 * Interface Filterable
 *
 * @package IronBound\WP_API_Idempotence\ResponseSerializer
 */
interface Filterable extends ResponseSerializer {

	/**
	 * Add additional data to the serialized form of the response.
	 *
	 * @since 1.0.0
	 *
	 * @param string                      $serialized Serialized response.
	 * @param \WP_REST_Response|\WP_Error $response
	 * @param array                       $data       Map of additional data to be attached to the serialized version.
	 *
	 * @return string Serialized response with additional data encoded.
	 *
	 * @throws ResponseSerializeFailedException
	 */
	public function add_filtered_data( $serialized, $response, array $data );

	/**
	 * Get any additional data that was serialized with the response.
	 *
	 * @since 1.0.0
	 *
	 * @param string $serialized
	 *
	 * @return array Decoded additional data.
	 *
	 * @throws ResponseUnserializeFailedException
	 */
	public function get_filtered_data( $serialized );
}