<?php
/**
 * Form Factory.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2017 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\WP_API_Idempotence\Admin;

use Gregwar\Formidable\Factory;
use Gregwar\Cache\Cache;

/**
 * Class FormFactory
 *
 * @package IronBound\WP_API_Idempotence\Admin
 */
class FormFactory {

	/** @var Factory */
	private $factory;

	/** @var Cache */
	private $cache;

	/**
	 * FormFactory constructor.
	 *
	 * @param Factory $factory
	 * @param Cache   $cache
	 */
	public function __construct( Factory $factory, Cache $cache ) {
		$this->factory = $factory;
		$this->cache   = $cache;
	}

	/**
	 * Make a form object.
	 *
	 * @since 2.0.0
	 *
	 * @param string $path
	 *
	 * @return \Gregwar\Formidable\Form
	 */
	public function make( $path ) {
		return new \Gregwar\Formidable\Form(
			$path,
			null,
			$this->cache,
			$this->factory
		);
	}
}