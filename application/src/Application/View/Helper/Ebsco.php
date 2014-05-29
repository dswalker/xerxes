<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\View\Helper;

use Application\Model\Search\ResultSet;

class Ebsco extends Search
{
	public function addFacetLinks( ResultSet &$results )
	{
		parent:: addFacetLinks( $results );
		
		// peer-review quasi-facet
		
		$facets = $results->getFacets();
		
		$params = $this->facetParams();
		
		if ( array_key_exists("scholarly", $params) )
		{
			unset($params["scholarly"]);
		}
		else
		{
			$params["scholarly"] = "Scholarly only";
		}
		
		$facets->refereed_link = $this->request->url_for($params);
	}
}