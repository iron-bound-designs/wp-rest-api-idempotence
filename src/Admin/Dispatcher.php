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
				return $this->view_factory->make( $name, $config );
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
	 * @param callable $make_controller
	 */
	protected function register_menu( $name, array $config, callable $make_controller ) {

		add_action( 'admin_menu', function () use ( $name, $config, $make_controller ) {

			$render = function () use ( $make_controller ) {
				$controller = $make_controller();
				$this->handle_pre_eval( $controller );
				echo $controller;
			};

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

	/**
	 * Call all pre-eval functions before rendering.
	 *
	 * @since 1.0.0
	 *
	 * @param Controller $controller
	 */
	protected function handle_pre_eval( Controller $controller ) {

		if ( $controller instanceof WithPreEval ) {
			$controller->pre_eval();
		}

		foreach ( $controller->get_subviews() as $subview ) {
			$this->handle_pre_eval( $subview );
		}
	}
}