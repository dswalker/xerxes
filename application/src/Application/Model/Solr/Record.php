<?php

namespace Application\Model\Solr;

use Xerxes\Marc\Record as MarcRecord,
	Xerxes\Record\Bibliographic,
	Xerxes\Utility\Parser;

/**
 * Extract properties for books, articles, and dissertations from SolrMarc implementation
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Record extends Bibliographic
{
	protected $source = "solr";
	protected $record_id;
	
	public function loadXML($doc)
	{
		$id = null;
		$format = null;
		$score = null;
		$xml_data = "";
		
		foreach ( $doc->str as $str )
		{
			// marc record
				
			if ( (string) $str["name"] == 'fullrecord' )
			{
				$marc = trim( (string) $str );
		
				// marc-xml or marc-y marc -- come on, come on, feel it, feel it!
		
				if ( substr($marc, 0, 5) == '<?xml')
				{
					$xml_data = $marc;
				}
				else
				{
					$marc = preg_replace('/#31;/', "\x1F", $marc);
					$marc = preg_replace('/#30;/', "\x1E", $marc);
					 
					$marc_file = new \File_MARC($marc, \File_MARC::SOURCE_STRING);
					$marc_record = $marc_file->next();
					$xml_data = $marc_record->toXML();
				}
			}
				
			// record id
				
			elseif ( (string) $str["name"] == 'id' )
			{
				$id = (string) $str;
			}
		}
		
		// format
		
		foreach ( $doc->arr as $arr )
		{
			if ( $arr["name"] == "format" )
			{
				$format = (string) $arr->str;
			}
		}
		
		// score
		
		foreach ( $doc->float as $float )
		{
			if ( $float["name"] == "score" )
			{
				$score = (string) $float;
			}
		}
		
		// load marc data
		
		$this->marc = new MarcRecord();
		$this->marc->loadXML($xml_data);
		
		// save for later
		
		$this->document = Parser::convertToDOMDocument($doc);
		$this->serialized = $doc->asXml();
		
		// process it
		
		$this->map();
		$this->cleanup();
		
		// add non-marc data
		
		$this->setRecordID($id);
		$this->setScore($score);
		
		$this->format()->setInternalFormat($format);
		$this->format()->setPublicFormat($format);		
	}
	
	public function __sleep()
	{
		return array("serialized");
	}
	
	public function __wakeup()
	{
		$xml = simplexml_load_string($this->serialized);
		$this->__construct();
		$this->loadXML($xml);
	}
	
	public function map()
	{
		parent::map();
		
		// we assume that all records have items
		// ... unless told otherwise
		
		// here we've defined marc fields that contain the physical holdings
		// if the record doesn't have these, then it doesn't have items
		
		$config = Config::getInstance();
		
		$item_field = $config->getConfig("ITEM_FIELD", false);
		$item_query = $config->getConfig("ITEM_FIELD_QUERY", false);
		
		// simple field value
		
		if ( $item_field != null ) 
		{
			$items = $this->marc->datafield($item_field);
			
			// print_r($items);
			
			if ( $items->length() == 0 )
			{
				$this->physical_holdings = false;
			}
		}
		
		// expressed as an xpath query
		
		elseif ( $item_query != null )
		{
			$items = $this->marc->xpath($item_query);
			
			// print_r($items);
			
			if ( $items->length == 0 )
			{
				$this->physical_holdings = false;
			}
		}
	}

	public function getRecordID()
	{
		return $this->record_id;
	}
	
	public function getOpenURL($strResolver, $strReferer = null, $param_delimiter = "&")
	{
		$url = parent::getOpenURL($strResolver, $strReferer, $param_delimiter);
		
		// always ignore dates for journals and books, since catalog is describing
		// the item as a whole, not any specific issue or part
		
		return $url . "&sfx.ignore_date_threshold=1";
	}

	public function getOriginalXML($bolString = false)
	{
		$xml = $this->marc->getMarcXML();
		
		if ( $bolString == true )
		{
			return $xml->saveXML();
		}

		return $xml;
	}
}
