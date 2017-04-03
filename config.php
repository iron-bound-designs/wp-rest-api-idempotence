<?php
/**
 * Auto-loader config.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

return [
	'poll.sleepSeconds' => 1,
	'poll.maxQueries'   => 15,

	'wpdb' => DI\factory( function () { return $GLOBALS['wpdb']; } ),

	'responseSerializer' => DI\object( 'IronBound\WP_API_Idempotence\ResponseSerializer\JSON' ),
	'requestHasher'      => DI\object( 'IronBound\WP_API_Idempotence\RequestHasher\Simple' ),

	'IronBound\WP_API_Idempotence\RequestHasher\RequestHasher' =>
		DI\object( 'IronBound\WP_API_Idempotence\RequestHasher\Cached' )
			->constructor( DI\link( 'requestHasher' ) ),

	'IronBound\WP_API_Idempotence\RequestPoller\RequestPoller' =>
		DI\object( 'IronBound\WP_API_Idempotence\RequestPoller\Sleep' )
			->constructor( DI\link( 'poll.sleepSeconds' ), DI\link( 'poll.maxQueries' ) ),

	'IronBound\WP_API_Idempotence\ResponseSerializer\ResponseSerializer' => DI\factory( function ( \Interop\Container\ContainerInterface $container ) {
		$serializer = $container->get( 'responseSerializer' );

		if ( $serializer instanceof \IronBound\WP_API_Idempotence\ResponseSerializer\Filterable ) {
			$serializer = new \IronBound\WP_API_Idempotence\ResponseSerializer\Filtered( $serializer );
		}

		return $serializer;
	} ),

	'IronBound\WP_API_Idempotence\DataStore\DataStore' =>
		DI\object( 'IronBound\WP_API_Idempotence\DataStore\DB' )
			->constructor(
				DI\link( 'IronBound\WP_API_Idempotence\RequestHasher\RequestHasher' ),
				DI\link( 'IronBound\WP_API_Idempotence\ResponseSerializer\ResponseSerializer' ),
				DI\link( 'wpdb' )
			),

	'IronBound\WP_API_Idempotence\Config' => DI\factory( function () {
		return \IronBound\WP_API_Idempotence\Config::from_settings( [] );
	} ),

	'dataStore' => DI\link( 'IronBound\WP_API_Idempotence\DataStore\DataStore' ),
	'config'    => DI\link( 'IronBound\WP_API_Idempotence\Config' ),
];