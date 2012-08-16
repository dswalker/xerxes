<?php

namespace Xerxes\Utility;

use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mail\Transport\Sendmail;
use Zend\Mail\Message;

/**
 * Email
 * 
 * @author David Walker
 * @copyright 2012 California State University
 * @link
 * @license
 * @version
 */

class Email 
{
	private $from;
	private $host;
	private $transport;
	
	public function __construct()
	{
		$registry = Registry::getInstance();
		
		$this->from = $registry->getConfig("EMAIL_FROM", true);
		$this->host = $registry->getConfig("SMTP_SERVER", false);
		
		if ( strstr($this->host, ':') )
		{
			$parts = explode(':', $this->host);
			$this->port = array_pop($parts);
			$this->host = implode(':', $parts);
		}
		
		if ( $this->host != '' )
		{
			$options = new SmtpOptions();
			$options->setHost($this->host);
			$options->setPort($this->port);
		
			$this->transport = new Smtp($options);
		}
		else
		{
			$this->transport = new Sendmail();
		}		
	}
	
	public function send($email, $subject, $body)
	{
		$message = new Message();
		
		$message->setTo($email);
		$message->setFrom($this->from);
		$message->setSubject($subject);
		$message->setBody($body);
		
		$this->transport->send($message);
	}
}
