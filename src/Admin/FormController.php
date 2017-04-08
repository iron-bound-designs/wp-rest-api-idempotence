<?php
/**
 * Form View class.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\Admin;

use Gregwar\Formidable\Form;

/**
 * Class FormController
 *
 * @package IronBound\WP_API_Idempotence\Admin
 */
class FormController extends Controller implements WithForm, WithPreEval {

	/** @var Form */
	private $form;

	/** @var array */
	private $config;

	/** @var array */
	private $notices = [];

	/**
	 * @inheritdoc
	 */
	public function set_form( Form $form, array $config ) {
		$this->form   = $form;
		$this->config = $config;
	}

	/**
	 * @inheritDoc
	 */
	public function pre_eval() {

		if ( ! $this->form || ! $this->config ) {
			return;
		}

		$notices = $this->notices;

		$this->form->handle( function () use ( &$notices ) {

			$values   = $this->form->getValues();
			$security = $this->config['security'];

			if ( isset( $security['nonceField'] ) ) {
				unset( $values[ $security['nonceField'] ] );
			}

			if ( isset( $security['capability'] ) && ! current_user_can( $security['capability'] ) ) {
				$notices['errors'][] = __( "You don't have permission to save this form.", 'wp-api-idempotence' );

				return;
			}

			if ( $this->config['storage']['type'] === 'options' ) {
				update_option( $this->config['storage']['option'], $values );
			}

			$notices['success'][] = __( 'Saved', 'wp-api-idempotence' );
		}, function ( $errors = [] ) use ( &$notices ) {
			$notices['errors'] = $errors;
		} );

		$this->notices = $notices;
	}

	/**
	 * @inheritDoc
	 */
	public function __invoke() {

		if ( ! $this->form || ! $this->config ) {
			return '';
		}

		return $this->view->render( array_merge( [ 'notices' => $this->notices ], $this->make_twig_context() ) );
	}

	/**
	 * @inheritDoc
	 */
	protected function make_twig_context() {
		return [
			'form' => $this->form,
		];
	}
}