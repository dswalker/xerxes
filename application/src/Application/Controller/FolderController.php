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

use Application\Model\DataMap\SavedRecords;
use Application\Model\Saved\Engine;
use Application\Model\Solr;
use Application\View\Helper\Folder as FolderHelper;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Xerxes\Mvc\Request;
use Xerxes\Utility\Email;
use Xerxes\Utility\User;

class FolderController extends SearchController
{
	protected $id = 'folder';
	
	/**
	 * @var Engine
	 */
	
	protected $engine;
	
	/**
	 * (non-PHPdoc)
	 * @see Application\Controller.SearchController::init()
	 */

	protected function init()
	{
		parent::init();
	
		$this->helper = new FolderHelper($this->event, $this->id, $this->engine);
	}
	
	/**
	 * @return Engine
	 */
	
	protected function getEngine()
	{
		return new Engine();
	}
	
	/**
	 * Register return url and redirect to results page
	 */
	
	public function indexAction()
	{
		// register the return url in session so we can send the user back
		
		$this->request->setSessionData('return', $this->request->getParam('return'));
		
		// redirect to the results page
		
		$params = array (
			'controller' => 'folder',
			'action' => 'results',
			'username' => $this->request->getSessionData('username')
		);
		
		return $this->redirectTo($params);
	}
	
	/**
	 * Main page of results
	 */
	
	public function resultsAction()
	{
		// ensure we've got the right user
		
		if ( $this->request->getParam('username') != $this->request->getSessionData('username') )
		{
			$params = array(
				'controller' => 'folder',
				'action' => 'results',
				'username' => $this->request->getSessionData('username')
			);
				
			return $this->redirectTo($params);
		}		
		
		$total = $this->engine->getHits($this->query)->getTotal();
		
		// user is not logged in, and has no temporary saved records, so nothing to show here;
		// force them to login
		
		if ( ! $this->request->getUser()->isAuthenticated() && $total == 0 && $this->request->getSessionData('folder_visited') == null )
		{
			// link back here, but minus any username
			
			$folder_link = $this->request->url_for(	array('controller' => 'folder'), true );
			
			// auth link, with return back to here
			
			$params = array(
				'controller' => 'authenticate',
				'action' => 'login',
				'return' => $folder_link
			);
			
			return $this->redirectTo($params); // redirect them out
		}
		
		$this->request->setSessionData('folder_visited', true);
		
		return parent::resultsAction();
	}
	
	/**
	 * Assign label to a record
	 */
	
	public function labelAction()
	{
		$missing = $this->checkRequiredParams();
		
		if ( $missing != null )
		{
			return $missing;
		}
		
		$id_array = $this->request->getParam('record', null, true);
		$return = $this->request->getParam('return');
		$tag = $this->request->getParam('tag');
		$remove = $this->request->getParam('remove');
		$username = $this->request->getSessionData('username');
		
		if ( $tag == null )
		{
			$this->request->setFlashMessage(Request::FLASH_MESSAGE_ERROR, 'Please provide a label.');
			return $this->redirectTo($return);
		}
		
		$datamap = new SavedRecords();
		
		if ( $remove != null )
		{
			$datamap->deleteTag($username, $tag, $id_array);
		}
		else // assign tag(s)
		{
			$tags = explode(',', $tag);
			
			foreach ( $tags as $tag_add )
			{
				$datamap->addTag($username, trim($tag_add), $id_array);
			}
		}
		
		// redirect out with message
		
		$this->request->setFlashMessage(Request::FLASH_MESSAGE_NOTICE, "Records updated");
		return $this->redirectTo($return);
	}
	
	/**
	 * Delete records
	 */
	
	public function deleteAction()
	{
		$missing = $this->checkRequiredParams();
		
		if ( $missing != null )
		{
			return $missing;
		}
		
		$id_array = $this->request->getParam('record', null, true);
		$return = $this->request->getParam('return');
		$username = $this->request->getSessionData('username');
	
		// remove saved records from database
	
		$datamap = new SavedRecords();
		$original_ids = $datamap->deleteRecordByID($username, $id_array);
	
		// remove any saved record state for these
	
		foreach ( $original_ids as $original_id )
		{
			$this->unmarkSaved($original_id);
		}
	
		$this->request->setFlashMessage(Request::FLASH_MESSAGE_NOTICE, "Records deleted");
		return $this->redirectTo($return);
	}	
	
	/**
	 * Master output function, ultimately calls the functions below
	 */
	
	public function exportAction()
	{
		$output = $this->request->requireParam('output', 'Request must include output option');

		$missing = $this->checkRequiredParams();
		
		if ( $missing != null )
		{
			return $missing;
		}
		
		$method = $output . 'Action';
		
		$this->request->setSessionData('last_output', $output);
		
		if ( method_exists($this, $method))
		{
			return $this->$method();
		}
	}
	
	/**
	 * Redirect the user to Endnote Web with return URL
	 */
	
	public function endnotewebAction()
	{
		// get the ids that were selected for export
			
		$id_array = $this->request->getParam('record', null, true);
			
		// construct return url back to the fetch action
			
		$params = array (
			'controller' => 'folder',
			'action' => 'fetch',
			'format' => 'ris',
			'records' => implode(',', $id_array)
		);
			
		$return = $this->request->url_for($params, true);
		
		
		// @todo abstract this out to search?
		// get address for refworks
			
		$url = $this->registry->getConfig('ENDNOTE_ADDRESS', false, 'https://www.myendnoteweb.com/EndNoteWeb.html');
		$name = $this->registry->getConfig('APPLICATION_NAME', false, 'Xerxes');
			
		// construct full url to endnote
			
		$url .= '?partnerName=' . urlencode($name);
		$url .= '&dataRequestUrl=' . urlencode($return);
		$url .= '&func=directExport&dataIdentifier=1&Init=Yes&SrcApp=CR&returnCode=ROUTER.Unauthorized';
			
		return $this->redirectTo($url);
	}
	
