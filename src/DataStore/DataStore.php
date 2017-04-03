<?php
/**
 * Interface defining a repository for requests and responses.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\DataStore;

use IronBound\WP_API_Idempotence\Exception\DuplicateIdempotentKeyException;
use IronBound\WP_API_Idempotence\Exception\RequestHashFailedException;
use IronBound\WP_API_Idempotence\Exception\ResponseSerializeFailedException;
use IronBound\WP_API_Idempotence\Exception\ResponseUnserializeFailedException;
use IronBound\WP_API_Idempotence\IdempotentRequest;

/**
 * Interface DataStore
 *
 * @package IronBound\WP_API_Idempotence
 */
interface DataStore {

	/**
	 * Get the stored response for a given request.
	 *
	 * If there is no response, but a request is already in process, this function will set
	 * IdempotentRequest::is_in_progress() to true.
	 *
	 * @since 1.0.0
	 *
	 * @param IdempotentRequest $request
	 *
	 * @return \WP_REST_Response|\WP_Error|null
	 *
	 * @throws RequestHashFailedException
	 * @throws DuplicateIdempotentKeyException
	 * @throws ResponseUnserializeFailedException
	 */
	public function get( IdempotentRequest $request );

	/**
	 * Start an idempotent request.
	 *
	 * This will not alter the IdempotentRequest::is_in_progress() state.
	 *
	 * @since 1.0.0
	 *
	 * @param IdempotentRequest $request
	 *
	 * @throws RequestHashFailedException
	 * @throws DuplicateIdempotentKeyException
	 */
	public function start( IdempotentRequest $request );

	/**
	 * Either get the stored response for a given request or start a request if not started.
	 *
	 * If there is no response, but a request is already in process, this function will set
	 * IdempotentRequest::is_in_progress() to true.
	 *
	 * @since 1.0.0
	 *
	 * @param IdempotentRequest $request
	 *
	 * @return \WP_REST_Response|\WP_Error|null
	 *
	 * @throws RequestHashFailedException
	 * @throws DuplicateIdempotentKeyException
	 * @throws ResponseUnserializeFailedException
	 */
	public function get_or_start( IdempotentRequest $request );

	/**
	 * Finish an idempotent request by storing its response.
	 *
	 * If the response is already saved for this request, the ::finish() call will be ignored.
	 *
	 * @since 1.0.0
	 *
	 * @param IdempotentRequest $request
	 *
	 * @throws ResponseSerializeFailedException
	 */
	public function finish( IdempotentRequest $request );

	/**
	 * Drop an idempotent request from the store.
	 *
	 * @since 1.0.0
	 *
	 * @param IdempotentRequest $request
	 */
	public function drop( IdempotentRequest $request );

	/**
	 * Drop all idempotent requests older than given hours.
	 *
	 * @since 1.0.0
	 *
	 * @param int $hours Drop records older than this many hours. Accepts '0' to drop all.
	 */
	public function drop_old( $hours = 24 );
}