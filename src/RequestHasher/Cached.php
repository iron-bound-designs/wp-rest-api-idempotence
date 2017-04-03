<?php
/**
 * Caching layer around RequestHasher.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\RequestHasher;

use IronBound\WP_API_Idempotence\IdempotentRequest;

/**
 * Class Cached
 *
 * This caches the hashes of `WP_REST_Request` objects in memory. Only an identical object
 * will match the cache. A request object with the same properties will re-hash the request.
 *
 * @package IronBound\WP_API_Idempotence\RequestHasher
 */
class Cached implements RequestHasher {

	/** @var RequestHasher */
	private $hasher;

	/** @var \SplObjectStorage */
	private $storage;

	/**
	 * CachedRequestHasher constructor.
	 *
	 * @param RequestHasher $hasher
	 */
	public function __construct( RequestHasher $hasher ) {
		$this->hasher  = $hasher;
		$this->storage = new \SplObjectStorage();
	}

	/**
	 * @inheritDoc
	 */
	public function hash( IdempotentRequest $request ) {

		$storage = $this->storage;

		if ( $storage->contains( $request->get_request() ) ) {
			return $storage[ $request->get_request() ];
		}

		$hash = $this->hasher->hash( $request );

		$storage[ $request->get_request() ] = $hash;

		return $hash;
	}
}
