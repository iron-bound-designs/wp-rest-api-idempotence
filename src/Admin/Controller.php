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
		return $this->view->render( [
			'subviews' => $this->subviews,
		] );
	}

	/**
	 * @inheritDoc
	 */
	public function __toString() {
		return $this();
	}
}