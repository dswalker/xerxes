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

use Xerxes\Utility\DataMap;
use Application\Model\Search\Fulltext;

/**
 * Database access mapper for sfx institutioanl holdings (google scholar) full-text cache
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Availability extends DataMap
{
	/**
	 * Delete all records from the sfx table
	 */
	
	public function clearFullText()
	{
		$this->delete( "DELETE FROM xerxes_sfx" );
	}
	
	/**
	 * Get a list of journals from the sfx table by issn
	 *
	 * @param mixed $issn		[string or array] ISSN or multiple ISSNs
	 * @return Fulltext[]
	 */
	
	public function getFullText($issn)
	{
		$arrFull = array ( );
		$arrResults = array ( );
		$strSQL = "SELECT * FROM xerxes_sfx WHERE ";
		
		if ( is_array( $issn ) )
		{
			if ( count( $issn ) == 0 ) throw new \Exception( "issn query with no values" );
			
			$x = 1;
			$arrParams = array ( );
			
			foreach ( $issn as $strIssn )
			{
				$strIssn = str_replace( "-", "", $strIssn );
				
				if ( $x == 1 )
				{
					$strSQL .= " issn = :issn$x ";
				} 
				else
				{
					$strSQL .= " OR issn = :issn$x ";
				}
				
				$arrParams["issn$x"] = $strIssn;
				
				$x ++;
			}
			
			$arrResults = $this->select( $strSQL, $arrParams );
		} 
		else
		{
			$issn = str_replace( "-", "", $issn );
			$strSQL .= " issn = :issn";
			$arrResults = $this->select( $strSQL, array (":issn" => $issn ) );
		}
		
		foreach ( $arrResults as $arrResult )
		{
			$objFull = new Fulltext();
			$objFull->load( $arrResult );
			
			array_push( $arrFull, $objFull );
		}
		
		return $arrFull;
	}
	
	/**
	 * Add a Fulltext object to the database
	 *
	 * @param Fulltext $objValueObject
	 * @return int status
	 */
	
	public function addFulltext(Fulltext $objValueObject)
	{
		return $this->doSimpleInsert( "xerxes_sfx", $objValueObject );
	}
}
