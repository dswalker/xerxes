<?php

/*
 * This file is part of the Xerxes project.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Controller;

use Xerxes\Mvc\ActionController;
use Xerxes\Utility\Labels;

class AssetController extends ActionController
{
	public function labelsAction()
	{
		$lang = $this->request->getParam("lang");
		
		$labels = new Labels();
		$labels->setLanguage($lang);
		
		$this->response->setVariable('labels', $labels);
		
		
		$this->response->headers->set('Content-type', 'application/javascript');
		$this->response->setView('asset/labels.phtml');
		
		return $this->response;
	}
}