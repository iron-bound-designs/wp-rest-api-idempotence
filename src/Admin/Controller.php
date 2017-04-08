<?php
/**
 * View controller.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\Admin;

/**
 * Class View
 *
 * @package IronBound\WP_API_Idempotence\Admin
 */
class Controller {

	/** @var \Twig_TemplateWrapper */
	protected $view;

	/** @var Controller[] */
	private $subviews;

	/**
	 * View constructor.
	 *
	 * @param \Twig_TemplateWrapper $view
	 * @param Controller[]          $subviews
	 */
	public function __construct( \Twig_TemplateWrapper $view, array $subviews = [] ) {
		$this->view     = $view;
		$this->subviews = $subviews;
	}

	/**
	 * @inheritDoc
	 */
	public function __invoke() {
		return $this->view->render( $this->make_twig_context() );
	}

	/**
	 * Make context to pass to Twig.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function make_twig_context() {
		return [
			'subviews' => $this->subviews,
		];
	}

	/**
	 * @inheritDoc
	 */
	public function __toString() {
		return $this();
	}

	/**
	 * Get any subviews this controller contains.
	 *
	 * @since 1.0.0
	 *
	 * @return Controller[]
	 */
	public function get_subviews() {
		return $this->subviews;
	}
}