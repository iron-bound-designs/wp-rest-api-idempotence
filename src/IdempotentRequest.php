<?php
/**
 * Idempotent request object.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence;

/**
 * Class IdempotentRequest
 *
 * @package IronBound\WP_API_Idempotence
 */
class IdempotentRequest {

	/** @var string */
	private $idempotent_key;

	/** @var \WP_REST_Request */
	private $request;

	/** @var \WP_User|null */
	private $user;

	/** @var \WP_REST_Response|\WP_Error */
	private $response;

	/** @var bool */
	private $in_progress = false;

	/**
	 * IdempotentRequest constructor.
	 *
	 * @param string           $idempotent_key
	 * @param \WP_REST_Request $request
	 * @param \WP_User|null    $user
	 */
	public function __construct( $idempotent_key, \WP_REST_Request $request, \WP_User $user = null ) {
		$this->idempotent_key = $idempotent_key;
		$this->request        = $request;
		$this->user           = $user;
	}

	/**
	 * Get the key identifying this request.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_idempotent_key() {
		return $this->idempotent_key;
	}

	/**
	 * Get the API request object.
	 *
	 * @since 1.0.0
	 *
	 * @return \WP_REST_Request
	 */
	public function get_request() {
		return $this->request;
	}

	/**
	 * Get the user making the request.
	 *
	 * @since 1.0.0
	 *
	 * @return \WP_User|null
	 */
	public function get_user() {
		return $this->user;
	}

	/**
	 * Get the response to be sent back to the API client.
	 *
	 * @since 1.0.0
	 *
	 * @return \WP_Error|\WP_REST_Response|null
	 */
	public function get_response() {
		return $this->response;
	}

	/**
	 * Set the response to be sent back to the API client.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Error|\WP_REST_Response $response
	 */
	public function set_response( $response ) {
		$this->response = $response;
	}

	/**
	 * Is an idempotent request with this key already in progress.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_in_progress() {
		return $this->in_progress;
	}

	/**
	 * Mark that an idempotent request is already in progress and this request should stall for a response.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $in_progress
	 */
	public function set_in_progress( $in_progress ) {
		$this->in_progress = $in_progress;
	}

	/**
	 * @inheritDoc
	 */
	public function __toString() {

		if ( $this->get_user() ) {
			return sprintf(
			/* translators: %1$s is the HTTP method. %2$s is the route. %3$s is the username. */
				__( '\'%1%s\' request to \'%2$s\' by \'%3$s\'', 'wp-api-idempotence' ),
				$this->get_request()->get_method(),
				$this->get_request()->get_route(),
				$this->get_user()->user_login
			);
		}

		return sprintf(
		/* translators: %1$s is the HTTP method. %2$s is the route. */
			__( '\'%1%s\' request to \'%2$s\'', 'wp-api-idempotence' ),
			$this->get_request()->get_method(),
			$this->get_request()->get_route()
		);
	}
}