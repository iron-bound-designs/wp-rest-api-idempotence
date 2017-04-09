<?php
/**
 * Test the middleware class.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\Tests\Unit;

use IronBound\WP_API_Idempotence\Config;
use IronBound\WP_API_Idempotence\IdempotentRequest;
use IronBound\WP_API_Idempotence\IdempotentRequestFactory;
use IronBound\WP_API_Idempotence\Middleware;
use IronBound\WP_API_Idempotence\Tests\TestCase;

/**
 * Class TestMiddleware
 *
 * @package IronBound\WP_API_Idempotence\Tests\Unit
 */
class TestMiddleware extends TestCase {

	public function test_response_passed_through() {

		$middleware = new Middleware(
			$this->getMockBuilder( 'IronBound\WP_API_Idempotence\RequestPoller\RequestPoller' )->getMock(),
			$this->getMockBuilder( 'IronBound\WP_API_Idempotence\DataStore\DataStore' )->getMock(),
			new IdempotentRequestFactory(),
			new Config()
		);

		$response  = new \WP_REST_Response();
		$_response = $middleware->pre_dispatch( $response, rest_get_server(), new \WP_REST_Request() );

		$this->assertSame( $response, $_response );
	}

	public function test_response_ignored_for_non_applicable_methods() {

		$middleware = new Middleware(
			$this->getMockBuilder( 'IronBound\WP_API_Idempotence\RequestPoller\RequestPoller' )->getMock(),
			$this->getMockBuilder( 'IronBound\WP_API_Idempotence\DataStore\DataStore' )->getMock(),
			new IdempotentRequestFactory(),
			Config::from_settings( [] )
		);

		$request = new \WP_REST_Request();
		$request->set_method( 'GET' );
		$returned = $middleware->pre_dispatch( null, rest_get_server(), $request );

		$this->assertNull( $returned );
	}

	public function test_response_ignored_if_key_missing() {

		$middleware = new Middleware(
			$this->getMockBuilder( 'IronBound\WP_API_Idempotence\RequestPoller\RequestPoller' )->getMock(),
			$this->getMockBuilder( 'IronBound\WP_API_Idempotence\DataStore\DataStore' )->getMock(),
			new IdempotentRequestFactory(),
			Config::from_settings( [] )
		);

		$request = new \WP_REST_Request();
		$request->set_method( 'POST' );
		$returned = $middleware->pre_dispatch( null, rest_get_server(), $request );

		$this->assertNull( $returned );
	}

	public function test_response_started() {

		wp_set_current_user( 1 );
		$user = wp_get_current_user();

		$request = new \WP_REST_Request();
		$request->set_method( 'POST' );
		$request->add_header( 'X-WP-Idempotency-Key', 'key' );
		$i_request = new IdempotentRequest( 'key', $request, $user );

		$store = $this->getMockBuilder( 'IronBound\WP_API_Idempotence\DataStore\DataStore' )
		              ->setMethods( [ 'get_or_start' ] )->getMockForAbstractClass();
		$store->expects( $this->once() )->method( 'get_or_start' )->with( $i_request )->willReturn( null );

		$factory = $this->getMockBuilder( 'IronBound\WP_API_Idempotence\IdempotentRequestFactory' )
		                ->setMethods( [ 'make' ] )->getMockForAbstractClass();
		$factory->expects( $this->once() )->method( 'make' )->with( 'key', $request, $user )->willReturn( $i_request );

		$middleware = new Middleware(
			$this->getMockBuilder( 'IronBound\WP_API_Idempotence\RequestPoller\RequestPoller' )->getMock(),
			$store,
			$factory,
			Config::from_settings( [] )
		);

		$returned = $middleware->pre_dispatch( null, rest_get_server(), $request );

		$this->assertNull( $returned );
	}

