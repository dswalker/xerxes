<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Saved;

use Application\Model\Search;
use Application\Model\Metalib;
use Xerxes;
use Xerxes\Utility\DataValue;

/**
 * Saved Result
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Result extends Search\Result
{
	public $id;
	public $source;
	public $original_id;
	public $timestamp;
	public $username;
	public $tags = array();
	
	public function __construct($record, Config $config)
	{
		// old metalib record from xerxes 1
		
		if ( $record instanceof \Xerxes_MetalibRecord )
		{
			$record = new Metalib\Record($record); // convert it x2 record
		}
		
		// record from xerxes 1 to xerxes 2 transition (only at cal state)
		
		elseif ( $record instanceof \Xerxes_TransRecord )
		{
			$record = $record->record(); // extract the x2 record
		}
		
		if ( ! $record instanceof Xerxes\Record )
		{
			$record = new Xerxes\Record();
			$record->setProperties(array('title' => '[Sorry, this record is corrupted]'));
		}
		
		parent::__construct($record, $config);
	}
}
