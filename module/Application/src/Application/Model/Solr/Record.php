<?php

namespace Application\Model\Solr;

use Xerxes\Record\Bibliographic;

/**
 * Extract properties for books, articles, and dissertations from SolrMarc implementation
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: Record.php 1961 2011-10-28 17:42:57Z dwalker.calstate@gmail.com $
 * @package Xerxes
 */

class Record extends Bibliographic
{
	protected $source = "solr";
	protected $record_id;
	
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
			$items = $this->datafield($item_field);
			
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
		$node = $this->document->getElementsByTagName("record" )->item( 0 );
		
		$string = $this->document->saveXML($node);
		
		if ( $bolString == true )
		{
			return $string;
		}
		else
		{
			$xml = new \DOMDocument();
			$xml->loadXML($string);
			return $xml;
		}		
	}
}
