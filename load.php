<?php
/**
 * Load the plugin.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

require_once __DIR__ . '/vendor/autoload.php';

$builder = new \DI\ContainerBuilder();
$builder->useAutowiring( true );
$builder->useAnnotations( false );
$builder->setDefinitionCache( new \Doctrine\Common\Cache\ArrayCache() );
$builder->addDefinitions( __DIR__ . '/config.php' );

/**
 * Fires when a container builder is finished being initialized.
 *
 * @since 1.0.0
 *
 * @param \DI\ContainerBuilder $builder
 */
do_action( 'wp_api_idempotence_initialize_container_builder', $builder );

$container = $builder->build();

$data_store = $container->get( 'dataStore' );

if ( $data_store instanceof \IronBound\WP_API_Idempotence\DataStore\Configurable ) {
	$data_store->configure( $container->get( 'config' ) );
}

/** @var \IronBound\WP_API_Idempotence\Middleware $middleware */
$middleware = $container->make( '\IronBound\WP_API_Idempotence\Middleware' );
$middleware->filters();

register_activation_hook( WP_API_IDEMPOTENCE_FILE, function () use ( $data_store ) {
	if ( $data_store instanceof \IronBound\WP_API_Idempotence\DataStore\Installable ) {
		$data_store->install();
	}

	wp_schedule_event( time(), 'daily', 'wp_api_idempotence_flush_logs' );
} );

add_action( 'wp_api_idempotence_flush_logs', function () use ( $data_store ) {
	$data_store->drop_old();
} );