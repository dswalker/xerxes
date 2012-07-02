<?php

namespace Xerxes\Lti;

use Xerxes\Utility\Parser;

require_once __DIR__ . '/../OAuth/OAuth.php'; // @todo: make this not hardwired

/**
 * Convenience class for verifying and accessing properties of basic lti launch request
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @version
 * @package Xerxes
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 */

class Basic 
{
	protected $request;
	
	public function __construct( $key, $secret )
	{
		$request = \OAuthRequest::from_request();
	
		$oauth_consumer_key = $request->get_parameter("oauth_consumer_key");
	
		// ensure the key in the request matches the locally supplied one
	
		if ( $oauth_consumer_key == null)
		{
			throw new \Exception("Missing oauth_consumer_key in request");
		}
		
		if ( $oauth_consumer_key != $key )
		{
			throw new \Exception("oauth_consumer_key doesn't match supplied key");
		}
		
		// verify the message signature
		
		$store = new TrivialOAuthDataStore( $oauth_consumer_key, $secret );
		$server = new \OAuthServer( $store );
		
		$method = new \OAuthSignatureMethod_HMAC_SHA1();
		$server->add_signature_method( $method );

		$server->verify_request( $request );
		
		$this->request = $request;
	}
	
	public function getParam($name)
	{
		return $this->request->get_parameter($name);
	}
	
	public function getID()
	{
		return $this->getParam("oauth_consumer_key") . ":" 
			. $this->getParam('context_id') . ":" 
			. $this->getParam('resource_link_id');		
	}
	
	public function isInstructor()
	{
		$roles = explode(',', $this->getParam('roles'));
		
		if ( in_array('Instructor', $roles) )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function toXML()
	{
		$xml = Parser::convertToDOMDocument("<lti />");
		
		$this->appendElement($xml, "id", $this->getID());
		$this->appendElement($xml, "instructor", $this->isInstructor());
		
		foreach ( $this->request->get_parameters() as $id => $value )
		{
			$this->appendElement($xml, $id, $value);
		}
		
		return $xml;
	}
	
	private function appendElement(&$xml, $id, $value)
	{
		$new = $xml->createElement($id, Parser::escapeXml($value));
		$xml->documentElement->appendChild($new);
	}
}

/**
 * A Trivial memory-based store - no support for tokens
 */

class TrivialOAuthDataStore extends \OAuthDataStore
{
    private $consumers = array();

    public function __construct($consumer_key, $consumer_secret) 
    {
        $this->consumers[$consumer_key] = $consumer_secret;
    }

    public function lookup_consumer($consumer_key) 
    {
        if ( strpos($consumer_key, "http://" ) === 0 )
        {
            $consumer = new \OAuthConsumer($consumer_key,"secret", NULL);
            return $consumer;
        }
        
        if ( $this->consumers[$consumer_key] )
        {
            $consumer = new \OAuthConsumer($consumer_key,$this->consumers[$consumer_key], NULL);
            return $consumer;
        }
        return NULL;
    }

    public function lookup_token($consumer, $token_type, $token) 
    {
        return new \OAuthToken($consumer, "");
    }

    public function lookup_nonce($consumer, $token, $nonce, $timestamp) 
    {
        // Should add some clever logic to keep nonces from
        // being reused - for no we are really trusting
		// that the timestamp will save us
        return NULL;
    }

    public function new_request_token($consumer) 
    {
        return NULL;
    }

    public function new_access_token($token, $consumer) 
    {
        return NULL;
    }
}
