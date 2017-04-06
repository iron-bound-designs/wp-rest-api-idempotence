<?php
/**
 * Controller.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\Admin;

/**
 * Class Controller
 *
 * @package IronBound\WP_API_Idempotence\Admin
 */
class Dispatcher {

	/** @var ControllerFactory */
	private $view_factory;

	/**
	 * Controller constructor.
	 *
	 * @param ControllerFactory $view_factory
	 */
	public function __construct( ControllerFactory $view_factory ) { $this->view_factory = $view_factory; }

	/**
	 * Register multiple views.
	 *
	 * @since 1.0.0
	 *
	 * @param array $views
	 */
	public function register_views( array $views ) {

		foreach ( $views as $name => $view ) {
			$this->register_view( $name, $view );
		}
	}

	/**
	 * Register a single view.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name
	 * @param array  $config
	 */
	public function register_view( $name, array $config ) {
		$this->do_register_view( $name, $config );
	}

	/**
	 * Register a view.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name
	 * @param array  $config
	 */
	protected function do_register_view( $name, array $config ) {
		if ( isset( $config['menu'] ) ) {
			$this->register_menu( $name, $config['menu'], function () use ( $name, $config ) {
				return $this->view_factory->make( $config['type'], $name, $config );
			} );
		}
	}

	/**
	 * Register the menu page with WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @param string   $name
	 * @param array    $config
	 * @param callable $make_view
	 */
	protected function register_menu( $name, array $config, callable $make_view ) {

		add_action( 'admin_menu', function () use ( $name, $config, $make_view ) {

			$render = function () use ( $make_view ) { echo $make_view(); };

			$slug = "wp-api-idempotence-{$name}";

			if ( isset( $config['slug'] ) ) {
				$slug .= "-{$config['slug']}";
			}

			if ( isset( $config['submenu'] ) ) {
				add_submenu_page(
					$config['submenu'],
					$config['title'],
					$config['title'],
					$config['capability'],
					$slug,
					$render
				);
			} else {
				add_menu_page(
					$config['title'],
					$config['title'],
					$config['capability'],
					$slug,
					$render,
					isset( $config['icon'] ) ? $config['icon'] : '',
					isset( $config['position'] ) ? $config['position'] : ''
				);
			}
		} );
	}
}