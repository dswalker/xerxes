<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Saved\ReadingList;

use Xerxes\Record;

/**
 * Reading List Result
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Result
{
	/**
	 * @var int
	 */
	public $record_id;

	/**
	 * @var string
	 */
	public $title;
	
	/**
	 * @var string
	 */
	public $author;
	
	/**
	 * @var string
	 */
	public $publication;
	
	/**
	 * @var string
	 */
	public $description;

	/**
	 * Create new Reading List Result
	 * 
	 * @param array $data  [optional] array of property => value's
	 */
	
	public function __construct(array $data = array() )
	{
		foreach ( $data as $key => $value )
		{
			if ( property_exists($this, $key) )
			{
				$this->$key = $value;
			}
		}
	}
	
}
