<?php
/**
 * Middleware to capture requests with an idempotency key.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence;

use IronBound\WP_API_Idempotence\DataStore\DataStore;
use IronBound\WP_API_Idempotence\Exception\DuplicateIdempotentKeyException;
use IronBound\WP_API_Idempotence\Exception\Exception;
use IronBound\WP_API_Idempotence\RequestPoller\RequestPoller;

/**
 * Class Middleware
 *
 * @package IronBound\WP_API_Idempotence
 */
class Middleware {

	/** @var Config */
	private $config;

	/** @var DataStore */
	private $data_store;

	/** @var RequestPoller */
	private $poller;

	/**
	 * Middleware constructor.
	 *
	 * @param RequestPoller $poller
	 * @param DataStore     $data_store
	 * @param Config        $config
	 */
	public function __construct( RequestPoller $poller, DataStore $data_store, Config $config ) {
		$this->config     = $config;
		$this->data_store = $data_store;
		$this->poller     = $poller;
	}

	/**
	 * Register filters.
	 *
	 * @since 1.0.0
	 */
	public function filters() {
		add_filter( 'rest_pre_dispatch', [ $this, 'pre_dispatch' ], 0, 3 );
		add_filter( 'rest_post_dispatch', [ $this, 'post_dispatch' ], 100, 3 );
	}

	/**
	 * Pre Dispatch Filter.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Response|\WP_Error|null $response
	 * @param \WP_REST_Server                  $server
	 * @param \WP_REST_Request                 $request
	 *
	 * @return \WP_REST_Response|\WP_Error|null
	 */
	public function pre_dispatch( $response, $server, $request ) {

		if ( $response ) {
			return $response;
		}

		if ( ! in_array( strtoupper( $request->get_method() ), $this->config->get_applicable_methods(), true ) ) {
			return $response;
		}

		$idempotence_key = $this->extract_idempotent_key( $request );

		if ( ! $idempotence_key ) {
			return null;
		}

		$user = wp_get_current_user() ?: null;

		$idempotent_request = new IdempotentRequest( $idempotence_key, $request, $user );

		try {

			$response = $this->data_store->get_or_start( $idempotent_request );

			if ( $response ) {
				return $response;
			}

			if ( ! $idempotent_request->is_in_progress() ) {
				return $response;
			}

			$response = $this->poller->poll( $this->data_store, $idempotent_request );
		} catch ( DuplicateIdempotentKeyException $e ) {
			return new \WP_Error( 'rest_duplicate_idempotent_key', $e->getMessage(), [ 'status' => 400 ] );
		} catch ( Exception $e ) {
			return new \WP_Error(
				'rest_internal_error',
				__( 'Internal Server Error', 'wp-api-idempotence' ),
				[ 'status' => 500 ]
			);
		}

		return $response;
	}

	/**
	 * Immediately prior to the response being sent, update the idempotence record.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Response|\WP_Error $response
	 * @param \WP_REST_Server             $server
	 * @param \WP_REST_Request            $request
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function post_dispatch( $response, $server, $request ) {

		if ( ! in_array( strtoupper( $request->get_method() ), $this->config->get_applicable_methods(), true ) ) {
			return $response;
		}

		$idempotence_key = $this->extract_idempotent_key( $request );

		if ( ! $idempotence_key ) {
			return $response;
		}

		$user = wp_get_current_user() ?: null;

		$idempotent_request = new IdempotentRequest( $idempotence_key, $request, $user );
		$idempotent_request->set_response( $response );

		$this->data_store->finish( $idempotent_request );

		return $response;
	}

	/**
	 * Extract the idempotent key from a request.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return string
	 */
	protected function extract_idempotent_key( \WP_REST_Request $request ) {

		switch ( $this->config->get_key_location() ) {
			case Config::LOCATION_HEADER:
				$key = $request->get_header( "X-{$this->config->get_key_name()}" );
				$request->remove_header( "X-{$this->config->get_key_name()}" );
				break;
			case Config::LOCATION_BODY:
				$key = $request->get_param( $this->config->get_key_name() );
				$request->set_param( $this->config->get_key_name(), null );
				break;
			default:
				$key = '';
				break;
		}

		return $key ?: '';
	}
}