	public function test_existing_response_returned() {

		wp_set_current_user( 1 );
		$user = wp_get_current_user();

		$request = new \WP_REST_Request();
		$request->set_method( 'POST' );
		$request->add_header( 'X-WP-Idempotency-Key', 'key' );
		$i_request = new IdempotentRequest( 'key', $request, $user );
		$response  = new \WP_REST_Response();

		$store = $this->getMockBuilder( 'IronBound\WP_API_Idempotence\DataStore\DataStore' )
		              ->setMethods( [ 'get_or_start' ] )->getMockForAbstractClass();
		$store->expects( $this->once() )->method( 'get_or_start' )->with( $i_request )->willReturn( $response );

		$factory = $this->getMockBuilder( 'IronBound\WP_API_Idempotence\IdempotentRequestFactory' )
		                ->setMethods( [ 'make' ] )->getMockForAbstractClass();
		$factory->expects( $this->once() )->method( 'make' )->with( 'key', $request, $user )->willReturn( $i_request );

		$middleware = new Middleware(
			$this->getMockBuilder( 'IronBound\WP_API_Idempotence\RequestPoller\RequestPoller' )->getMock(),
			$store,
			$factory,
			Config::from_settings( [] )
		);

		$returned = $middleware->pre_dispatch( null, rest_get_server(), $request );

		$this->assertEquals( $response, $returned );
	}

	public function test_polling_for_response_if_request_in_progress() {

		wp_set_current_user( 1 );
		$user = wp_get_current_user();

		$request = new \WP_REST_Request();
		$request->set_method( 'POST' );
		$request->add_header( 'X-WP-Idempotency-Key', 'key' );
		$i_request = new IdempotentRequest( 'key', $request, $user );
		$response  = new \WP_REST_Response();

		$store = $this->getMockBuilder( 'IronBound\WP_API_Idempotence\DataStore\DataStore' )
		              ->setMethods( [ 'get_or_start' ] )->getMockForAbstractClass();
		$store->expects( $this->once() )->method( 'get_or_start' )->with( $i_request )
		      ->willReturnCallback( function () use ( $i_request ) {
			      $i_request->set_in_progress( true );

			      return null;
		      } );

		$factory = $this->getMockBuilder( 'IronBound\WP_API_Idempotence\IdempotentRequestFactory' )
		                ->setMethods( [ 'make' ] )->getMockForAbstractClass();
		$factory->expects( $this->once() )->method( 'make' )->with( 'key', $request, $user )->willReturn( $i_request );

		$poller = $this->getMockBuilder( 'IronBound\WP_API_Idempotence\RequestPoller\RequestPoller' )
		               ->setMethods( [ 'poll' ] )->getMockForAbstractClass();
		$poller->expects( $this->once() )->method( 'poll' )->with( $store, $i_request )->willReturn( $response );

		$middleware = new Middleware( $poller, $store, $factory, Config::from_settings( [] ) );

		$returned = $middleware->pre_dispatch( null, rest_get_server(), $request );

		$this->assertEquals( $response, $returned );
	}

	public function test_error_returned_if_polling_failed() {

		wp_set_current_user( 1 );
		$user = wp_get_current_user();

		$request = new \WP_REST_Request();
		$request->set_method( 'POST' );
		$request->add_header( 'X-WP-Idempotency-Key', 'key' );
		$i_request = new IdempotentRequest( 'key', $request, $user );

		$store = $this->getMockBuilder( 'IronBound\WP_API_Idempotence\DataStore\DataStore' )
		              ->setMethods( [ 'get_or_start' ] )->getMockForAbstractClass();
		$store->expects( $this->once() )->method( 'get_or_start' )->with( $i_request )
		      ->willReturnCallback( function () use ( $i_request ) {
			      $i_request->set_in_progress( true );

			      return null;
		      } );

		$factory = $this->getMockBuilder( 'IronBound\WP_API_Idempotence\IdempotentRequestFactory' )
		                ->setMethods( [ 'make' ] )->getMockForAbstractClass();
		$factory->expects( $this->once() )->method( 'make' )->with( 'key', $request, $user )->willReturn( $i_request );

		$poller = $this->getMockBuilder( 'IronBound\WP_API_Idempotence\RequestPoller\RequestPoller' )
		               ->setMethods( [ 'poll' ] )->getMockForAbstractClass();
		$poller->expects( $this->once() )->method( 'poll' )->with( $store, $i_request )->willReturn( null );

		$middleware = new Middleware( $poller, $store, $factory, Config::from_settings( [] ) );

		$returned = $middleware->pre_dispatch( null, rest_get_server(), $request );

		$this->assertWPError( $returned );
		$this->assertEquals( 'rest_retry_idempotent_request', $returned->get_error_code() );
	}

