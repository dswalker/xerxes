<?php

use Zend\ServiceManager\ServiceManager,
Zend\Mvc\Service\ServiceManagerConfiguration;

chdir(__DIR__);

$path = dirname(dirname(__DIR__));

// Composer autoloading
if (!include_once("$path/vendor/autoload.php")) {
	throw new RuntimeException("$path/vendor/autoload.php could not be found. Did you run `php composer.phar install`?");
}

// Get application stack configuration
$configuration = include "$path/config/application.config.php";

// Setup service manager
$serviceManager = new ServiceManager(new ServiceManagerConfiguration($configuration['service_manager']));
$serviceManager->setService('ApplicationConfiguration', $configuration);
$serviceManager->get('ModuleManager')->loadModules();

// Run application
$serviceManager->get('Application')->bootstrap()->run()->send();