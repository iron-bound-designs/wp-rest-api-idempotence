<?php
return [
	'settings' => [
		'menu'     => [
			'submenu'    => 'options-general.php',
			'title'      => 'WP API Idempotence',
			'capability' => 'manage_options',
		],
		'path'     => 'settings.twig',
		'type'     => 'html',
		'class'    => 'IronBound\WP_API_Idempotence\Admin\SettingsController',
		'subviews' => [
			'form' => [
				'type' => 'form',
				'path' => 'settings-form.twig',
				'form' => [
					'path'     => __DIR__ . '/forms/settings.php',
					'sources'  => [
						'applicable_methods' => [
							'GET'    => 'GET',
							'POST'   => 'POST',
							'PUT'    => 'PUT',
							'PATCH'  => 'PATCH',
							'DELETE' => 'DELETE',
						],
						'key_locations'      => [
							'header' => __( 'Request Header', 'wp-rest-api-idempotence' ),
							'body'   => __( 'Request Body', 'wp-rest-api-idempotence' ),
						],
					],
					'storage'  => [
						'type'   => 'options',
						'option' => 'wp_api_idempotence'
					],
					'security' => [
						'nonceField' => 'nonce',
						'capability' => 'manage_options',
					]
				]
			]
		]
	]
];