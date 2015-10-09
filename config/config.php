<?php
return [
	
	'logger'=>[
		'level'=>'debug'
	],

	'view'=>[
		'path'=>[
			'../templates/'
		],
		'default_engine'=>'mustache'
	],

	'routes' => [
		'templates' => [
			'default' => [
				'ident'=>'default',
				'controller'=>null,
				'methods'=>['GET'],
				'group'=>null,
				'template'=>'default'
			],
			'example' => [
				'ident'=>'example',
				'controller'=>null,
				'methods'=>['GET'],
				'group'=>null,
				'template'=>'default',
				'engine'=>'mustache',
				'cache'=>[
					'active'=>true,
					'key'=>'route/{{ident}}/{{lang}}/'
				],
				'options'=>[]

			]
		],
		'actions' => [
			'default' => [],
			'example' => [
				'ident'=>'example',
				'controller'=>null,
				'methods'=>['POST'],
				'group'=>null
			]
		],
		'scripts' => [
			'example' => [
				'ident'=>'example',
				'controller'=>null,
				'methods'=>['GET'],
				'group'=>null
			]
		]
	]
];

