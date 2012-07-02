<?php

namespace Xerxes\Utility;

use Zend\View\Model\ModelInterface,
	Zend\View\Renderer\RendererInterface,
	Zend\View\Renderer\TreeRendererInterface,
	Zend\View\Resolver\ResolverInterface as Resolver;

/**
 * View Renderer
 * 
 * @author David Walker
 * @copyright 2012 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class ViewRenderer implements RendererInterface, TreeRendererInterface
{
	private $_script_path; // path to the distro script
	private $format = "html"; // output format type
	
	/**
	 * Create new View Renderer
	 * 
	 * @param string $script_path
	 */
	
	public function __construct($script_path)
	{
		$this->_script_path = $script_path;
	}
	
	/**
	 * Return the template engine object
	 */
	
	public function getEngine()
	{
		return null;
	}
	
	/**
	 * Set the resolver used to map a template name to a resource the renderer may consume.
	 *
	 * @param  Resolver $resolver
	 * @return ViewRenderer
	 */
	
	public function setResolver(Resolver $resolver)
	{
		return $this;
	}
	
	/**
	 * Set output format
	 * 
	 * @param string $format
	 */
	
	public function setFormat($format)
	{
		$this->format = $format;
	}
	
	/**
	 * Get output format
	 */
	
	public function getFormat()
	{
		return $this->format;
	}
	
	public function canRenderTrees()
	{
		return true;	
	}
	
	/**
	 * Processes a view script and returns the output.
	 *
	 * @param  string|Model $name The script/resource process, or a view model
	 * @param  null|array|\ArrayAccess Values to use during rendering
	 * @return string The script output.
	 */
	
	public function render($model, $vars = null)	
	{
		if ( ! $model instanceof ModelInterface )
		{
			throw new \Exception('how did that happen?');
		}
		
		$variables = $model->getVariables();
		
		// internal xml
		
		if ( $this->format == "xml" )
		{
			return $this->toXML($variables)->saveXML();
		}
		
		$view = $model->getTemplate();
		
		// no view set, so do nothing
		
		if ( $view == null )
		{
			return;
		}
		
		// xslt view
			
		if (strstr($view, '.xsl') )
		{
			$xml = $this->toXML($variables);
			$html = $this->transform($xml, $view);
			return $html;
		}
			
		// php view
			
		else
		{
			foreach ( $variables as $id => $value )
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
	
	/**
	 * Return values as XML
	 * 
	 * @param null|array|\ArrayAccess Values to use during rendering
	 */
	
	public function toXML($vars)
	{
		$xml = Parser::convertToDOMDocument('<xerxes />');
	
		foreach ( $vars as $id => $object )
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
	
	protected function transform($xml, $path_to_xsl, array $params = array())
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
		
		return $xsl->transformToXml($xml, $path_to_xsl, $this->format, $params, $import_array);
	}
}
