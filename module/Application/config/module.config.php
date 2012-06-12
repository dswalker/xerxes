<?php

return array(
		'router' => array(
				'routes' => array(
						'default' => array(
								'type'    => 'Zend\Mvc\Router\Http\Segment',
								'options' => array(
										'route'    => '/[:controller[/:action]]',
										'constraints' => array(
												'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
												'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
										),
										'defaults' => array(
												'controller' => 'index',
												'action'     => 'index',
										),
								),
						),
						'home' => array(
								'type' => 'Zend\Mvc\Router\Http\Literal',
								'options' => array(
										'route'    => '/',
										'defaults' => array(
												'controller' => 'index',
												'action'     => 'index',
										),
								),
						),
				),
		),
		'controller' => array(
				'classes' => array(
						'index' => 'Application\Controller\IndexController'
				),
		),
		'view_manager' => array(
				'display_not_found_reason' => true,
				'display_exceptions'       => true,
				'doctype'                  => 'HTML5',
				'not_found_template'       => 'error/404',
				'exception_template'       => 'error/index',
				'template_map' => array(
						'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
						'index/index'   => __DIR__ . '/../view/index/index.phtml',
						'error/404'     => __DIR__ . '/../view/error/404.phtml',
						'error/index'   => __DIR__ . '/../view/error/index.phtml',
				),
				'template_path_stack' => array(
						__DIR__ . '/../view',
				),
		),
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
			'Application\View\Strategy' => array(
				'parameters' => array(
					'view_renderer' => 'Xerxes\Utility\ViewRenderer'
				),
			),
		),
	)
);
