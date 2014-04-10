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

use Xerxes\Mvc\Exception\AccessDeniedException;

use Guzzle\Http\Exception\RequestException;
use Xerxes\Mvc\ActionController;

class ErrorController extends ActionController
{
    public function displayError(\Exception $e)
    {
    	$code = $e->getCode();
    	$message = $e->getMessage();
    	$line = $e->getFile() . ', line ' . $e->getLine();
    	$trace = $e->getTraceAsString();
    	
    	// send the error to the log
    	
    	trigger_error("Xerxes Error ($line): $message");
    	
    	// error response
    	
    	$error = array();
    	$error['type'] = 'error';
    	$error['code'] = $code;
    	
    	// handle PDO and Guzzle errors differently
    	
    	if ( $e instanceof RequestException )
    	{
    		// echo (string) $e->getResponse()->getBody(); exit; 
    		
    		$message = 'Could not connect to search engine';
    	}
    	elseif ($e instanceof \PDOException )
    	{
    		$message = 'There was a problem with the database';
    	}
    	elseif ( $e instanceof AccessDeniedException )
    	{
    		$error['type'] = 'access_denied'; 
    	}

    	$error['message'] = $message;
    	
    	// only include location and trace if reporting is turned on
    	
    	if ( ini_get('display_errors') ==  1 )
    	{
    		$error['line'] = $line;
    		$error['trace'] = $trace;
    	}
    	
    	$this->response->setStatusCode(500);
    	
    	$this->response->setVariable('error', $error);
    	
    	if ( $this->request->isXmlHttpRequest() )
    	{
    		$this->response->setView('error/ajax.xsl');
    	}
    	else
    	{
	    	$this->response->setView('error/index.xsl');
    	}
    	
    	return $this->response;
    }
}
