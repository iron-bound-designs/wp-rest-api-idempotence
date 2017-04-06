<?php
/**
 * Formidable language file using gettext.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\Helpers;

use Gregwar\Formidable\Language\Language;

/**
 * Class gettextLanguage
 *
 * @package IronBound\WP_API_Idempotence\Helpers
 */
class gettextLanguage extends Language {

	/**
	 * @inheritDoc
	 */
	public function __construct() {
		$this->messages = [
			'read_only' => __( 'The field %s is read-only and should not be changed', 'wp-api-idempotence' ),
			'value_required' => __( 'You should enter a value for the field %s', 'wp-api-idempotence' ),
			'bad_format' => __( 'Format of the field %s is not correct', 'wp-api-idempotence' ),
			'at_least' => __( 'The field %s should be at least %d characters long', 'wp-api-idempotence' ),
			'not_more' => __( 'The field %s should not be longer than %d characters', 'wp-api-idempotence' ),
			'bad_email' => __( 'The field %s should be a valid e-mail address', 'wp-api-idempotence' ),
			'bad_captcha' => __( 'The captcha value is not correct', 'wp-api-idempotence' ),
			'bad_date' => __( 'The date %s is not correct', 'wp-api-idempotence' ),
			'add' => __( 'Add', 'wp-api-idempotence' ),
			'remove' => __( 'Remove', 'wp-api-idempotence' ),
			'file_size_too_big' => __( 'File size for the field %s should not exceed %s', 'wp-api-idempotence' ),
			'file_image' => __( 'File for the field %s should be an image', 'wp-api-idempotence' ),
			'file_required' => __( 'You should send a file for the field %s', 'wp-api-idempotence' ),
			'integer' => __( 'Field %s should be an integer', 'wp-api-idempotence' ),
			'should_check' => __( 'You should check a box for %s', 'wp-api-idempotence' ),
			'number' => __( 'Field %s should be a number', 'wp-api-idempotence' ),
			'number_min' => __( 'Field %s should be at least equal to %s', 'wp-api-idempotence' ),
			'number_max' => __( 'Field %s should not be bigger than %s', 'wp-api-idempotence' ),
			'number_step' => __( 'Field %s should be a multiple of %f', 'wp-api-idempotence' ),
			'should_choose' => __( 'You should choose a field for %s', 'wp-api-idempotence' ),
			'multiple_min' => __( 'You should at least provide %d entries for %s', 'wp-api-idempotence' ),
			'multiple_mmax' => __( 'You should not provide more than %d entries for %s', 'wp-api-idempotence' ),
			'bad_array_value' => __( 'The value for fields %s is not correct', 'wp-api-idempotence' ),
		];
	}
}