	public function test_retry_error_no_stored_as_response() {

		wp_set_current_user( 1 );
		$user = wp_get_current_user();

		$request = new \WP_REST_Request();
		$request->set_method( 'POST' );
		$request->add_header( 'X-WP-Idempotency-Key', 'key' );
		$i_request = new IdempotentRequest( 'key', $request, $user );

		$store = $this->getMockBuilder( 'IronBound\WP_API_Idempotence\DataStore\DataStore' )
		              ->setMethods( [ 'finish' ] )->getMockForAbstractClass();
		$store->expects( $this->never() )->method( 'finish' );

		$factory = $this->getMockBuilder( 'IronBound\WP_API_Idempotence\IdempotentRequestFactory' )
		                ->setMethods( [ 'make' ] )->getMockForAbstractClass();
		$factory->method( 'make' )->with( 'key', $request, $user )->willReturn( $i_request );

		$poller = $this->getMockBuilder( 'IronBound\WP_API_Idempotence\RequestPoller\RequestPoller' )
		               ->getMockForAbstractClass();

		$middleware = new Middleware( $poller, $store, $factory, Config::from_settings( [] ) );

		$error = new \WP_Error(
			'rest_retry_idempotent_request',
			__( 'Please retry this request in a few minutes.', 'wp-rest-api-idempotence' ),
			[ 'status' => 500 ]
		);

		$returned = $middleware->post_dispatch( $error, rest_get_server(), $request );

		$this->assertWPError( $returned );
		$this->assertEquals( 'rest_retry_idempotent_request', $returned->get_error_code() );
	}

	public function test_response_ignored_for_non_applicable_methods_post_dispatch() {

		$middleware = new Middleware(
			$this->getMockBuilder( 'IronBound\WP_API_Idempotence\RequestPoller\RequestPoller' )->getMock(),
			$this->getMockBuilder( 'IronBound\WP_API_Idempotence\DataStore\DataStore' )->getMock(),
			new IdempotentRequestFactory(),
			Config::from_settings( [] )
		);

		$request = new \WP_REST_Request();
		$request->set_method( 'GET' );
		$returned = $middleware->post_dispatch( null, rest_get_server(), $request );

		$this->assertNull( $returned );
	}

	public function test_response_ignored_if_key_missing_post_dispatch() {

		$middleware = new Middleware(
			$this->getMockBuilder( 'IronBound\WP_API_Idempotence\RequestPoller\RequestPoller' )->getMock(),
			$this->getMockBuilder( 'IronBound\WP_API_Idempotence\DataStore\DataStore' )->getMock(),
			new IdempotentRequestFactory(),
			Config::from_settings( [] )
		);

		$request = new \WP_REST_Request();
		$request->set_method( 'POST' );
		$returned = $middleware->post_dispatch( null, rest_get_server(), $request );

		$this->assertNull( $returned );
	}

