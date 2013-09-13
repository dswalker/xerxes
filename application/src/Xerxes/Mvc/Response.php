<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xerxes\Mvc;

use Symfony\Component\HttpFoundation;
use Xerxes\Mvc\Response;
use Xerxes\Utility\Parser;
use Xerxes\Utility\Registry;
use Xerxes\Utility\Xsl;

/**
 * Response
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Response extends HttpFoundation\Response
{
	private $_vars = array(); // variables
	private $_script_path; // path to the distro script
	private $_view_dir; // view directory
	private $_view; // view file
	
	public $cache = true; // cache response
	
	/**
	 * @var Request
	 */
	
	protected $request;
	
	/**
	 * Set the request
	 * @param Request $request
	 */
	
	public function setRequest(Request $request)
	{
		$this->request = $request;
	}
	
	/**
	 * Set variable
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	
	public function setVariable($name, $value)
	{
		$this->_vars[$name] = $value;
	}
	
	/**
	 * Get variable
	 * 
	 * @param string $name
	 */
	
	public function getVariable($name)
	{
		if ( array_key_exists($name, $this->_vars) )
		{
			return $this->_vars[$name];
		}
		else
		{
			return null;
		}
	}
	
	/**
	 * Set (distro) view dir
	 *
	 * @param string $path
	 */
	
	public function setViewDir($path)
	{
		$this->_view_dir = $path;
	}	
	
	/**
	 * Set the view script
	 * 
	 * @param string $view
	 */
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	/**
	 * Unset the view script
	 */
	
	public function noView()
	{
		$this->_view = null;
	}
	
	/**
	 * Processes the view script against the data.
	 */
	
	public function render($format = 'html')	
	{
		// do we have a path to the view?
		
		if ( $this->_view_dir == '')
		{
			throw new \Exception('No view directory has been set');
		}
		
		// internal xml
		
		if ( $format == "xerxes" )
		{
			$this->headers->set('Content-type', 'text/xml');
			$this->setContent($this->toXML()->saveXML());
		}
		
		// no view set
		
		elseif ( $this->_view == null )
		{
			// do nothing
		}
		
		// xslt view
			
		elseif (strstr($this->_view, '.xsl') )
		{
			$xml = $this->toXML();
			$html = $this->transform($xml, $this->_view, $format);
			$this->setContent($html);
		}
			
		// php view
			
		else
		{
			// buffer the output so we can catch and return it
			
			ob_start();
			require_once $this->_view_dir . "/" . $this->_view;
			$html = ob_get_clean();
			
			$this->setContent($html);
		}
		
		if ( $this->cache == false )
		{
			$this->noCache();
		}
		
		return $this;
	}
	
	/**
	 * Return variables as XML
	 */
	
	public function toXML()
	{
		$xml = Parser::convertToDOMDocument('<xerxes />');
	
		foreach ( $this->_vars as $id => $object )
		{
			Parser::addToXML($xml, $id, $object);
		}
	
		return $xml;
	}
	
	/**
	 * Transform XML to HTML
	 * 
	 * @param mixed $xml  XML-like data
	 * @param string $path_to_xsl
	 * @param array $params
	 */
	
	protected function transform($xml, $path_to_xsl, $format, array $params = array())
	{
		$import_array = array();
		
		// the xsl lives here

		$distro_xsl_dir = $this->_view_dir;
		$local_xsl_dir = realpath(getcwd()) . "/views/";
		
		// language
		
		// english file is included by default (as a fallback)
		
		array_push($import_array, "labels/eng.xsl");
		
		$language = $this->request->getParam("lang");
		
		$registry = Registry::getInstance();
		
		if ( $language == "" )
		{
			$language = $registry->defaultLanguage();
		}
		
		// if language is set to something other than english
		// then include that file to override the english labels
		
		if ( $language != "eng" &&  $language != '') 
		{
			array_push($import_array, "labels/$language.xsl");
		}
		
		// make sure we've got a reference to the local includes too
		
		array_push($import_array, "includes.xsl");
		
		// transform
		
		$xsl = new Xsl($distro_xsl_dir, $local_xsl_dir);
		
		return $xsl->transformToXml($xml, $path_to_xsl, $format, $params, $import_array);
	}
	
	/**
	 * Make sure no caching of page
	 */
	
	protected function noCache()
	{
		$this->headers->addCacheControlDirective('no-store', true);
		$this->headers->addCacheControlDirective('no-cache', true);
		$this->headers->addCacheControlDirective('must-revalidate', true);
		$this->headers->addCacheControlDirective('post-check', 0);
		$this->headers->addCacheControlDirective('pre-check', 0);
	}	
}
