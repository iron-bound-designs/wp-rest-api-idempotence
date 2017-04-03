<?php
/**
 * Responsible for polling the Data Store for a finished request.
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
 * Class Sleep
 *
 * @package IronBound\WP_API_Idempotence
 */
class Sleep implements RequestPoller {

	/** @var int */
	private $sleep_seconds;

	/** @var int */
	private $max_queries;

	/**
	 * RequestPoller constructor.
	 *
	 * @param int $sleep_seconds Number of seconds between querying the data store.
	 * @param int $max_queries   Maximum number of times to query the data store.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct( $sleep_seconds = 1, $max_queries = 15 ) {

		if ( ! is_int( $sleep_seconds ) || $sleep_seconds <= 0 ) {
			throw new \InvalidArgumentException( '$sleep_seconds must be a positive integer.' );
		}

		$this->sleep_seconds = $sleep_seconds;
		$this->max_queries   = $max_queries;
	}

	/** @inheritdoc */
	public function poll( DataStore $data_store, IdempotentRequest $request ) {

		$max_queries = $this->max_queries;
		$response    = $request->get_response();

		while ( $max_queries >= 0 && ! $response ) {
			sleep( $this->sleep_seconds );
			$max_queries --;

			$response = $data_store->get( $request );
		}

		return $response;
	}
}