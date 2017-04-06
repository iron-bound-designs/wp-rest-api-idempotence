<?php
return [
	'IronBound' => [
		'WP_API_Idempotence' => [
			'DependencyManager' => [
				'handlers' => [
					'styles' => 'BrightNucleus\Dependency\StyleHandler',
				],
				'styles'   => [
					[
						'handle' => 'wp-api-idempotence-settings',
						'src'    => plugins_url( 'public/settings.css', __FILE__ ),
					],
				],
			],
		],
	],
];