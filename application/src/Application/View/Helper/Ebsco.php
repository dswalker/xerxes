<?php

namespace Application\View\Helper;

use Application\Model\Search\ResultSet,
	Application\Model\Search\FacetGroup;

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