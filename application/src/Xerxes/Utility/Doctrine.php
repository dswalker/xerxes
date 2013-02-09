<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xerxes\Utility;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

/**
 * Doctrine
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

abstract class Doctrine extends DatabaseConnection
{
	/**
	 * Lazy load the EntityManager
	 * 
	 * @param array $paths    to entities
	 * @return EntityManager
	 */
	
	protected function getEntityManager(array $paths)
	{
		$params = array(
			'pdo' => $this->pdo()
		);
		
		$config = Setup::createAnnotationMetadataConfiguration($paths, true);
		return EntityManager::create($params, $config);		
	}
}