	public function test_response_stored_post_dispatch() {

		wp_set_current_user( 1 );
		$user = wp_get_current_user();

		$request = new \WP_REST_Request();
		$request->set_method( 'POST' );
		$request->add_header( 'X-WP-Idempotency-Key', 'key' );
		$i_request = new IdempotentRequest( 'key', $request, $user );
		$response  = new \WP_REST_Response();

		$store = $this->getMockBuilder( 'IronBound\WP_API_Idempotence\DataStore\DataStore' )
		              ->setMethods( [ 'finish' ] )->getMockForAbstractClass();
		$store->expects( $this->once() )->method( 'finish' )
		      ->with( $this->callback( function ( $request ) use ( $i_request, $response ) {
			      return $request === $i_request && $response === $request->get_response();
		      } ) );

		$factory = $this->getMockBuilder( 'IronBound\WP_API_Idempotence\IdempotentRequestFactory' )
		                ->setMethods( [ 'make' ] )->getMockForAbstractClass();
		$factory->expects( $this->once() )->method( 'make' )->with( 'key', $request, $user )->willReturn( $i_request );

		$middleware = new Middleware(
			$this->getMockBuilder( 'IronBound\WP_API_Idempotence\RequestPoller\RequestPoller' )->getMock(),
			$store,
			$factory,
			Config::from_settings( [] )
		);

		$returned = $middleware->post_dispatch( $response, rest_get_server(), $request );

		$this->assertEquals( $response, $returned );
		$this->assertEquals( $response, $i_request->get_response() );
	}

	public function test_extract_idempotency_key_from_header_removes_header_from_request() {

		wp_set_current_user( 1 );
		$user = wp_get_current_user();

		$request = new \WP_REST_Request();
		$request->set_method( 'POST' );
		$request->add_header( 'X-WP-Idempotency-Key', 'key' );
		$i_request = new IdempotentRequest( 'key', $request, $user );

		$store = $this->getMockBuilder( 'IronBound\WP_API_Idempotence\DataStore\DataStore' )
		              ->setMethods( [ 'get_or_start' ] )->getMockForAbstractClass();
		$store->expects( $this->once() )->method( 'get_or_start' )->with( $i_request )->willReturn( null );

		$factory = $this->getMockBuilder( 'IronBound\WP_API_Idempotence\IdempotentRequestFactory' )
		                ->setMethods( [ 'make' ] )->getMockForAbstractClass();
		$factory->expects( $this->once() )->method( 'make' )->with( 'key', $request, $user )->willReturn( $i_request );

		$middleware = new Middleware(
			$this->getMockBuilder( 'IronBound\WP_API_Idempotence\RequestPoller\RequestPoller' )->getMock(),
			$store,
			$factory,
			Config::from_settings( [] )
		);
		$middleware->pre_dispatch( null, rest_get_server(), $request );

		$this->assertNull( $request->get_header( 'X-WP-Idempotency-Key' ) );
	}

	public function test_extract_idempotency_key_from_header_removes_body_param_from_request() {

		$this->markTestSkipped( '#40344' );

		wp_set_current_user( 1 );
		$user = wp_get_current_user();

		$request = new \WP_REST_Request();
		$request->set_method( 'POST' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			'hi'                 => 'there',
			'WP-Idempotency-Key' => 'key'
		) ) );

		$i_request = new IdempotentRequest( 'key', $request, $user );

		$store = $this->getMockBuilder( 'IronBound\WP_API_Idempotence\DataStore\DataStore' )
		              ->setMethods( [ 'get_or_start' ] )->getMockForAbstractClass();
		$store->expects( $this->once() )->method( 'get_or_start' )->with( $i_request )->willReturn( null );

		$factory = $this->getMockBuilder( 'IronBound\WP_API_Idempotence\IdempotentRequestFactory' )
		                ->setMethods( [ 'make' ] )->getMockForAbstractClass();
		$factory->expects( $this->once() )->method( 'make' )->with( 'key', $request, $user )->willReturn( $i_request );

		$middleware = new Middleware(
			$this->getMockBuilder( 'IronBound\WP_API_Idempotence\RequestPoller\RequestPoller' )->getMock(),
			$store,
			$factory,
			Config::from_settings( [ 'key_location' => Config::LOCATION_BODY ] )
		);
		$middleware->pre_dispatch( null, rest_get_server(), $request );

		$this->assertNull( $request->get_param( 'WP-Idempotency-Key' ) );
	}
}