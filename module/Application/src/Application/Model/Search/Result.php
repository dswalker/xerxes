<?php

namespace Application\Model\Search;

use Application\Model\Search\Availability\AvailabilityFactory;

use Application\Model\Bx\Engine as BxEngine,
	Xerxes\Record,
	Xerxes\Utility\Cache,
	Xerxes\Utility\Parser,
	Xerxes\Utility\Registry,
	Zend\Http\Client;

/**
 * Search Record
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license
 * @version
 * @package Xerxes
 */

class Result
{
	public $url_open; // open url
	public $openurl_kev_co;	 // just the key-encoded-values of the openurl
	public $xerxes_record; // record
	public $original_record; // original xml
	public $holdings; // holdings from an ils
	public $recommendations = array(); // recommendation objects	
	public $reviews; // reviews
	
	protected $registry; // global config
	protected $config; // local config
	protected $sid; // open url sid
	protected $link_resolver; // link resolver
	
	/**
	 * Constructor
	 * 
	 * @param Xerxes_Record $record		record
	 * @param Config $config			local config
	 */
	
	public function __construct(Record $record, Config $config)
	{
		$this->xerxes_record = $record;
		$this->registry = Registry::getInstance();
		$this->config = $config;
		
		// link resolver stuff
		
		$this->link_resolver = $this->config->getConfig("LINK_RESOLVER_ADDRESS", false, $this->registry->getConfig("LINK_RESOLVER_ADDRESS", false));
		$this->sid = $this->registry->getConfig("APPLICATION_SID", false, "calstate.edu:xerxes");
		
		if ( $this->link_resolver != null )
		{
			$this->url_open = $record->getOpenURL($this->link_resolver, $this->sid);
		}
		
		$this->openurl_kev_co = $record->getOpenURL(null, $this->sid);
		
		// holdings
		
		$this->holdings = new Holdings();
		
		if ( $record->hasPhysicalHoldings() == false )
		{
			$this->holdings->checked = true;
		}
	}
	
	/**
	 * Enhance record with bx recommendations
	 */
	
	public function addRecommendations()
	{
		$configToken = $this->registry->getConfig("BX_TOKEN", false);
						
		if ( $configToken != null && $this->link_resolver != null )
		{
			$configBX = $this->registry->getConfig("BX_SERVICE_URL", false);
			$configMinRelevance	= (int) $this->registry->getConfig("BX_MIN_RELEVANCE", false, 0);
			$configMaxRecords = (int) $this->registry->getConfig("BX_MAX_RECORDS", false, 10);
			
			$bx_engine = new BxEngine($configToken, $this->sid, $configBX);
			$bx_records = $bx_engine->getRecommendations($this->xerxes_record, $configMinRelevance, $configMaxRecords);
			
			if ( count($bx_records) > 0 ) // only if there are any records
			{
				foreach ( $bx_records as $bx_record )
				{
					$result = new Result($bx_record, $this->config);
					array_push($this->recommendations, $result);
				}
			}
		}
	}
	
	/**
	 * Add holdings to this result
	 */
	
	public function setHoldings( Holdings $holdings )
	{
		$this->holdings = $holdings;
	}
	
	/**
	 * Return item records
	 * 
	 * @return array of Item
	 */
	
	public function getHoldings()
	{
		return $this->holdings;
	}

	/**
	 * Fetch item and holding records from an ILS for this record
	 */
	
	public function fetchHoldings()
	{
		$xerxes_record = $this->getXerxesRecord();
		
		$id = $xerxes_record->getRecordID(); // id from the record
		$cache_id = $xerxes_record->getSource() . "." . $id; // to identify this in the cache
		
		$type = $this->config->getConfig("LOOKUP"); // availability look-up type
		
		// mark that we've checked holdings either way
		
		$this->holdings->checked = true;
		
		// no holdings source defined or somehow id's are blank
		
		if ( $xerxes_record->hasPhysicalHoldings() == false || $type == "" || $id == "" )
		{
			return null;
		}
		
		// get the data
		
		$availabilty_factory = new AvailabilityFactory();
		$availability = $availabilty_factory->getAvailabilityObject($type);
		
		$this->holdings = $availability->getHoldings($id);
		$this->holdings->checked = true;
		
		// cache it for the future
		
		$cache = new Cache(); // @todo: zend\cache
		
		$expiry = $this->config->getConfig("HOLDINGS_CACHE_EXPIRY", false, 2 * 60 * 60); // expiry set for two hours
		$expiry += time(); 
		
		$cache->set($cache_id, serialize($this->holdings), $expiry);
		
		return null;
	}
	
	/**
	 * Add reviews from Good Reads
	 */
	
	public function addReviews()
	{
		$xerxes_record = $this->getXerxesRecord();
		$isbn = $xerxes_record->getISBN();
		
		$key = $this->registry->getConfig("GOOD_READS_API_KEY", false );
		
		if ( $key != null )
		{
			$url = "http://www.goodreads.com/book/isbn?isbn=$isbn&key=$key";
			
			$data = Parser::request($url, 5);
			
			if ( $data != "" )
			{
				$xml = Parser::convertToDOMDocument($data);
				
				$this->reviews = $xml;
			}
		}
	}
	
	/**
	 * Get the Xerxes_Record object
	 */
	
	public function getXerxesRecord()
	{
		return $this->xerxes_record;
	}
}
