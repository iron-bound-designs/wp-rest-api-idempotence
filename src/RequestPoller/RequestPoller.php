<?php
/**
 * RequestPoller interface. Responsible for polling the Data Store for a finished request.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\RequestPoller;

use IronBound\WP_API_Idempotence\DataStore\DataStore;
use IronBound\WP_API_Idempotence\IdempotentRequest;

/**
 * Interface RequestPoller
 *
 * @package IronBound\WP_API_Idempotence\RequestPoller
 */
interface RequestPoller {

	/**
	 * Poll the data store for a completed request.
	 *
	 * @since 1.0.0
	 *
	 * @param DataStore         $data_store
	 * @param IdempotentRequest $request
	 *
	 * @return \WP_REST_Response|\WP_Error|null
	 */
	public function poll( DataStore $data_store, IdempotentRequest $request );
}