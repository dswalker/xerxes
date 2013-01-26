<?php

/*
 * This file is part of the Xerxes project.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xerxes\Utility;

/**
 * Email
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Email 
{
	private $registry;
	private $transport;
	private $mailer;
	
	public function __construct()
	{
		$this->registry = Registry::getInstance();
		
		$host = $this->registry->getConfig("SMTP_SERVER", false);
		
		if ( $host == '' ) // use local mail
		{
			$this->transport = \Swift_MailTransport::newInstance();
		}
		else // use smtp
		{
			$port = 25;
			
			if ( strstr($host, ':') )
			{
				$parts = explode(':', $host);
				$port = array_pop($parts);
				$host = implode(':', $parts);
			}
			
			$this->transport = \Swift_SmtpTransport::newInstance($host, $port);
		}
		
		$this->mailer = \Swift_Mailer::newInstance($this->transport);
	}
	
	public function send($email, $subject, $body)
	{
		$from = $this->registry->getConfig("EMAIL_FROM", true);
		
		$message = \Swift_Message::newInstance($subject)
			->setFrom($from)
			->setTo($email)
			->setBody($body);
		
		$numSent = $this->mailer->send($message);
		
		if ( $numSent == 1 )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
