<?php

$path = dirname(__DIR__);

return array(
		'modules' => array(
				'Application',
		),
		'module_listener_options' => array(
				'config_glob_paths'    => array(
						"$path/config/autoload/{,*.}{global,local}.php",
				),
				'config_cache_enabled' => false,
				'cache_dir'            => 'data/cache',
				'module_paths' => array(
						"$path/module",
						"$path/vendor",
				),
		),
		'service_manager' => array(
				'use_defaults' => true,
				'factories'    => array(
				),
		),
);