<?php
/**
 * DataStores that requires an installation function.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\DataStore;

/**
 * Interface Installable
 *
 * @package IronBound\WP_API_Idempotence\DataStore
 */
interface Installable extends DataStore {

	/**
	 * Install the data store.
	 *
	 * @since 1.0.0
	 */
	public function install();
}