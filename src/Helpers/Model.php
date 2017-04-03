<?php
/**
 * IronBound DB Model for accessing a request log.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\Helpers;

/**
 * Class Model
 *
 * @package IronBound\WP_API_Idempotence
 *
 * @property int       $id
 * @property \DateTime $request_time
 * @property string    $request_hash
 * @property string    $method
 * @property string    $response
 * @property \WP_User  $user
 * @property string    $ikey
 */
class Model extends \IronBound\DB\Model {

	protected static $_cache = false;

	/**
	 * @inheritDoc
	 */
	public function get_pk() {
		return $this->id;
	}

	/**
	 * @inheritDoc
	 */
	protected static function boot() {
		parent::boot();

		/**
		 * Filter whether the models should be cached in the WordPress object cache.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $cache Whether to cache the models. Defaults to false.
		 */
		static::$_cache = apply_filters( 'wp_api_idempotence_cache_model', false );
	}

	/**
	 * @inheritDoc
	 */
	protected static function get_table() {
		return static::$_db_manager->get( Table::SLUG );
	}
}