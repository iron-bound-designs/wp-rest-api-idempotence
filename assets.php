<?php
return [
	'IronBound' => [
		'WP_API_Idempotence' => [
			'DependencyManager' => [
				'handlers' => [
					'styles'  => 'BrightNucleus\Dependency\StyleHandler',
					'scripts' => 'BrightNucleus\Dependency\ScriptHandler',
				],
				'styles'   => [
					[
						'handle' => 'wp-api-idempotence-settings',
						'src'    => plugins_url( 'public/settings.css', __FILE__ ),
					],
				],
				'scripts'  => [
					[
						'handle'   => 'wp-api-idempotence-settings',
						'src'      => plugins_url( 'public/settings.js', __FILE__ ),
						'localize' => [
							'name' => 'wpApiIdempotenceSettings',
							'data' => function () {
								return [
									'restRoute' => wp_nonce_url( rest_url( 'wp/v2/posts' ), 'wp_rest' )
								];
							}
						],
					],
				],
			],
		],
	],
];