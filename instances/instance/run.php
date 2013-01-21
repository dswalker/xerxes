<?php 

use Xerxes\Mvc\FrontController;

// working directory is the instance

chdir(__DIR__);

// reference path to root, two directories up

$root = dirname(dirname(__DIR__));

// composer autoloading

if ( ! include_once("$root/vendor/autoload.php") ) 
{
	throw new \Exception("$root/vendor/autoload.php could not be found. Did you run `php composer.phar install`?");
}

// application config

$config = include "$root/application/config/config.php";

// run the application

FrontController::execute($config);