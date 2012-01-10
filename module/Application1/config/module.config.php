<?php
return array(
	'display_exceptions' => true,
	'di' => array(
		'instance' => array(
			'alias' => array(
				'view'   => 'Xerxes\Utility\ViewRenderer',
				'labels'   => 'Xerxes\Utility\Labels',
			),
			'Xerxes\Utility\ViewRenderer' => array(
				'parameters' => array(
					'script_path' => __DIR__ . '/../views'
				),
			),
			'Xerxes\Utility\Labels' => array(
				'parameters' => array(
					'path' => __DIR__ . '/../views/labels'
				),
			),
		),
	),
	'routes' => array(
		'default' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Segment',
			'options' => array(
				'route'	=> '/[:controller[/:action]]',
				'constraints' => array(
					'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
					'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
				),
				'defaults' => array(
					'controller' => 'index',
					'action' => 'index',
				),
			),
		),
		'home' => array(
			'type' => 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/',
				'defaults' => array(
					'controller' => 'index',
					'action' => 'index',
				),
			),
		),
	),
);