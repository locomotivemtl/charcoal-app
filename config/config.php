<?php
return [

	'logger'=>[
		'level'=>'debug'
	],

	'view'=>[
		'path'=>[
			'../templates/'
		],
        'default_template'=>'default',
		'default_engine'=>'mustache'
	],

	'routes' => [
		'templates' => [
			'default' => [
				'ident'=>'default'
			],
			'example' => [
				'ident'=>'example',
				'controller'=>null,
				'methods'=>['GET'],
				'group'=>null,
				'template'=>'example',
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

