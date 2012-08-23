<?php

namespace Application\Model\Search;

use Xerxes\Utility\Email;

use Application\Model\Bx\Engine as BxEngine,
	Application\Model\Search\Availability\AvailabilityFactory,
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
		$this->sid = $this->config->getConfig("APPLICATION_SID", false, $this->registry->getConfig("APPLICATION_SID", false, "calstate.edu:xerxes"));
		
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
	 * Include original record in object
	 */
	
	public function includeOriginalRecord()
	{
		$this->original_record = $this->xerxes_record->getOriginalXML();
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
		
		$type = $this->config->getConfig("LOOKUP"); // availability look-up type
		
		// mark that we've checked holdings either way
		
		$this->holdings->checked = true;
		
		// no holdings source defined or somehow id's are blank
		
		if ( $xerxes_record->hasPhysicalHoldings() == false || $type == "" || $id == "" )
		{
			return $this;
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
		
		$cache->set($this->getCacheId(), serialize($this->holdings), $expiry);
		
		return $this;
	}
	
	/**
	 * Send a text message of this record to carrier using email gateway 
	 * 
	 * @param unknown_type $item_number
	 */
	
	public function textLocationTo($email, $item_number)
	{
		if ( $this->holdings->length() == 0 )
		{
			$this->fetchHoldings();
			
			// nothing here to text!
			
			if ( $this->holdings->length() == 0 )
			{
				return $this;
			}
		}
		
		// title
		
		$title = $this->getXerxesRecord()->getTitle();
		
		// item info
		
		$item = $this->holdings->getItems($item_number);
		
		$item_message = $item->location . " " . $item->callnumber;
		
		// make sure we don't go over sms size limit
		
		$title_length = strlen($title);
		$item_length = strlen($item_message);
		$total_length = $title_length + $item_length;
			
		if ( $total_length > 150 )
		{
			$title = substr($title,0,$total_length - $item_length - 6) . "...";
		}
		
		$body = $title . " / " . $item_message;
		
		$email_client = new Email();
		$email_client->send($email, 'library', $body);
		
		return $this;
	}
	
	/**
	 * Canonical cache id to identify this record
	 * 
	 * @return string
	 */
	
	protected function getCacheId()
	{
		$xerxes_record = $this->getXerxesRecord();
		
		return $xerxes_record->getSource() . "." . $xerxes_record->getRecordID();
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
