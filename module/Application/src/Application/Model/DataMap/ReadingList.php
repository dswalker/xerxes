<?php

namespace Application\Model\DataMap;

use Xerxes\Utility\DataMap;

class ReadingList extends DataMap
{
	protected $id;
	
	public function __construct($id)
	{
		parent::__construct();
		
		$this->id = $id;
	}
	
	public function assignRecords(array $record_array)
	{
		if ( count($record_array) > 0 )
		{
			$already_assigned_array = $this->getCourseRecordIDs();;
			
			$this->beginTransaction();
			
			foreach ( $record_array as $record_id )
			{
				// don't add it if it's already assigned
				
				if ( in_array($record_id, $already_assigned_array ) )
				{
					continue;
				}
					
				$sql = "INSERT INTO xerxes_reading_list (context_id, record_id) VALUES (:context_id, :record_id)";
				$this->insert( $sql, array(":context_id" => $this->id, ":record_id" => $record_id ) );
			}
			
			$this->commit();
		}
	}	
	
	public function reorderRecords(array $reorder_array)
	{
		if ( count($reorder_array) > 0 )
		{
			$already_assigned_array = $this->getCourseRecordIDs();
			
			$this->beginTransaction();
			
			foreach ( $reorder_array as $order => $record_id )
			{
				// sanity check: don't change it if it is not in the list
				
				if ( ! in_array($record_id, $already_assigned_array ) )
				{
					throw new \Exception("reordering record that does not exist in list");
				}
					
				$sql = "UPDATE xerxes_reading_list SET record_order = :order WHERE record_id = :record_id";
				$this->update( $sql, array(":order" => $order, ":record_id" => $record_id ) );
			}
			
			$this->commit();
		}
	}
	
	public function removeRecord($record_id)
	{
		if ( $record_id != "" )
		{
			$sql = "DELETE FROM xerxes_reading_list WHERE context_id = :context_id AND record_id = :record_id";
			return $this->delete( $sql, array (":context_id" => $this->id, "record_id" => $record_id ) );
		}
	}
	
	public function getRecords()
	{
		if ( $this->hasCourseRecords() )
		{
			$ids = $this->getCourseRecordIDs();
			
			$records_datamap = new SavedRecords();
			
			$records = $records_datamap->getRecordsByID($ids);
			
			// reorder them here -- hacky sack! @todo: find a better way!
			
			// print_r($ids); print_r($records); exit;
			
			$final = array();
			
			foreach ( $ids as $id )
			{
				foreach ( $records as $record )
				{
					if ( $record->id == $id )
					{
						$final[] = $record;
						break;
					}
				}
			}
			
			return $final;
		}
	}
	
	public function hasRecords()
	{
		$sql = "SELECT count(record_id) FROM xerxes_reading_list WHERE context_id = :context_id";
		$results = $this->select( $sql, array(":context_id" => $this->id) );
			
		$count = $results[0][0];
			
		if ($count > 0 )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	protected function getRecordIDs()
	{
		$sql = "SELECT record_id FROM xerxes_reading_list WHERE context_id = :context_id ORDER BY record_order";
		$results = $this->select( $sql, array (":context_id" => $this->id) );
			
		$ids = array();
			
		foreach ( $results as $result )
		{
			$ids[] = $result['record_id'];
		}
			
		return $ids;
	}
}
