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

	'config'             => DI\link( 'IronBound\WP_API_Idempotence\Config' ),
	'dataStore'          => DI\factory( function ( \Interop\Container\ContainerInterface $container ) {
		$ds = $container->get( 'IronBound\WP_API_Idempotence\DataStore\DataStore' );

		if ( $ds instanceof \IronBound\WP_API_Idempotence\DataStore\Configurable ) {
			$ds->configure( $container->get( 'config' ) );
		}

		return $ds;
	} ),
	'requestHasher'      => DI\object( 'IronBound\WP_API_Idempotence\RequestHasher\Simple' ),
	'responseSerializer' => DI\object( 'IronBound\WP_API_Idempotence\ResponseSerializer\JSON' ),

	'formCache' => DI\factory( function () { return get_temp_dir() . '/wp-api-idempotence-form-cache/'; } ),

	'twig.options' => [
		//'cache' => get_temp_dir() . '/wp-api-idempotence-twig-cache/'
	],

	'twig.paths' => __DIR__ . '/twig/',

	'IronBound\WP_API_Idempotence\RequestHasher\RequestHasher' =>
		DI\object( 'IronBound\WP_API_Idempotence\RequestHasher\Cached' )
			->constructor( DI\link( 'requestHasher' ) ),

	'IronBound\WP_API_Idempotence\RequestPoller\RequestPoller' =>
		DI\object( 'IronBound\WP_API_Idempotence\RequestPoller\Sleep' )
			->constructor( DI\link( 'poll.sleepSeconds' ), DI\link( 'poll.maxQueries' ) ),

	'IronBound\WP_API_Idempotence\ResponseSerializer\ResponseSerializer' =>
		DI\factory( function ( \Interop\Container\ContainerInterface $container ) {
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
		return \IronBound\WP_API_Idempotence\Config::from_settings( get_option( 'wp_api_idempotence' ) ?: [] );
	} ),

	'Gregwar\Formidable\Factory' =>
		DI\object( 'Gregwar\Formidable\Factory' )
			->method( 'registerType', 'nonce', 'IronBound\WP_API_Idempotence\Helpers\NonceField' )
			->method( 'setLanguage', DI\link( 'Gregwar\Formidable\Language\Language' ) ),

	'Gregwar\Formidable\Language\Language' =>
		DI\object( 'IronBound\WP_API_Idempotence\Helpers\gettextLanguage' ),

	'Gregwar\Cache\Cache' =>
		DI\object( 'Gregwar\Cache\Cache' )
			->constructor( DI\link( 'formCache' ) ),


	'BrightNucleus\Config\ConfigInterface' => DI\factory( function () {
		return \BrightNucleus\Config\ConfigFactory::createSubConfig(
			__DIR__ . '/assets.php', 'IronBound\WP_API_Idempotence'
		);
	} ),

	'dependencyConfig' => DI\factory( function ( \Interop\Container\ContainerInterface $container ) {
		/** @var \BrightNucleus\Config\ConfigInterface $config */
		$config = $container->get( 'BrightNucleus\Config\ConfigInterface' );

		return $config->getSubConfig( 'DependencyManager' );
	} ),

	'BrightNucleus\Dependency\DependencyManagerInterface' =>
		DI\object( '\BrightNucleus\Dependency\DependencyManager' )
			->constructor( DI\link( 'dependencyConfig' ) ),

	'Twig_LoaderInterface' => DI\object( 'Twig_Loader_Filesystem' )
		->constructor( DI\link( 'twig.paths' ) ),

	'Twig_Environment' =>
		DI\object( 'Twig_Environment' )
			->constructor( DI\link( 'Twig_LoaderInterface' ), DI\link( 'twig.options' ) )
];