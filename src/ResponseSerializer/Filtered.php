<?php
/**
 * A ResponseSerializer decorator that allows for filters to modify the serialization process.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\ResponseSerializer;

/**
 * Class Filtered
 *
 * @package IronBound\WP_API_Idempotence\ResponseSerializer
 */
class Filtered implements ResponseSerializer {

	/** @var Filterable */
	private $filterable;

	/**
	 * Filtered constructor.
	 *
	 * @param Filterable $filterable
	 */
	public function __construct( Filterable $filterable ) { $this->filterable = $filterable; }

	/**
	 * @inheritDoc
	 */
	public function serialize( $response ) {
		$serialized = $this->filterable->serialize( $response );

		/**
		 * Filter any additional response data that should be included in the serialized form.
		 *
		 * @since 1.0.0
		 *
		 * @param array $data
		 * @param \WP_REST_Response|\WP_Error
		 */
		$data = apply_filters( 'wp_api_idempotence_serialized_response_data', [], $response );

		if ( $data ) {
			$serialized = $this->filterable->add_filtered_data( $serialized, $response, $data );
		}

		return $serialized;
	}

	/**
	 * @inheritDoc
	 */
	public function unserialize( $serialized ) {
		$response = $this->filterable->unserialize( $serialized );

		if ( ! $response ) {
			return $response;
		}

		$data = $this->filterable->get_filtered_data( $serialized );

		if ( $data ) {
			/**
			 * Fires when additional data should be attached to the unserialized response object.
			 *
			 * @since 1.0.0
			 *
			 * @param \WP_REST_Response|\WP_Error $response
			 * @param array                       $data
			 */
			do_action( 'wp_api_idempotence_attach_serialized_response_data', $response, $data );
		}

		return $response;
	}
}