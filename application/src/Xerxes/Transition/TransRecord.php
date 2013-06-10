<?php

class Xerxes_TransRecord
{
	protected $record; // record object
	protected $xml; // xerxes xml
	protected $original; // original data
	protected $open_url;
	
	public function __construct($record, $link_resolver, $sid)
	{
		$this->xml = $record->toXML()->saveXML();
		$this->original = serialize($record); // save it for later
		
		$this->open_url = $record->getOpenURL($link_resolver, $sid);
	}
	
	public function getOpenURL()
	{
		return $this->open_url;
	}
	
	public function toXML()
	{
		$xml = new DOMDocument();
		$xml->loadXML($this->xml);
		
		return $xml;
	}
	
	public function setRecordID($id)
	{
		$this->record()->setRecordID($id);
	}
	
	protected function record()
	{
		if ( ! is_object($this->record) )
		{
			$this->record = unserialize($this->original);
		}
		
		return $this->record;
	}
}

