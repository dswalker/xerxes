<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */ 

use Xerxes\Mvc\Bootstrap;
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

// bootstrap

$bootstrap = new Bootstrap();

// run the application

FrontController::execute($bootstrap);