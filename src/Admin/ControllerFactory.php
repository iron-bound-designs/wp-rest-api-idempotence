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

	/**
	 * ViewFactory constructor.
	 *
	 * @param FormFactory $form_factory
	 * @param \Twig_Environment $twig
	 */
	public function __construct( FormFactory $form_factory, \Twig_Environment $twig ) {
		$this->form_factory = $form_factory;
		$this->twig         = $twig;
	}

	/**
	 * Make a view class for a given type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type
	 * @param string $name
	 * @param array  $config
	 *
	 * @return Controller
	 */
	public function make( $type, $name, array $config ) {

		$subviews = [];

		if ( isset( $config['subviews'] ) ) {
			foreach ( $config['subviews'] as $sv_name => $sv_config ) {
				$subviews[ $sv_name ] = $this->make( $sv_config['type'], $sv_name, $sv_config );
			}
		}

		switch ( $type ) {
			case 'form':
				return new FormController(
					$this->twig->load( $config['path'] ),
					$subviews,
					$this->make_form( $name, $config['form'] ),
					$config['form']['security'],
					$config['form']['save']
				);
			case 'html':
			default:
				return new Controller( $this->twig->load( $config['path'] ), $subviews );
		}
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

		$form->setValues( $config['values']() );

		return $form;
	}
}