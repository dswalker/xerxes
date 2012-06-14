<?php

namespace Application\Model\DataMap;

use Xerxes\Utility\DataMap,
	Xerxes\Record,
	Application\Model\Saved\Record as SavedRecord,
	Application\Model\Saved\Record\Format,
	Application\Model\Saved\Record\Tag,
	Application\Model\Summon;

/**
 * Database access mapper for saved records
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class SavedRecords extends DataMap
{
	/**
	 * Get the total number of saved records for the user
	 *
	 * @param string $strUsername	username under which records are saved
	 * @param string $strLabel		[optional] limit count to a specific tag label
	 * @param string $strFormat		[optional] limit count to a specific format
	 * @return int					number of saved records
	 */
	
	public function totalRecords($strUsername, $strLabel = null, $strFormat = null)
	{
		$arrParams = array();
		
		// labels are little different, since we need to make sure they
		// include the tags table 

		if ( $strLabel != null )
		{
			$strSQL = "SELECT count(*) as total FROM xerxes_records, xerxes_tags " . 
				" WHERE xerxes_tags.record_id = xerxes_records.id AND xerxes_records.username = :user " . 
				" AND xerxes_tags.tag = :tag";
			
			$arrParams[":user"] = $strUsername;
			$arrParams[":tag"] = $strLabel;
		} 
		else
		{
			// faster to get all or format-specific group just from the records table

			$strSQL = "SELECT count(*) as total FROM xerxes_records WHERE username = :user";
			$arrParams[":user"] = $strUsername;
			
			if ( $strFormat != null )
			{
				$strSQL .= " AND ( format = :format ) ";
				$arrParams[":format"] = $strFormat;
			}
		}
		
		$arrResults = $this->select( $strSQL, $arrParams );
		
		return ( int ) $arrResults[0]["total"];
	}
	
	/**
	 * Reassign records previously saved under a temporary (or old) username to a new one
	 *
	 * @param string $old			old username
	 * @param string $new			new username
	 * @return int status
	 */
	
	public function reassignRecords($old, $new)
	{
		$strSQL = "UPDATE xerxes_records SET username = :new WHERE username = :old";
		
		return $this->update( $strSQL, array (":old" => $old, ":new" => $new ) );
	}
	
	/**
	 * Get user's saved records 
	 *
	 * @param string $strUsername		[optional] username under which the records are saved
	 * @param string $strView			[optional] 'brief' or 'full', defaults to 'full'.
	 * @param string $strOrder			[optional] sort order of the results" 'year', 'author' or 'title', defaults to date added (desc)
	 * @param int $iStart				[optional] offset to start from, defaults to 1, unless $arrID specified
	 * @param int $iCount				[optional] number of records to return, defaults to all, unless $arrID specified
	 * 
	 * @return array					array of Record objects
	 */	
	
	public function getRecords($strUsername, $strView = null, $strOrder = null, $iStart = 1, $iCount = 20)
	{
		return $this->returnRecords( $strUsername, $strView, null, $strOrder, $iStart, $iCount );
	}
	
	/**
	 * Get user's saved records by label
	 *
	 * @param string $strUsername		[optional] username under which the records are saved
	 * @param string $strLabel			[optiional] limit record to specific tag
	 * @param string $strOrder			[optional] sort order of the results" 'year', 'author' or 'title', defaults to date added (desc)
	 * @param int $iStart				[optional] offset to start from, defaults to 1, unless $arrID specified
	 * @param int $iCount				[optional] number of records to return, defaults to all, unless $arrID specified
	 * 
	 * @return array					array of Record objects
	 */		
	
	public function getRecordsByLabel($strUsername = null, $strLabel, $strOrder = null, $iStart = 1, $iCount = null)
	{
		return $this->returnRecords( $strUsername, null, null, $strOrder, $iStart, $iCount, null, $strLabel );
	}
	
	/**
	 * Get user's saved records by format
	 *
	 * @param string $strUsername		[optional] username under which the records are saved
	 * @param string $strFormat			[optional] limit records to specific format
	 * @param string $strOrder			[optional] sort order of the results" 'year', 'author' or 'title', defaults to date added (desc)
	 * @param int $iStart				[optional] offset to start from, defaults to 1, unless $arrID specified
	 * @param int $iCount				[optional] number of records to return, defaults to all, unless $arrID specified
	 * 
	 * @return array					array of Record objects
	 */			
	
	public function getRecordsByFormat($strUsername = null, $strFormat, $strOrder = null, $iStart = 1, $iCount = null)
	{
		return $this->returnRecords( $strUsername, null, null, $strOrder, $iStart, $iCount, $strFormat );
	}
	
	/**
	 * Get a single saved record by (internal) ID
	 *
	 * @param string $strID				Internal ID
	 * 
	 * @return Record
	 */		
	
	public function getRecordByID($strID)
	{
		$arrResults = $this->getRecordsByID( array ($strID ) );
		
		if ( count( $arrResults ) == 0 )
		{
			return null;
		} 
		elseif ( count( $arrResults ) == 1 )
		{
			return $arrResults[0];
		} 
		else
		{
			throw new \Exception( "More than one saved record found for id $strID !" );
		}
	}

	/**
	 * Get saved records by ID's
	 *
	 * @param array $arrID				array of ID's
	 * @param string $strOrder			[optional] sort order of the results" 'year', 'author' or 'title', defaults to date added (desc)
	 * 
	 * @return array					array of Record objects
	 */		
	
	public function getRecordsByID($arrID, $strOrder = null)
	{
		return $this->returnRecords( null, null, $arrID, $strOrder );
	}
	
	/**
	 * Get a set of records from the user's saved records table 
	 *
	 * @param string $strUsername		[optional] username under which the records are saved
	 * @param string $strView			[optional] 'brief' or 'full', defaults to 'full'.
	 * @param string $strOrder			[optional] sort order of the results" 'year', 'author' or 'title', defaults to date added (desc)
	 * @param array $arrID				[optional] array of id values
	 * @param int $iStart				[optional] offset to start from, defaults to 1, unless $arrID specified
	 * @param int $iCount				[optional] number of records to return, defaults to all, unless $arrID specified
	 * @param string $strFormat			[optional] limit records to specific format
	 * @param string $strLabel			[optiional] limit record to specific tag
	 * @return array					array of Record objects
	 */
	
	private function returnRecords($strUsername = null, $strView = "full", $arrID = null, $strOrder = null, $iStart = 1, $iCount = null, $strFormat = null, $strLabel = null)
	{
		// esnure that we don't just end-up with a big database dump

		if ( $arrID == null && $strUsername == null && $iCount == null )
		{
			throw new \Exception( "query must be limited by username, id(s), or record count limit" );
		}
		
		#### construct the query

		$arrParams = array ( ); // sql paramaters
		$strSQL = ""; // main sql query
		$strTable = ""; // tables to include
		$strColumns = ""; // column portion of query
		$strCriteria = ""; // where clause in query
		$strLimit = ""; // record limit and off-set
		$strSort = ""; // sort part query
		

		// set the start record, limit and offset; mysql off-set is zero-based

		if ( $iStart == null )
		{
			$iStart = 1;
		}
		
		$iStart --;
		
		// we'll only apply a limit if there was a count

		if ( $iCount != null )
		{
			$strLimit = " LIMIT $iStart, $iCount ";
		}
		
		// which columns to include -- may not actually use brief any more
		
		$strTable = " xerxes_records ";
		$strColumns = " * ";
		
		if ( $strView == "brief" )
		{
			$strColumns = " xerxes_records.id, xerxes_records.original_id, xerxes_records.source, 
				xerxes_records.username, xerxes_records.nonsort, xerxes_records.title, xerxes_records.author, 
				xerxes_records.format, xerxes_records.year, xerxes_records.refereed ";
		} 
		else
		{
			$strColumns = " xerxes_records.* ";
		}
		
		// limit to a specific user

		if ( $strUsername != "" )
		{
			$strCriteria = " WHERE xerxes_records.username = :username ";
			$arrParams[":username"] = $strUsername;
		} 
		else
		{
			$strCriteria = " WHERE xerxes_records.username LIKE '%' ";
		}
		
		// limit to specific tag

		if ( $strLabel != "" )
		{
			// need to include the xerxes tags table

			$strTable .= ", xerxes_tags ";
			
			// and limit the results to only those where the tag matches!

			$strCriteria .= " AND xerxes_tags.record_id = xerxes_records.id ";
			$strCriteria .= " AND xerxes_tags.tag = :tag ";
			
			$arrParams[":tag"] = $strLabel;
		}
		
		// limit to specific format
		
		if ( $strFormat != "" )
		{
			$strCriteria .= " AND format = :format ";
			$arrParams[":format"] = $strFormat;
		}
		
		// limit to specific records by id

		if ( $arrID != null )
		{
			// make sure we've got an array 
			
			if ( ! is_array( $arrID ) )
			{
				$arrID = array ($arrID );
			}
			
			$strCriteria .= " AND (";
			
			for ( $x = 0 ; $x < count( $arrID ) ; $x ++ )
			{
				if ( $x > 0 )
				{
					$strCriteria .= " OR";
				}
				
				$num = sprintf("%04d", $x); // pad it to keep id's unique for mssql
				
				$strCriteria .= " id = :id$x ";
				$arrParams[":id$x"] = $arrID[$x];
			}
			
			$strCriteria .= ")";
		}
		
		// sort option
		// order by supplied sort criteria otherwise by id
		// to show most recently added first
		
		switch ( $strOrder )
		{
			case "date" :
				$strSort = " ORDER BY year DESC";
				break;
			case "author" :
				$strSort = " ORDER BY author";
				break;
			case "title" :
				$strSort = " ORDER BY title";
				break;
			default :
				$strSort = " ORDER BY id DESC";
				break;
		}
		
		// kind of a funky query, but do it this way to limit to 10 (or whatever) records
		// per page, while joining in as many tags as exist

		$strSQL = "SELECT * FROM 
			(SELECT $strColumns FROM $strTable $strCriteria $strSort $strLimit ) as xerxes_records
			LEFT OUTER JOIN xerxes_tags on xerxes_records.id = xerxes_tags.record_id";

		// ms sql server specific code
		
		$sql_server_clean = null;
		
		if ( $this->rdbms == "mssql")
		{
			// mimicking the MySQL LIMIT clause
			
			$strMSPage = "";
			
			if ( $iCount != null)
			{
				$strMSLimit = $iStart + $iCount;
				$strMSPage = "WHERE row > $iStart and row <= $strMSLimit";
			}
			
			$strSQL = "SELECT * FROM
				( SELECT * FROM ( SELECT $strColumns , ROW_NUMBER() OVER ( $strSort ) as row FROM $strTable $strCriteria ) 
					as tmp $strMSPage ) as xerxes_records 
				LEFT OUTER JOIN xerxes_tags on xerxes_records.id = xerxes_tags.record_id";
				
			$sql_server_clean = array(":username",":tag",":format");
			                        
			for ( $x = 0 ; $x < count( $arrID ) ; $x ++ )
			{
					$num = sprintf("%04d", $x); // pad it to keep id's unique for mssql
			        array_push($sql_server_clean, ":id$num");
			}
		}

		
		#### return the objects
		
		$arrResults = array ( ); // results as array
		$arrRecords = array ( ); // records as array
		
		$arrResults = $this->select( $strSQL, $arrParams, $sql_server_clean );
		
		if ( $arrResults != null )
		{
			$objRecord = new SavedRecord();
			
			foreach ( $arrResults as $arrResult )
			{
				// if the previous row has a different id, then we've come 
				// to a new database, otherwise these are values from the outter join

				if ( $arrResult["id"] != $objRecord->id )
				{
					if ( $objRecord->id != null )
					{
						array_push( $arrRecords, $objRecord );
					}
					
					$objRecord = new SavedRecord();
					$objRecord->load( $arrResult );
					
					if ( array_key_exists( "marc", $arrResult ) )
					{
						if ( $arrResult["record_type"] == "xerxes_record") // new-style saved record
						{
							$objRecord->xerxes_record = unserialize($arrResult["marc"]);
						}
						else // old style @todo: remove this and maybe make a conversion script or something?
						{
							$xerxes_record = new \Application\Model\Metalib\Record();
							$xerxes_record->loadXML( $arrResult["marc"] );
							$objRecord->xerxes_record = $xerxes_record;
						}						
						
						
					}
				}
				
				// if the current row's outter join value is not already stored,
				// then then we've come to a unique value, so add it

				$arrColumns = array ("tag" => "tags" );
				
				foreach ( $arrColumns as $column => $identifier )
				{
					if ( array_key_exists( $column, $arrResult ) )
					{
						if ( ! in_array( $arrResult[$column], $objRecord->$identifier ) )
						{
							array_push( $objRecord->$identifier, $arrResult[$column] );
						}
					}
				}
			}
			
			// get the last one

			array_push( $arrRecords, $objRecord );
		}
		
		return $arrRecords;
	}
	
	/**
	 * Retrive format-based record counts for saved records
	 *
	 * @param string $strUsername		username under which the records are saved
	 * @return array					array of Record_Facet objects
	 */
	
	public function getFormats($strUsername)
	{
		$arrFacets = array ( );
		
		$strSQL = "SELECT format, count(id) as total from xerxes_records WHERE username = :username GROUP BY format ORDER BY format";
		$arrResults = $this->select( $strSQL, array (":username" => $strUsername ) );
		
		foreach ( $arrResults as $arrResult )
		{
			$objRecord = new Format();
			$objRecord->load( $arrResult );
			array_push( $arrFacets, $objRecord );
		}
		
		return $arrFacets;
	}
	
	/**
	 * Retrieve listing and count of labels for saved records
	 *
	 * @param unknown_type $strUsername
	 * @return unknown
	 */
	
	public function getTags($strUsername)
	{
		$arrFacets = array ( );
		
		$strSQL = "SELECT tag as label, count(record_id) as total from xerxes_tags WHERE username = :username GROUP BY tag ORDER BY label";
		$arrResults = $this->select( $strSQL, array (":username" => $strUsername ) );
		
		foreach ( $arrResults as $arrResult )
		{
			$objRecord = new Tag();
			$objRecord->load( $arrResult );
			array_push( $arrFacets, $objRecord );
		}
		
		return $arrFacets;
	}
	
	/**
	 * Associate tags with a saved record
	 *
	 * @param string $strUsername		username 
	 * @param array $arrTags			array of tags supplied by user
	 * @param int $iRecord				record id tags are associated with
	 */
	
	public function assignTags($strUsername, $arrTags, $iRecord)
	{
		// data check

		if ( $strUsername == "" ) throw new \Exception( "param 1 'username' must not be null" );
		if ( ! is_array( $arrTags ) ) throw new \Exception( "param 2 'tags' must be of type array" );
		if ( $iRecord == "" ) throw new \Exception( "param 3 'record' must not be null" );
			
		// wrap it in a transaction, yo!

		$this->beginTransaction();
		
		// first clear any old tags associated with the record, so 
		// we can 'edit' and 'add' on the same action

		$strSQL = "DELETE FROM xerxes_tags WHERE record_id = :record_id AND username = :username";
		$this->delete( $strSQL, array (":record_id" => $iRecord, ":username" => $strUsername ) );
		
		// now assign the new ones to the database
		
		foreach ( $arrTags as $strTag )
		{
			if ( $strTag != "" )
			{
				$strSQL = "INSERT INTO xerxes_tags (username, record_id, tag) VALUES (:username, :record_id, :tag)";
				$this->insert( $strSQL, array (":username" => $strUsername, ":record_id" => $iRecord, ":tag" => $strTag ) );
			}
		}
		
		$this->commit();
	}
	
	/**
	 * Add a record to the user's saved record space. $objXerxesRecord will be
	 * updated with internal db id and original id.. 
	 *
	 * @param string $username					username to save the record under
	 * @param string $source					name of the source database
	 * @param string $id						identifier for the record
	 * @param Xerxes_Record $objXerxesRecord	xerxes record object to save
	 * @return int  inserted id
	 */
	
	public function addRecord($username, $source, $id, Record $objXerxesRecord)
	{
		$arrValues = array ( );
		
		$iYear = ( int ) $objXerxesRecord->getYear();
		$strTitle = $objXerxesRecord->getMainTitle();
		$strSubTitle = $objXerxesRecord->getSubTitle();
		
		if ( $strSubTitle != "" ) $strTitle .= ": " . $strSubTitle;
			
		$strSQL = "INSERT INTO xerxes_records 
			( source, original_id, timestamp, username, nonsort, title, author, year, format, record_type, marc )
			VALUES 
			( :source, :original_id, :timestamp, :username, :nonsort, :title, :author, :year, :format, :record_type, :marc)";
		
		$arrValues[":source"] = $source;
		$arrValues[":original_id"] = $id;
		$arrValues[":timestamp"] = date( "Y-m-d H:i:s" );
		$arrValues[":username"] = $username;
		$arrValues[":nonsort"] = $objXerxesRecord->getNonSort();
		$arrValues[":title"] = substr($strTitle, 0, 90);
		$arrValues[":author"] = $objXerxesRecord->getPrimaryAuthor( true );
		$arrValues[":year"] = $iYear;
		$arrValues[":format"] = $objXerxesRecord->format()->getInternalFormat();
		$arrValues[":marc"] = serialize($objXerxesRecord);		
		$arrValues[":record_type"] = "xerxes_record";
		
		$this->insert( $strSQL, $arrValues );
		
		// get the internal xerxes record id for the saved record, and fill record
		// with it, so caller can use. 
		
		$getIDSql = "SELECT id FROM xerxes_records WHERE original_id = :original_id";
		$getIDParam = array (":original_id" => $id );
		$getIDResults = $this->select( $getIDSql, $getIDParam );
		$objXerxesRecord->id = $getIDResults[0]["id"];
		
		$objXerxesRecord->original_id = $id;
		
		return $objXerxesRecord->id;
	}
	
	/**
	 * Remove a record from the user's saved record space by the source and id
	 *
	 * @param string $username			username under which the record is saved
	 * @param string $source			source from which the record came
	 * @param string $id				id of the record
	 * @return int status
	 */
	
	public function deleteRecordBySource($username, $source, $id)
	{
		$strSQL = "DELETE FROM xerxes_records WHERE username = :username AND source = :source AND original_id = :original_id";
		
		return $this->delete( $strSQL, array (":username" => $username, ":source" => $source, ":original_id" => "$id" ) );
	}
	
	/**
	 * Delete record by the local internal id
	 *
	 * @param string $username			username under which the record is saved
	 * @param int $id					internal id number
	 * @return int status
	 */
	
	public function deleteRecordByID($username, $id)
	{
		$strSQL = "DELETE FROM xerxes_records WHERE username = :username AND id = :id";
		
		return $this->delete( $strSQL, array (":username" => $username, ":original_id" => "$id" ) );
	}
}
