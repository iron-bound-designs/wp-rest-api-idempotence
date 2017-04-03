<?php
/**
 * Service class responsible for generating a unique hash for a `WP_REST_Request`.
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
 * Class Simple
 *
 * @package IronBound\WP_API_Idempotence\RequestHasher
 */
class Simple implements RequestHasher {

	/** @var string[] */
	private $header_whitelist;

	/**
	 * RequestHasher constructor.
	 *
	 * @param \string[] $header_whitelist
	 */
	public function __construct( array $header_whitelist = [] ) {
		$this->header_whitelist = $header_whitelist;

		if ( func_num_args() === 0 ) {
			$this->header_whitelist = [
				'Content-Type',
				'Cookie',
				'If-Modified-Since',
				'If-Unmodified-Since',
				'If-Range',
				'If-None-Match',
				'Range',
				'Referer'
			];
		}
	}

	/** @inheritdoc */
	public function hash( IdempotentRequest $request ) {

		$api_request = $request->get_request();

		$headers = [];

		foreach ( $this->header_whitelist as $header ) {
			if ( $value = $api_request->get_header( $header ) ) {
				$headers[ $header ] = $value;
			}
		}

		$data = [
			'method'  => strtoupper( $api_request->get_method() ),
			'route'   => $api_request->get_route(),
			'headers' => $headers,
			'query'   => $api_request->get_query_params(),
			'files'   => $api_request->get_file_params(),
			'body'    => $api_request->get_body(),
			'user'    => $request->get_user() ? $request->get_user()->ID : 0,
		];

		$as_string = wp_json_encode( $data );

		if ( json_last_error() ) {
			throw new RequestHashFailedException( json_last_error_msg(), $request );
		}

		$hash = hash( 'sha256', $as_string );

		if ( empty( $hash ) ) {
			throw new RequestHashFailedException( "PHP 'hash()' function failed.", $request );
		}

		return $hash;
	}
}