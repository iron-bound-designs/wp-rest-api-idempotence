<?php
/**
 * View Factory.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\Admin;

use DI\FactoryInterface;

/**
 * Class ViewFactory
 *
 * @package IronBound\WP_API_Idempotence\Admin
 */
class ControllerFactory {

	/** @var FormFactory */
	private $form_factory;

	/** @var \Twig_Environment */
	private $twig;

	/** @var FactoryInterface */
	private $factory;

	private static $class_map = [
		'html' => 'IronBound\WP_API_Idempotence\Admin\Controller',
		'form' => 'IronBound\WP_API_Idempotence\Admin\FormController',
	];

	/**
	 * ViewFactory constructor.
	 *
	 * @param FormFactory       $form_factory
	 * @param \Twig_Environment $twig
	 */
	public function __construct( FormFactory $form_factory, \Twig_Environment $twig, FactoryInterface $factory ) {
		$this->form_factory = $form_factory;
		$this->twig         = $twig;
		$this->factory      = $factory;
	}

	/**
	 * Make a view class for a given type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name
	 * @param array  $config
	 *
	 * @return Controller
	 */
	public function make( $name, array $config ) {

		$subviews = [];

		if ( isset( $config['subviews'] ) ) {
			foreach ( $config['subviews'] as $sv_name => $sv_config ) {
				$subviews[ $sv_name ] = $this->make( $sv_name, $sv_config );
			}
		}

		if ( isset( $config['class'] ) ) {
			$class = $config['class'];
		} elseif ( isset( $config['type'], static::$class_map[ $config['type'] ] ) ) {
			$class = static::$class_map[ $config['type'] ];
		} else {
			$class = static::$class_map['html'];
		}

		$controller = $this->factory->make( $class, [
			'view'     => $this->twig->load( $config['path'] ),
			'subviews' => $subviews
		] );

		if ( $controller instanceof WithForm ) {
			$controller->set_form( $this->make_form( $name, $config['form'] ), $config['form'] );
		}

		return $controller;
	}

	/**
	 * Make a form class for a given config.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name
	 * @param array  $config
	 *
	 * @return \Gregwar\Formidable\Form
	 */
	protected function make_form( $name, array $config ) {

		$form = $this->form_factory->make( $config['path'] );

		if ( isset( $config['sources'] ) ) {
			foreach ( $config['sources'] as $field => $value ) {
				$form->source( $field, $value );
			}
		}

		if ( $config['storage']['type'] === 'options' ) {
			$form->setValues( get_option( $config['storage']['option'], [] ) );
		}

		return $form;
	}
}