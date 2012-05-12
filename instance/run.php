<?php

chdir(__DIR__);

$path = dirname(__DIR__);

require_once "$path/vendor/ZendFramework/library/Zend/Loader/AutoloaderFactory.php";
Zend\Loader\AutoloaderFactory::factory();

$appConfig = include "$path/config/application.config.php";

$listenerOptions  = new Zend\Module\Listener\ListenerOptions($appConfig['module_listener_options']);
$defaultListeners = new Zend\Module\Listener\DefaultListenerAggregate($listenerOptions);
$defaultListeners->getConfigListener()->addConfigGlobPath("$path/config/autoload/*.config.php");

$moduleManager = new Zend\Module\Manager($appConfig['modules']);
$moduleManager->events()->attachAggregate($defaultListeners);
$moduleManager->loadModules();

// Create application, bootstrap, and run
$bootstrap   = new Zend\Mvc\Bootstrap($defaultListeners->getConfigListener()->getMergedConfig());
$application = new Zend\Mvc\Application;
$bootstrap->bootstrap($application);
$application->run()->send();