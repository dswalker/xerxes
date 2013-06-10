<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Metalib;

use Xerxes;

/**
 * Extract properties for books, articles, and dissertations from Metalib
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Record extends Xerxes\Record\Bibliographic
{
	protected $metalib_id;
	protected $result_set;
	protected $record_number;
	
	/**
	 * Create new Metalib Record
	 * @param \Xerxes_MetalibRecord $record
	 */
	
	public function __construct(\Xerxes_MetalibRecord $record)
	{
		parent::__construct();
		
		// inspect the metalib record
		
		$metalib_reflect = new \ReflectionClass($record);
		$metalib_properties = $metalib_reflect->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);
		
		// take it's properties and convert them into updated x2 equivalent
		
		foreach ( $metalib_properties as $metalib_prop )
		{
			$metalib_prop->setAccessible(true);
		
			$name = $metalib_prop->getName();
			$value = $metalib_prop->getValue($record);
			
			// objects
			
			if ( $name == 'authors')
			{
				foreach ( $value as $metalib_author )
				{
					$author = new Xerxes\Record\Author();
					$author->first_name = $metalib_author->first_name;
					$author->last_name = $metalib_author->last_name;
					$author->init = $metalib_author->init;
					$author->name = $metalib_author->name;
					$author->type = $metalib_author->type;
					$author->additional = $metalib_author->additional;
					$author->display = $metalib_author->display;
					
					$this->authors[] = $author;
				}
			}
			elseif ( $name == 'subjects' )
			{
				foreach ( $value as $metalib_subject )
				{
					$subject = new Xerxes\Record\Subject();
					$subject->display = $metalib_subject->display;
					$subject->value = $metalib_subject->value;
				}
			}
			elseif ( $name == 'format' )
			{
				$this->format = new Xerxes\Record\Format();
				$this->format->determineFormat($value);
			}
			elseif ( $name == 'links' )
			{
				foreach ( $value as $metalib_link_array )
				{
					$display = $metalib_link_array[0];
					$type = $metalib_link_array[2];
					
					// url handling
					
					$url = '?base=databases&action=proxy&database=' . $this->metalib_id;
					
					// link template
					
					if ( is_array($metalib_link_array[1]) )
					{
						foreach ( $metalib_link_array[1] as $key => $value )
						{
							$url .= '&param=' . urlencode("$key=$value");
						}	
					}
					else // regular link
					{
						$url .= '&url=' . urlencode($metalib_link_array[1]);
					}
					
					switch ($type)
					{
						case 'original_record': $type = Xerxes\Record\Link::ORIGINAL_RECORD; break;
						case 'pdf': $type = Xerxes\Record\Link::PDF; break;
						case 'html': $type = Xerxes\Record\Link::HTML; break;
						case 'none': $type = Xerxes\Record\Link::NONE; break;
						case 'online': $type = Xerxes\Record\Link::ONLINE; break;
					}
					
					$link = new Xerxes\Record\Link($url);
					$link->setDisplay($display);
					$link->setType($type);
					
					$this->links[] = $link;
				}
			}
			elseif ( $name == 'toc' )
			{
				if ( ! is_array($value) )
				{
					$value = array($value);
				}
				
				foreach ( $value as $metalib_toc )
				{
					$toc = new Xerxes\Record\Chapter();
					$toc->statement = $metalib_toc;
					$this->toc;
				}
			}
			elseif ( $name == 'journal_title_continues' || $name == 'journal_title_continued_by' )
			{
				// ignore for now
			}
			
			// basic data types
			
			elseif ( property_exists($this, $name))
			{
				$this->$name =  $value;
			}
		}
	}
}