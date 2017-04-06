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
 * Class FormView
 *
 * @package IronBound\WP_API_Idempotence\Admin
 */
class FormController extends Controller {

	/** @var Form */
	private $form;

	/** @var array */
	private $security;

	/** @var callable */
	private $save;

	/**
	 * @inheritDoc
	 */
	public function __construct( \Twig_TemplateWrapper $view, $subviews, Form $form, array $security, callable $save ) {
		parent::__construct( $view, $subviews );

		$this->form     = $form;
		$this->security = $security;
		$this->save     = $save;
	}

	/**
	 * @inheritDoc
	 */
	public function __invoke() {

		$notices = [];

		$this->form->handle( function () use ( &$notices ) {

			$values   = $this->form->getValues();
			$security = $this->security;

			if ( isset( $security['nonceField'] ) ) {
				unset( $values[ $security['nonceField'] ] );
			}

			if ( isset( $security['capability'] ) && ! current_user_can( $security['capability'] ) ) {
				$notices['errors'][] = __( "You don't have permission to save this form.", 'wp-api-idempotence' );

				return;
			}

			call_user_func( $this->save, $values );

			$notices['success'][] = __( 'Saved', 'wp-api-idempotence' );
		}, function ( $errors = [] ) use ( &$notices ) {
			$notices['errors'] = $errors;
		} );

		return $this->view->render( [
			'form'    => $this->form,
			'notices' => $notices,
		] );
	}
}