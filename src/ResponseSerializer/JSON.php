<?php
/**
 * Response Serializer that converts a response to a JSON string.
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
 * Class JSONResponseSerializer
 *
 * @package IronBound\WP_API_Idempotence\ResponseSerializer
 */
class JSON implements Filterable {

	/**
	 * @inheritDoc
	 */
	public function serialize( $response ) {

		if ( empty( $response ) ) {
			return '';
		}

		$response = rest_ensure_response( $response );

		if ( $response instanceof \WP_REST_Response ) {
			$data = array(
				'headers' => $response->get_headers(),
				'status'  => $response->get_status(),
				'body'    => $response->get_data(),
				'links'   => $response->get_links(),
			);
		} elseif ( is_wp_error( $response ) ) {
			$data = array(
				'errors'     => $response->errors,
				'error_data' => $response->error_data,
			);
		} else {
			throw new ResponseSerializeFailedException(
				__( 'Non-standard response object encountered.', 'wp-api-idempotence' ),
				$response
			);
		}

		$json = wp_json_encode( $data );

		if ( json_last_error() ) {
			throw new ResponseSerializeFailedException( json_last_error_msg(), $response );
		}

		return $json;
	}

	/**
	 * @inheritDoc
	 */
	public function unserialize( $serialized ) {

		if ( empty( $serialized ) ) {
			return null;
		}

		$data = json_decode( $serialized, true );

		if ( json_last_error() ) {
			throw new ResponseUnserializeFailedException( json_last_error_msg() );
		}

		if ( isset( $data['original'] ) ) {
			$data = json_decode( $data['original'], true );

			if ( json_last_error() ) {
				throw new ResponseUnserializeFailedException( json_last_error_msg() );
			}
		}

		if ( isset( $data['headers'], $data['status'], $data['body'], $data['links'] ) ) {
			$response = new \WP_REST_Response( $data['body'], $data['status'], $data['headers'] );
			$response->add_links( $data['links'] );
		} elseif ( isset( $data['errors'], $data['error_data'] ) ) {
			$response             = new \WP_Error();
			$response->errors     = $data['errors'];
			$response->error_data = $data['error_data'];
		} else {
			throw new ResponseUnserializeFailedException(
				__( 'Non-standard response object encountered.', 'wp-api-idempotence' )
			);
		}

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function add_filtered_data( $serialized, $response, array $data ) {
		$json = wp_json_encode( array(
			'original' => $serialized,
			'data'     => $data,
		) );

		if ( json_last_error() ) {
			throw new ResponseSerializeFailedException( json_last_error_msg(), $response );
		}

		return $json;
	}

	/**
	 * @inheritDoc
	 */
	public function get_filtered_data( $serialized ) {

		if ( empty( $serialized ) ) {
			return [];
		}

		$decoded = json_decode( $serialized, true );

		if ( json_last_error() ) {
			throw new ResponseUnserializeFailedException( json_last_error_msg() );
		}

		if ( ! isset( $decoded['original'] ) ) {
			return [];
		}

		return $decoded['data'];
	}
}