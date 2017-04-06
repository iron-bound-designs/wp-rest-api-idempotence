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
							'header' => __( 'Request Header', 'wp-api-idempotence' ),
							'body'   => __( 'Request Body', 'wp-api-idempotence' ),
						],
					],
					'values'   => function () {
						$values = get_option( 'wp_api_idempotence', [] );

						$values['applicable_methods'] = array_flip( $values['applicable_methods'] );

						return $values;
					},
					'save'     => function ( $values ) {
						update_option( 'wp_api_idempotence', $values );
					},
					'security' => [
						'nonceField' => 'nonce',
						'capability' => 'manage_options',
					]
				]
			]
		]
	]
];