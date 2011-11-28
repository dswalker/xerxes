<?php

/**
 * Database access mapper for peer-reviewed data
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: Refereed.php 1993 2011-11-10 17:06:42Z dwalker.calstate@gmail.com $
 * @package Xerxes
 */

class Xerxes_Model_DataMap_Refereed extends Xerxes_Framework_DataMap
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
	 * @param Xerxes_Model_Search_Refereed $objTitle peer reviewed journal object
	 */
	
	public function addRefereed(Xerxes_Model_Search_Refereed $objTitle)
	{
		$objTitle->issn = str_replace("-", "", $objTitle->issn);
		$this->doSimpleInsert("xerxes_refereed", $objTitle);
	}
	
	/**
	 * Get all refereed data
	 * 
	 * @return array of Xerxes_Model_Search_Refereed objects
	 */
	
	public function getAllRefereed()
	{
		$arrPeer = array();
		$arrResults = $this->select( "SELECT * FROM xerxes_refereed");
		
		foreach ( $arrResults as $arrResult )
		{
			$objPeer = new Xerxes_Model_Search_Refereed();
			$objPeer->load( $arrResult );
			
			array_push( $arrPeer, $objPeer );
		}		
		
		return $arrPeer;
	}
	
	/**
	 * Get a list of journals from the refereed table
	 *
	 * @param mixed $issn		[string or array] ISSN or multiple ISSNs
	 * @return array			array of Xerxes_Model_Search_Refereed objects
	 */
	
	public function getRefereed($issn)
	{
		$arrPeer = array ( );
		$arrResults = array ( );
		$strSQL = "SELECT * FROM xerxes_refereed WHERE ";
		
		if ( is_array( $issn ) )
		{
			if ( count( $issn ) == 0 )	throw new Exception( "issn query with no values" );
			
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
			$objPeer = new Xerxes_Model_Search_Refereed( );
			$objPeer->load( $arrResult );
			
			array_push( $arrPeer, $objPeer );
		}
		
		return $arrPeer;
	}
}
