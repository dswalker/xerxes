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
		require_once __DIR__ . '/../../../../../../xerxes/lib/PHPMailer/class.phpmailer.php';
		
		$registry = Registry::getInstance();
		
		$mail = new \PHPMailer();
		$mail->IsSMTP();  // telling the class to use SMTP
		$mail->Host = "coweumx01.calstate.edu:25"; // SMTP server		
		
		$mail->From = $registry->getConfig("EMAIL_FROM", true);
		$mail->FromName = $subject;
		$mail->AddAddress($email);
			
		$mail->Subject = $subject;
		$mail->Body = $body;
		$mail->WordWrap = 50;

		if ( ! $mail->Send() )
		{
			throw new \Exception("Could not send message", 2);
		}		
		
		
		/*
		$message = new Message();
		
		$message->setTo($email);
		$message->setFrom($this->from);
		$message->setSubject($subject);
		$message->setBody($body);
		
		$this->transport->send($message);
		
		*/
	}
}
