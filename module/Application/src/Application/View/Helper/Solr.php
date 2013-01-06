<?php

namespace Application\View\Helper;

use Xerxes\Record\Author;

class Solr extends Search
{
	/**
	 * Take a defined searchable string for the author over the reguar name 
	 * 
	 * @see Application\View\Helper.Search::linkAuthor()
	 */
	
	public function linkAuthor( Author $author )
	{
		$query = $author->getName(); // regular author name
		
		// we've defined a specific searchable string for this author, so take that instead
		
		if ( $author->search_string != "" )
		{
			$query = $author->search_string;
		}
		
		$arrParams = $this->lateralLink();
		$arrParams['field'] = 'author';
		$arrParams['query'] = "\"$query\"";

		return $this->request->url_for($arrParams);
	}
}