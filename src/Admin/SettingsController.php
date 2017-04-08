<?php
/**
 * Settings controller.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\Admin;

use IronBound\WP_API_Idempotence\Config;

/**
 * Class SettingsController
 *
 * @package IronBound\WP_API_Idempotence\Admin
 */
class SettingsController extends Controller {

	/** @var Config */
	private $config;

	/**
	 * @inheritDoc
	 */
	public function __construct( Config $config, \Twig_TemplateWrapper $view, array $subviews = [] ) {
		parent::__construct( $view, $subviews );

		$this->config = $config;
	}

	/**
	 * @inheritDoc
	 */
	protected function make_twig_context() {

		$json = [
			'title'   => 'My Important Post',
			'content' => 'This will only go out once!',
			'status'  => 'draft',
		];

		if ( $this->config->get_key_location() === 'body' ) {
			$json[ $this->config->get_key_name() ] = wp_generate_uuid4();
		}

		return array_merge( parent::make_twig_context(), [
			'postsEndpoint' => rest_url( 'wp/v2/posts' ),
			'key'           => [
				'location' => $this->config->get_key_location(),
				'name'     => $this->config->get_key_name(),
			],
			'uuid'          => wp_generate_uuid4(),
			'requestJson'   => wp_json_encode( $json, JSON_PRETTY_PRINT )
		] );
	}
}