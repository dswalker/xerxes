<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\DataMap;

use Application\Model\Saved\ReadingList\Result;
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
			$already_assigned_array = $this->getRecordIDs();
			
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
			$already_assigned_array = $this->getRecordIDs();
			
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
	
	public function editRecord(Result $result)
	{
		$sql = "UPDATE xerxes_reading_list SET title= :title, author = :author, publication = :publication, description = :description " . 
			"WHERE record_id = :record_id";
		
		$params = array(
			':title' => $result->title, 
			':author' => $result->author,
			':publication' => $result->publication,
			':description' => $result->description,
			':record_id' => $result->record_id 
		);
		
		return $this->update($sql, $params);
	}	
	
	public function clearRecordData($record_id)
	{
		$sql = "UPDATE xerxes_reading_list SET title = NULL, author = NULL, publication = NULL, description = NULL " .
			"WHERE record_id = :record_id";
	
		$params = array(':record_id' => $record_id);
	
		return $this->update($sql, $params);
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
		if ( $this->hasRecords() )
		{
			// get full reading list record data
			
			$record_data = $this->getRecordData();
			
			// get just the id's
			
			$ids = array();
			
			foreach ( $record_data as $record_data_item )
			{
				$ids[] = $record_data_item->record_id;
			}
			
			// get the full Xerxes records
			
			$records_datamap = new SavedRecords();
			$records = $records_datamap->getRecordsByID($ids);
			
			// print_r($record_data); print_r($records); exit;
			
			// reorder them here -- hacky sack! @todo: find a better way!
			
			$final = array();
			
			foreach ( $record_data as $record_data_item )
			{
				foreach ( $records as $record )
				{
					if ( $record->id == $record_data_item->record_id )
					{
						// substitute any user-supplied data

						if ( $record_data_item->title != "" )
						{
							$user_params = array(
								'title' => $record_data_item->title,
								'sub_title' => '', // nix subtitle and non-sort so user-supplied title 
								'non_sort' => '',  // becomes the full title of the record
								'primary_author' => $record_data_item->author,
								'journal' => $record_data_item->publication,
								'abstract' => $record_data_item->description
							);
							
							$record->xerxes_record->setProperties($user_params);
						}
						
						// add them in this order
						
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
		$ids = array();
		
		$results = $this->getRecordQuery();
			
		foreach ( $results as $result )
		{
			$ids[] = $result['record_id'];
		}
			
		return $ids;
	}
	
	protected function getRecordData()
	{
		$ids = array();
		
		$results = $this->getRecordQuery();
			
		foreach ( $results as $result )
		{		
			$ids[] = new Result($result);
		}
		
		return $ids;
	}
	
	protected function getRecordQuery()
	{
		$sql = "SELECT * FROM xerxes_reading_list WHERE context_id = :context_id ORDER BY record_order";
		return $this->select( $sql, array (":context_id" => $this->id) );
	}
}
