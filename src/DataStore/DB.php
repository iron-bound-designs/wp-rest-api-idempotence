<?php
/**
 * DataStore implementation for IronBound\DB.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\DataStore;

use IronBound\DB\Exception as DBException;
use IronBound\DB\Manager;
use IronBound\WP_API_Idempotence\Config;
use IronBound\WP_API_Idempotence\Exception\DuplicateIdempotentKeyException;
use IronBound\WP_API_Idempotence\Helpers\Table;
use IronBound\WP_API_Idempotence\IdempotentRequest;
use IronBound\WP_API_Idempotence\Helpers\Model;
use IronBound\WP_API_Idempotence\RequestHasher\RequestHasher;
use IronBound\WP_API_Idempotence\ResponseSerializer\ResponseSerializer;

/**
 * Class DB
 *
 * @package IronBound\WP_API_Idempotence\DataStore
 */
final class DB implements DataStore, Installable, Configurable {

	/** @var RequestHasher */
	private $request_hasher;

	/** @var ResponseSerializer */
	private $response_serializer;

	/** @var \wpdb */
	private $wpdb;

	/** @var Table */
	private $table;

	/**
	 * IronBoundDataStore constructor.
	 *
	 * @param RequestHasher      $request_hasher
	 * @param ResponseSerializer $response_serializer
	 * @param \wpdb              $wpdb
	 */
	public function __construct( RequestHasher $request_hasher, ResponseSerializer $response_serializer, \wpdb $wpdb ) {
		$this->request_hasher      = $request_hasher;
		$this->response_serializer = $response_serializer;
		$this->wpdb                = $wpdb;
		$this->table               = new Table();
	}

	/**
	 * @inheritDoc
	 */
	public function get( IdempotentRequest $request ) {

		$model = $this->get_and_check_model( $request );

		if ( ! $model ) {
			return null;
		}

		if ( ! $model->response ) {
			$request->set_in_progress( true );

			return null;
		}

		$response = $this->response_serializer->unserialize( $model->response );

		$request->set_response( $response );

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function start( IdempotentRequest $request ) {

		$table = $this->table;
		$tn    = $table->get_table_name( $this->wpdb );

		$prev = $this->wpdb->show_errors( false );

		$this->wpdb->query(
			$this->wpdb->prepare(
				"INSERT INTO {$tn} (`user`,ikey,request_hash,method) VALUES(%d,%s,%s,%s) ON DUPLICATE KEY UPDATE ikey = ikey",
				$request->get_user() ? $request->get_user()->ID : 0,
				$request->get_idempotency_key(),
				$this->request_hasher->hash( $request ),
				$request->get_request()->get_method()
			)
		);
		$this->wpdb->show_errors( $prev );

		if ( $this->wpdb->last_error ) {

			if ( $this->wpdb->use_mysqli ) {
				$error_number = mysqli_errno( $this->wpdb->dbh );
			} else {
				$error_number = mysql_errno( $this->wpdb->dbh );
			}

			return new DBException( $this->wpdb->last_error, $error_number );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_or_start( IdempotentRequest $request ) {

		$response = $this->get( $request );

		if ( $response ) {
			return $response;
		}

		$this->start( $request );

		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function finish( IdempotentRequest $request ) {

		$model = $this->get_model( $request );

		if ( ! $model || $model->response ) {
			return;
		}

		$model->response = $this->response_serializer->serialize( $request->get_response() );
		$model->save();
	}

	/**
	 * @inheritDoc
	 */
	public function drop( IdempotentRequest $request ) {

		$model = $this->get_model( $request );

		if ( $model ) {
			$model->delete();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function drop_old( $hours = 24 ) {

		$wpdb = $this->wpdb;

		$table = $this->table;

		if ( $hours ) {
			$now = new \DateTime( 'now', new \DateTimeZone( 'UTC' ) );
			$now->sub( new \DateInterval( "PT{$hours}H" ) );

			$wpdb->query( $wpdb->prepare(
				"DELETE FROM {$table->get_table_name( $wpdb )} WHERE `requested_at` < %s",
				$now->format( 'Y-m-d H:i:s' )
			) );
		} else {
			$wpdb->query( "DELETE FROM {$table->get_table_name( $wpdb )}" );
		}
	}

	/**
	 * Attempt to retrieve the model for an idempotent request.
	 *
	 * @since 1.0.0
	 *
	 * @param IdempotentRequest $request
	 *
	 * @return Model|null
	 */
	private function get_model( IdempotentRequest $request ) {
		/** @noinspection ExceptionsAnnotatingAndHandlingInspection */
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return Model::query()->where( [
			'user' => $request->get_user() ? $request->get_user()->ID : 0,
			'ikey' => $request->get_idempotency_key(),
		] )->first() ?: null;
	}

	/**
	 * Attempt to retrieve the model for an idempotent key and check if the request hash matches.
	 *
	 * @since 1.0.0
	 *
	 * @param IdempotentRequest $request
	 *
	 * @return Model|null
	 *
	 * @throws DuplicateIdempotentKeyException
	 */
	private function get_and_check_model( IdempotentRequest $request ) {

		$model = $this->get_model( $request );

		if ( $model && $model->request_hash !== $this->request_hasher->hash( $request ) ) {
			throw new DuplicateIdempotentKeyException( $request );
		}

		return $model;
	}

	/**
	 * @inheritDoc
	 */
	public function install() {
		Manager::maybe_install_table( $this->table );
	}

	/**
	 * @inheritDoc
	 */
	public function configure( Config $config ) {
		Manager::register( $this->table, '', '\IronBound\WP_API_Idempotence\Helpers\Model' );
	}
}