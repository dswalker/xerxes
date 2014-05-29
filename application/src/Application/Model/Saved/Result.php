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
use Xerxes\Record\Author;

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
	public $corrupted = false;
	
	public function __construct(Record $record, Config $config)
	{
		$xerxes_record = $record->xerxes_record;
		
		// old metalib record from xerxes 1
		
		if ( $xerxes_record instanceof \Xerxes_MetalibRecord )
		{
			$xerxes_record = new Metalib\Record($xerxes_record); // convert it x2 record
		}
		
		// record from xerxes 1 to xerxes 2 transition (only at cal state)
		
		elseif ( $xerxes_record instanceof \Xerxes_TransRecord )
		{
			try
			{
				$xerxes_record = $xerxes_record->record(); // extract the x2 record
			}
			catch(\Exception $e)
			{
				trigger_error('Xerxes Error (' . $e->getLine() . '): ' . $e->getMessage() );
			}
		}
		
		// record has been corrupted  @todo fix this problem 
		
		if ( ! $xerxes_record instanceof Xerxes\Record )
		{
			$this->corrupted = true; // mark it as such
			
			// make a new record with the data we have on hand in the other
			// saved_records fields
			
			$xerxes_record = new Xerxes\Record();
			
			$title = $record->title;
			
			if ( $record->nonsort != "")
			{
				$title = $record->nonsort . ' ' . $title;
			}
			
			$author = new Author($record->author, null, Author::PERSONAL);

			$format = new \Xerxes\Record\Format();
			$format->setFormat($record->format);
			
			$properties = array(
				'title' => $title,
				'format' => $format,
				'authors' => array($author),
				'year' => $record->year,
			);
			
			$xerxes_record->setProperties($properties);
		}
		
		parent::__construct($xerxes_record, $config);
	}
}