	/**
	 * Redirect the user to Refworks with return URL
	 */	
	
	public function refworksAction()
	{
		// get the ids that were selected for export
			
		$id_array = $this->request->getParam('record', null, true);
			
		// construct return url back to the fetch action
			
		$params = array (
			'controller' => 'folder',
			'action' => 'fetch',
			'format' => 'ris',
			'records' => implode(',', $id_array)
		);
			
		$return = $this->request->url_for($params, true);		
		
		
		// @todo abstract this out to search?
		// get address for refworks
			
		$url = $this->registry->getConfig('REFWORKS_ADDRESS', false, 'http://www.refworks.com/express/ExpressImport.asp');
		$name = $this->registry->getConfig('APPLICATION_NAME', false, 'Xerxes');
			
		// construct full url to refworks
			
		$url .= '?vendor=' . urlencode($name);
		$url .= '&filter=RIS+Format';
		$url .= '&encoding=65001';
		$url .= '&url=' . urlencode($return);
			
		return $this->redirectTo($url);		
	}
	
	public function blackboardAction()
	{
		// get the ids that were selected for export
		
		$id_array = $this->request->getParam('record', null, true);
		
		// construct return url back to the fetch action
		
		$arrProperties = array (
			"controller" => "folder",
			"action" => "fetch",
			"format" => "ris",
			"records" => implode(',', $id_array)
		);
		
		$return = $this->request->url_for($arrProperties, true);
		
		
		// @todo abstract this out to search?
		// get address for blackboard
		
		$url = $this->registry->getConfig("BB_ADDRESS", false, 'http://bb91.verbenaconsultant.com/webapps/csu-citation-BBLEARN/citations/ExpressImport.jsp');
		
		// construct full url to bb
		
		$url .= "?url=" . urlencode($return);
		
		return $this->redirectTo($url);		
	}
	
	/**
	 * Email records to specified account
	 */
	
	public function emailAction()
	{
		$id_array = $this->request->getParam('record', null, true);
		$return = $this->request->getParam('return');
		
		$email = $this->request->getParam('email');
		$subject = $this->request->getParam('subject', 'Saved Records');
		$notes = $this->request->getParam('notes');
		
		// user hasn't entered email, so show that page
		
		if ( $email == null )
		{
			$this->response->setView('folder/email.xsl');
			return $this->response;
		}
		
		// save it for later, for convenience
		
		$this->request->setSessionData('email', $email);
		
		// get the records
		
		$this->response = $this->fetchAction();
		
		// convert to simple text
		
		$this->response->setView('citation/basic.xsl');
		$body = $notes . "\r\n\r\n\r\n" . $this->response->render('text')->getContent();
		
		// email it!
		
		$email_client = new Email();
		$result = $email_client->send($email, $subject, $body);
		
		// notify user and send them back
		
		if ( $result == true )
		{
			$this->request->setFlashMessage(Request::FLASH_MESSAGE_NOTICE, 'Email successfully sent');
		}
		else
		{
			$this->request->setFlashMessage(Request::FLASH_MESSAGE_ERROR, "Sorry, we couldn't send an email at this time");
		}
		
		return $this->redirectTo($return);
	}
	
	/**
	 * Download records to a text file
	 */	
	
	public function textAction()
	{
		$response = $this->fetchAction();
		
		$response->headers->set('Content-Type', 'text/plain');
		$disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'download.txt');
		$response->headers->set('Content-Disposition', $disposition);

		$response->setView('citation/basic.xsl');
		
		return $response;
	}
	
	/**
	 * Download records to a citation management tool, endnote, zotero, etc.
	 */
	
	public function endnoteAction()
	{
		$response = $this->fetchAction();
	
		$response->headers->set('Content-Type', 'application/x-research-info-systems');
		$response->setView('citation/ris.xsl');
	
		return $response;
	}	
	
	/**
	 * Fetch and display the metadata of records by id
	 */
	
	public function fetchAction()
	{
		$format = $this->request->getParam('format');
		
		// id's can either come in as a series of 'record' params 
		// or a single 'records' param containing id's separated by comma
		
		$id_array = $this->request->getParam('record', null, true);
		
		if ( count($id_array) == 0 )
		{
			$record_ids = $this->request->getParam('records');
			$id_array = explode(',', $record_ids);
		}
		
		if ( count($id_array) == 0 )
		{
			throw new \Exception('Request must specify record ids');
		}
		
		$results = $this->engine->getRecords($id_array);
		
		$this->response->setVariable('results',$results);
		
		if ( $format == 'ris')
		{
			$this->response->setView('citation/ris.xsl');
		}
		
		return $this->response;
	}
	
	/**
	 * Make sure request has records and a return url
	 */
	
	protected function checkRequiredParams()
	{
		$return = $this->request->requireParam('return', 'Request must include return URL');
		
		// no records selected, so send them back
		
		if ( $this->request->getParam('record') == null )
		{
			$this->request->setFlashMessage(Request::FLASH_MESSAGE_ERROR, 'Please select records.');
			return $this->redirectTo($return);
		}
		else
		{
			return null;
		}
	}
}
