<?php

namespace Application\View\Helper;

use Xerxes\Record\Author;

class Solr extends Search
{
	/**
	 * Make sure author names are based on full (display) field and quoted
	 * 
	 * @see Application\View\Helper.Search::linkAuthor()
	 */
	
	public function linkAuthor( Author $author )
	{
		$query = $author->getName();
		
		if ( $author->display != "" )
		{
			$query = $author->display;
		}
		
		$arrParams = $this->lateralLink();
		$arrParams['field'] = 'author';
		$arrParams['query'] = "\"$query\"";

		return $this->request->url_for($arrParams);
	}
}