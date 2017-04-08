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
final class Middleware {

	/** @var DataStore */
	private $data_store;

	/** @var RequestPoller */
	private $poller;

	/** @var IdempotentRequestFactory */
	private $factory;

	/** @var Config */
	private $config;

	/** @var \SplObjectStorage */
	private $request_key_map;

	/**
	 * Middleware constructor.
	 *
	 * @param RequestPoller            $poller
	 * @param DataStore                $data_store
	 * @param IdempotentRequestFactory $factory
	 * @param Config                   $config
	 */
	public function __construct( RequestPoller $poller, DataStore $data_store, IdempotentRequestFactory $factory, Config $config ) {
		$this->data_store = $data_store;
		$this->poller     = $poller;
		$this->factory    = $factory;
		$this->config     = $config;

		$this->request_key_map = new \SplObjectStorage();
	}

	/**
	 * Register filters.
	 *
	 * @since 1.0.0
	 */
	public function initialize() {
		add_filter( 'rest_pre_dispatch', [ $this, 'pre_dispatch' ], 0, 3 );
		add_filter( 'rest_post_dispatch', [ $this, 'post_dispatch' ], 100, 3 );
	}

	/**
	 * Unregister the filters.
	 *
	 * @since 1.0.0
	 */
	public function deinitialize() {
		remove_filter( 'rest_pre_dispatch', [ $this, 'pre_dispatch' ], 0 );
		remove_filter( 'rest_post_dispatch', [ $this, 'post_dispatch' ], 100 );
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

		$idempotency_key = $this->extract_idempotency_key( $request );

		if ( ! $idempotency_key ) {
			return null;
		}

		$this->request_key_map[ $request ] = $idempotency_key;

		$user = wp_get_current_user() ?: null;

		if ( ! $user && ! $this->config->are_logged_out_users_allowed() ) {
			return new \WP_Error(
				'rest_invalid_idempotency_key',
				__( 'Idempotency keys are not allowed for logged-out users.', 'wp-api-idempotence' ),
				[ 'status' => 400 ]
			);
		}

		$idempotent_request = $this->factory->make( $idempotency_key, $request, $user );

		try {

			$response = $this->data_store->get_or_start( $idempotent_request );

			if ( $response ) {
				return $response;
			}

			if ( ! $idempotent_request->is_in_progress() ) {
				return $response;
			}

			$response = $this->poller->poll( $this->data_store, $idempotent_request );

			if ( ! $response ) {
				return new \WP_Error(
					'rest_retry_idempotent_request',
					__( 'Please retry this request in a few minutes.', 'wp-api-idempotence' ),
					[ 'status' => 500 ]
				);
			}

		} catch ( DuplicateIdempotentKeyException $e ) {
			return new \WP_Error( 'rest_duplicate_idempotency_key', $e->getMessage(), [ 'status' => 400 ] );
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

		if ( $this->request_key_map->contains( $request ) ) {
			$idempotency_key = $this->request_key_map[ $request ];
		} else {
			$idempotency_key = $this->extract_idempotency_key( $request );

			if ( ! $idempotency_key ) {
				return $response;
			}
		}

		if ( is_wp_error( $response ) && $response->get_error_code() === 'rest_retry_idempotent_request' ) {
			return $response;
		}

		$user = wp_get_current_user() ?: null;

		if ( ! $user && ! $this->config->are_logged_out_users_allowed() ) {
			return $response;
		}

		$idempotent_request = $this->factory->make( $idempotency_key, $request, $user );
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
	protected function extract_idempotency_key( \WP_REST_Request $request ) {

		switch ( $this->config->get_key_location() ) {
			case Config::LOCATION_HEADER:
				$header = "X-{$this->config->get_key_name()}";
				$key    = $request->get_header( $header );
				$request->remove_header( $request::canonicalize_header_name( $header ) );
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

	/**
	 * Get the data store powering this middleware.
	 *
	 * @since 1.0.0
	 *
	 * @return DataStore
	 */
	public function get_data_store() {
		return $this->data_store;
	}
}