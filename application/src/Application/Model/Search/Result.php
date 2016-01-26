<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Search;

use Application\Model\Bx\Engine as BxEngine;
use Application\Model\Availability\AvailabilityFactory;
use Xerxes\Record;
use Xerxes\Utility\Cache;
use Xerxes\Utility\Email;
use Xerxes\Utility\Parser;
use Xerxes\Utility\Registry;

/**
 * Search Record
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Result
{
	/**
	 * @var string
	 */
	
	public $url_open;
	
	/**
	 * just the key-encoded-values of the openurl
	 * 
	 * @var string
	 */
	
	public $openurl_kev_co;
	
	/**
	 * @var Record
	 */
	
	public $xerxes_record;
	
	/**
	 * @var \DOMDocument
	 */
	
	public $original_record;
	
	/**
	 * @var Holdings
	 */
	
	public $holdings;
	
	/**
	 * @var BxRecords[]
	 */
	
	public $recommendations = array();
	
	/**
	 * @var \DOMDocument
	 */	
	
	public $reviews;
	
	/**
	 * @var Registry
	 */
	
	protected $registry;
	
	/**
	 * @var Config
	 */
	
	protected $config;
	
	/**
	 * OpenURL sid
	 * 
	 * @var string
	 */
	
	protected $sid;
	
	/**
	 * @var string
	 */
	
	protected $link_resolver;
	
	/**
	 * Constructor
	 * 
	 * @param Record $record
	 * @param Config $config
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
		
		// proxy links?
		
		$proxy_server = $this->registry->getConfig('PROXY_SERVER', false );
		$should_proxy_links = $this->config->getConfig('SHOULD_PROXY', false, false );
		
		if ( $should_proxy_links )
		{
			foreach ( $this->xerxes_record->getLinks() as $link )
			{
				$link->addProxyPrefix($proxy_server);
			}
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
	 * 
	 * @param Holdings $holdings
	 */
	
	public function setHoldings( Holdings $holdings )
	{
		$this->holdings = $holdings;
	}
	
	/**
	 * Return holdings
	 * 
	 * @return Holdings
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
		
		
		// items not to cache
		
		$no_cache = $this->config->getConfig('LOCATIONS_NO_CACHE', false);
		
		if ( $no_cache != '' && $no_cache instanceof \SimpleXMLElement )
		{
			$locations = array();
			
			foreach ( $no_cache->location as $location )
			{
				$locations[] = (string) $location;
			}
			
			foreach ( $this->holdings->getItems() as $item )
			{
				if (in_array($item->location, $locations) )
				{
					return $this;
				}
			}
		}
		
		// cache it for the future
		
		$cache = new Cache();
		
		$expiry = $this->config->getConfig("HOLDINGS_CACHE_EXPIRY", false, 30 * 60); // expiry set for 30 mins
		$expiry += time(); 
		
		$cache->set($this->getCacheId(), $this->holdings, $expiry);
		
		return $this;
	}
	
	/**
	 * Send a text message of this record to carrier using email gateway
	 * 
	 * @param string $phone
	 * @param string $provider
	 * @param int $item_number
	 * @throws \Exception
	 */
	
	public function textLocationTo($phone, $provider, $item_number)
	{
		$phone = preg_replace('/\D/', "", $phone);
			
		// did we get 10?
			
		if ( strlen($phone) != 10 )
		{
			throw new \Exception("Please enter a 10 digit phone number, including area code");
		}
		
		$email = $phone . '@' . $provider;		
		
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
		return $email_client->send($email, 'library', $body); // @todo l18n this
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
	 * 
	 * @return Record
	 */
	
	public function getXerxesRecord()
	{
		return $this->xerxes_record;
	}
}
