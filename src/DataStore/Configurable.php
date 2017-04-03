<?php
/**
 * Interface for data stores that need to be configured.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\DataStore;

use IronBound\WP_API_Idempotence\Config;

/**
 * Interface WithConfiguration
 *
 * @package IronBound\WP_API_Idempotence\DataStore
 */
interface Configurable extends DataStore {

	/**
	 * Configure the data store.
	 *
	 * @since 1.0.0
	 *
	 * @param Config $config
	 */
	public function configure( Config $config );
}