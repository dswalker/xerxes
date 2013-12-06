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

use Application\Model\Search\Query;

class Summon extends Search
{
	public function addQueryLinks(Query $query)
	{
		$query_exp = $this->request->getParam('facet.qe');
		$params = $this->currentParams();
		
		// link to activate query expansion
		
		$params['facet.qe'] = '1';
		$query->url_expand_query = $this->request->url_for($params);
		
		// link to deactivate it

		$params['facet.qe'] = '0';
		$query->url_dont_expand_query = $this->request->url_for($params);
		
		parent::addQueryLinks($query);
	}
}