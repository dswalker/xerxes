<?php

namespace Xerxes\Marc;

/**
 * MARC Data Field
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class DataField extends Field
{
	public $tag;
	public $ind1;
	public $ind2;
	
	private $_subfields = array();
	
	/**
	 * Create a MARC Data Field
	 * 
	 * @param \DOMNode $objNode
	 */
	
	public function __construct(\DOMNode $objNode = null )
	{
		if ( $objNode != null )
		{
			$this->tag = $objNode->getAttribute("tag");
			$this->ind1 = $objNode->getAttribute("ind1");
			$this->ind2 = $objNode->getAttribute("ind2");
	
			foreach ( $objNode->getElementsByTagName("subfield") as $objSubfield )
			{
				$objMarcSubField = new SubField($objSubfield);
				array_push($this->_subfields, $objMarcSubField);
			}
		}
	}
	
	/**
	 * Get the subfield of this datafield
	 *
	 * @param string $code		[optional] single subfield code, or multiple subfield codes listed together,
	 * 							empty value returns all subfields
	 * @param bool 				[optional] return fields in the order specified in $code
	 * 
	 * @return SubFieldList
	 */
	
	public function subfield($code = "", $specified_order = false)
	{
		$codes = str_split($code);
		
		$list = new SubFieldList();
		
		if ( $code == "" )
		{
			foreach ( $this->_subfields as $subfield )
			{
				$list->addField($subfield);
			}
		}
		else
		{
			if ( $specified_order == true)
			{
				// do it this way so fields are returned in the order in 
				// which they were specified in the paramater

				foreach ( $codes as $subfield_code )
				{
					foreach ( $this->_subfields as $subfield )
					{
						if ( $subfield->code == $subfield_code )
						{
							$list->addField($subfield);
						}
					}
				}				
				
			}
			else
			{
				// $code is just defining fields to include, not order of codes,
				// so take them in the order in which they appear
				
				foreach ( $this->_subfields as $subfield )
				{
					if ( in_array($subfield->code, $codes ) )
					{
						$list->addField($subfield);
					}
				}
			}
		}
		
		return $list;
	}
	
	/**
	 * Get all subfields and return them with a space separator
	 *
	 * @return unknown
	 */

	function __toString()
	{
		$content = "";
		
		foreach ( $this->_subfields as $subfield )
		{
			$content .= " " . $subfield->__toString();
		}
		
		return trim($content);
	}
	
	/**
	 * Add a MARC SubField to this Data Field
	 * 
	 * @param SubField $field
	 */
	
	public function addSubField(SubField $field)
	{
		array_push($this->_subfields, $field);
	}
}