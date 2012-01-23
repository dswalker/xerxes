<?php

namespace Xerxes\Record;

/**
 * Table of Contents Chapter
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Chapter
{
	public $title;
	public $author;
	public $statement;
	
	public function __toString()
	{
		if ( $this->statement != "" )
		{
			return $this->statement;
		}
		elseif ( ($this->title != "" && $this->author != null ))
		{
			return $this->title . ' / ' . $this->author;
		}
		elseif ( $this->title != "")
		{
			return $this->title;
		}
		else
		{
			return "";
		}
	}
}
