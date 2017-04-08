<?php
/**
 * Controller that needs to be evaluated before render.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\Admin;

/**
 * Interface WithPreEval
 *
 * @package IronBound\WP_API_Idempotence\Admin
 */
interface WithPreEval {

	/**
	 * Method called to perform checks or functions before all views are rendered.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function pre_eval();
}