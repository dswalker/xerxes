<?php

$path = dirname(__DIR__);

return array(
		'modules' => array(
				'Application',
		),
		'module_listener_options' => array(
				'config_cache_enabled' => false,
				'cache_dir'            => 'data/cache',
				'module_paths' => array(
						"$path/module",
						"$path/vendor",
				),
		),
);