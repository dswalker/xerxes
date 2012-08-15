<?php

namespace Application\Controller;

use Application\Model\Solr\Engine;

class SolrController extends SearchController
{
	protected $id = "solr";
	
	protected function getEngine()
	{
		return new Engine();
	}
	
	public function recordAction()
	{
		$model = parent::recordAction();

		// flash message
		
		$flashMessenger = $this->flashMessenger();
		
		if ($flashMessenger->hasMessages())
		{
			$message = $flashMessenger->getMessages();
			$model->setVariable( 'flash_message', $message[0] );
		}
		
		return $model;
	}
	
	public function smsAction()
	{
		$id = $this->request->getParam('id');
		$phone = $this->request->getParam('phone');
		$provider = $this->request->getParam('provider');
		$item_no = $this->request->getParam('item');

		### provider
		
		if ( $provider == "" )
		{
			throw new \Exception("Please choose your cell phone provider");
		}

		// save provider in session
			
		$this->request->setSessionData("user_provider", $provider);
		
		### phone
				
		if ( $phone == null )
		{
			throw new \Exception("Please enter a phone number");
		}
		
		// only numbers, please
			
		$phone = preg_replace('/\D/', "", $phone);
			
		// did we get 10?
			
		if ( strlen($phone) != 10 )
		{
			throw new \Exception("Please enter a 10 digit phone number, including area code");
		}	
		
		$email = $phone . '@' . $provider;
		
		### item
		
		// position is one-based in XSLT so switch to zero-based here
		
		$item_no = (int) $item_no;
		$item_no = $item_no - 1;
		
		### record
	
		$results = $this->engine->getRecord($id);	
		
		$result = $results->getRecord(0);
		
		// send it
		
		$result->textLocationTo($email, $item_no);
		
		// flash
		
		$this->flashMessenger()->addMessage("Message successfully sent");
		
		// send back to main record page
		
		$params = array(
			'controller' => $this->id,
			'action' => 'record',
			'id' => $id,
			// 'format' => 'xerxes'
		);
		
		$url = $this->request->url_for($params);
		return $this->redirect()->toUrl($url);
	}
}
