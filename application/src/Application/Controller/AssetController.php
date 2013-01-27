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

use Xerxes\Mvc\ActionController;

/**
 * Asset Controller
 * 
 * Special processing of assets (i.e., images, css, javascript)
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class AssetController extends ActionController
{
	/**
	 * Convert the label file into a Javascript array
	 * so we can use it in JS messages
	 */
	
	public function labelsAction()
	{
		$labels = $this->getLabels();
		$this->response->setVariable('labels', $labels);
		
		// this is a javascript file
		
		$this->response->headers->set('Content-type', 'application/javascript');
		$this->response->setView('asset/labels.phtml');
		
		return $this->response;
	}
}