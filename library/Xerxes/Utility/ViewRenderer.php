<?php

namespace Xerxes\Utility;

/**
 * Response Object
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes_Framework
 * @uses Parser
 */

class ViewRenderer
{
	private $_script_path; // path to the distro script
	
	public function __construct($script_path)
	{
		$this->_script_path = $script_path;
	}
	
	/**
	 * Display the response by calling view
	 */
	
	public function render($view, array $vars, $output_type = "html")
	{
		// xslt view
			
		if (strstr($view, '.xsl') )
		{
			$xml = $this->toXML($vars);
			$html = $this->transform($xml, $view, $output_type);
			return $html;
		}
			
		// php view
			
		else
		{			
			foreach ( $vars as $id => $value )
			{
				$this->$id = $value;
			}		
			
			// buffer the output so we can catch and return it
			
			ob_start();
			
			require_once $this->_script_path . "/" . $view;
			
			$content = ob_get_clean();
			
			return $content;
		}
	}
	
	public function toXML($vars)
	{
		$xml = new \DOMDocument();
		$xml->loadXML("<xerxes />");
	
		foreach ( $vars as $id => $object )
		{
			Parser::addToXML($xml, $id, $object);
		}
	
		return $xml;
	}
	
	protected function transform($xml, $path_to_xsl, $output_type, array $params = array())
	{
		$registry = Registry::getInstance();

		$import_array = array();
		
		// the xsl lives here

		$distro_xsl_dir = $this->_script_path . "/";
		$local_xsl_dir = realpath(getcwd()) . "/views/";
		
		// language file
		
		$request = new Request();
		$language = $request->getParam("lang");
		
		if ( $language == "" )
		{
			$language = $registry->defaultLanguage();
		}
		
		// english file is included by default (as a fallback)
		
		array_push($import_array, "labels/eng.xsl");
		
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
		
		return $xsl->transformToXml($xml, $path_to_xsl, $output_type, $params, $import_array);
	}
}
