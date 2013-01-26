<?php

/*
 * This file is part of the Xerxes project.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
