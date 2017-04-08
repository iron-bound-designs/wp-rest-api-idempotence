<?php
/**
 * todo: File Description
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
interface WithForm {

	/**
	 * Set the form for this controller.
	 *
	 * @since 1.0.0
	 *
	 * @param Form  $form
	 * @param array $config
	 */
	public function set_form( Form $form, array $config );
}