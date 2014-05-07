<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Controller;

/**
 * Embed Database information
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class EmbedController extends DatabasesController
{
	public function genSubjectAction()
	{
		$params = $this->request->getParams();
		$params['action'] = 'subject';
		
		$embed_info = array();
		
		$embed_info['server_side_url'] = $this->request->url_for($params, true);
		
		$params['format'] = 'embed_html_js';
		
		$embed_info['javascript_url'] = $this->request->url_for($params, true);
		
		$this->response->setVariable('embed_info', $embed_info);
		
		return $this->subjectAction();
	}
}
