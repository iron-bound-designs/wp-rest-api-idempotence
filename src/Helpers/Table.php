<?php
/**
 * Iron Bound DB Table for storing a request log.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\Helpers;

use IronBound\DB\Table\BaseTable;
use IronBound\DB\Table\Column\Column;
use IronBound\DB\Table\Column\DateTime;
use IronBound\DB\Table\Column\ForeignUser;
use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\StringBased;

/**
 * Class Table
 *
 * @package IronBound\WP_API_Idempotence
 */
class Table extends BaseTable {

	const SLUG = 'wp-api-idempotence-log';

	/** @var Column[] */
	private $columns = array();

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) { return "{$wpdb->prefix}wp_api_idempotence_log"; }

	/**
	 * @inheritDoc
	 */
	public function get_slug() { return self::SLUG; }

	/**
	 * @inheritDoc
	 */
	public function get_columns() {

		if ( $this->columns ) {
			return $this->columns;
		}

		$this->columns = [
			'id'           => new IntegerBased( 'BIGINT', 'id', [ 'unsigned', 'NOT NULL', 'auto_increment' ], [ 20 ] ),
			'request_time' => new DateTime( 'request_time', [ 'NOT NULL' ] ),
			'request_hash' => new StringBased( 'VARCHAR', 'request_hash', [ 'NOT NULL' ], [ 255 ] ),
			'method'       => new StringBased( 'VARCHAR', 'method', [ 'NOT NULL' ], [ 6 ] ),
			'response'     => new StringBased( 'LONGTEXT', 'response' ),
			'user'         => new ForeignUser( 'user' ),
			'ikey'         => new StringBased( 'VARCHAR', 'ikey', [ 'NOT NULL' ], [ 64 ] ),
		];

		return $this->columns;
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return [
			'id'           => 0,
			'request_time' => date( 'Y-m-d H:i:s', time() ),
			'request_hash' => '',
			'method'       => '',
			'response'     => '',
			'user'         => get_current_user_id(),
			'ikey'          => '',
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function get_keys() {
		$keys = parent::get_keys();

		$keys[] = "UNIQUE user__ikey (user,ikey)";

		return $keys;
	}

	/**
	 * @inheritDoc
	 */
	public function get_primary_key() { return 'id'; }

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }
}