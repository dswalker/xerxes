<?php

namespace Xerxes\Record;

/**
 * Table of Contents Chapter
 * 
 * @author David Walker <dwalker@calstate.edu>
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
