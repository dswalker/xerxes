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
use Xerxes\Utility\Labels;

/**
 * Asset Controller
 * 
 * Special processing of assets, e.g., images, javascript, etc.
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class AssetController extends ActionController
{
	/**
	 * Convert the label file into a Javascript 
	 * so we can use it in JS messages
	 */
	
	public function labelsAction()
	{
		$lang = $this->request->getParam("lang"); // @todo need a proper language grabber
		
		$labels = new Labels();
		$labels->setLanguage($lang);
		
		$this->response->setVariable('labels', $labels);
		
		// this is a javascript file
		
		$this->response->headers->set('Content-type', 'application/javascript');
		$this->response->setView('asset/labels.phtml');
		
		return $this->response;
	}
}