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

use Application\Model\Search\Refereed as RefereedValue;
use Xerxes\Utility\DataMap;

/**
 * Database access mapper for peer-reviewed data
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Refereed extends DataMap
{
	/**
	 * Delete all records for refereed journals
	 */
	
	public function flushRefereed()
	{
		$this->delete( "DELETE FROM xerxes_refereed" );
	}
	
	/**
	 * Add a refereed title
	 * 
	 * @param Refereed $objTitle peer reviewed journal object
	 */
	
	public function addRefereed(RefereedValue $title)
	{
		$title->issn = str_replace("-", "", $title->issn);
		$this->doSimpleInsert("xerxes_refereed", $title);
	}
	
	/**
	 * Get all refereed data
	 * 
	 * @return array of Refereed objects
	 */
	
	public function getAllRefereed()
	{
		$arrPeer = array();
		$arrResults = $this->select( "SELECT * FROM xerxes_refereed");
		
		foreach ( $arrResults as $arrResult )
		{
			$objPeer = new Refereed();
			$objPeer->load( $arrResult );
			
			array_push( $arrPeer, $objPeer );
		}		
		
		return $arrPeer;
	}
	
	/**
	 * Get a list of journals from the refereed table
	 *
	 * @param mixed $issn		[string or array] ISSN or multiple ISSNs
	 * @return array			array of Refereed objects
	 */
	
	public function getRefereed($issn)
	{
		$arrPeer = array ( );
		$arrResults = array ( );
		$strSQL = "SELECT * FROM xerxes_refereed WHERE ";
		
		if ( is_array( $issn ) )
		{
			if ( count( $issn ) == 0 )	throw new \Exception( "issn query with no values" );
			
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
			$objPeer = new RefereedValue();
			$objPeer->load( $arrResult );
			
			array_push( $arrPeer, $objPeer );
		}
		
		return $arrPeer;
	}
}
