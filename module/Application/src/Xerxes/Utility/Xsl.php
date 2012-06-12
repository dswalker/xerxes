<?php

namespace Xerxes\Utility;

/**
 * Utility class for XSLT to allow distro/local overriding
 * 
 * @author David Walker
 * @author Jonathan Rochkind
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license
 * @version
 * @package  Xerxes_Utility
 */ 

class Xsl
{
	private $distro_xsl_dir; // location of distro xsl
	private $local_xsl_dir; // directory with local override
	
	/**
	 * Return an XSL parsing object
	 * 
	 * @param string $distro_xsl_dir		location of distro xsl
	 * @param string $local_xsl_dir			directory with local override
	 */
	
	public function __construct($distro_xsl_dir, $local_xsl_dir )
	{
		$this->distro_xsl_dir = rtrim($distro_xsl_dir, '/');
		$this->local_xsl_dir = rtrim($local_xsl_dir, '/');
	}
	
	/**
	 * Alias for transform
	 */
	
	public function transformToDoc( $xml, $path_to_xsl, $output_type = null, array $params =  array(), $import_array = array() )
	{
		return $this->transform($xml, $path_to_xsl, $output_type, $params, $import_array, false);
	}
	
	/**
	 * Transform to string
	 */	
	
	public function transformToXml( $xml, $path_to_xsl, $output_type = null, array $params =  array(), array $import_array = array() )
	{
		return $this->transform($xml, $path_to_xsl, $output_type, $params, $import_array);
	}
	
	/**
	 * Simple, dynamic xsl transform
	 */	
				
	protected function transform ( $xml, $path_to_xsl, $output_type = null, array $params =  array(), array $import_array = array(), $to_string = true )
	{
		if ( $path_to_xsl == "") throw new \Exception("no stylesheet supplied");
		
		// make sure we have a domdocument
		
		if ( is_string($xml) )
		{
			$xml = Parser::convertToDOMDocument($xml);
		}
		
		// create xslt processor
		
		$processor = new \XsltProcessor();
		$processor->registerPhpFunctions();

		// add parameters
		
		foreach ($params as $key => $value)
		{
			$processor->setParameter(null, $key, $value);
		}
			
		// add stylesheet
		
		$xsl = $this->generateBaseXsl($path_to_xsl, $import_array, $output_type);
		
		$processor->importStylesheet($xsl);
		
		// transform
		
		if ( $to_string == true )
		{
			return $processor->transformToXml($xml);
		}
		else 
		{
			return $processor->transformToDoc($xml);
		}
	}

	/**
	 * Dynamically create our 'base' stylesheet, combining distro and local
	 * stylesheets, as available, using includes and imports into our
	 * 'base'.  Base uses the dynamic_skeleton.xsl to begin with. 
	 * 
	 * @param string $path_to_file 		Relative path to a stylesheet
	 * @param array $import_array		[optional] additional stylesheets that should be imported in the request
	 * @return DomDocument 				A DomDocument holding the generated XSLT stylesheet.
	 * @static
	*/
	
	private function generateBaseXsl( $path_to_file, $import_array = array(), $output_type)
	{
		$files_to_import = array();
		
		### first, set up the paths to the distro and local directories

		$distro_path =  $this->distro_xsl_dir . '/' . $path_to_file;
		$local_path =  $this->local_xsl_dir . '/' . $path_to_file;
		      

		### check to make sure at least one of the files exists
		
		$distro_exists = file_exists($distro_path);
		$local_exists = file_exists($local_path);

		// if we don't have either a local or a distro copy, that's a problem.
		
		if (! ( $local_exists || $distro_exists) )
		{
			// throw new Exception("No xsl stylesheet found: $local_path || $distro_path");
			throw new \Exception("No xsl stylesheet found: $path_to_file");
		}			
		
		
		### now create the skeleton XSLT file that will hold references to both
		### the distro and the local files
		
		$generated_xsl = new \DOMDocument();
		
		$xml = "
			<xsl:stylesheet 
				version=\"1.0\"
				xmlns:xsl=\"http://www.w3.org/1999/XSL/Transform\"
				xmlns:php=\"http://php.net/xsl\" 
				exclude-result-prefixes=\"php\">		
			</xsl:stylesheet>";
		
		$generated_xsl->loadXML(trim($xml));
		
		// dynamically create the output type
	
		$output = $generated_xsl->createElementNS('http://www.w3.org/1999/XSL/Transform', "output");
		$generated_xsl->documentElement->appendChild($output);
		
		// html 4
		
		if ( $output_type == "html")
		{
			$output->setAttribute("method", "html");
			$output->setAttribute("doctype-public", "-//W3C//DTD HTML 4.01 Transitional//EN");
			$output->setAttribute("doctype-system", "http://www.w3.org/TR/html4/loose.dtd");
		}
		
		// always include distro includes
		
		$files_to_import[] = $this->distro_xsl_dir . '/' .'includes.xsl';
		
		
		### add a reference to the distro file

		if ( $distro_exists == true )
		{	
			array_push($files_to_import, $distro_path);
		}
		
		### add a refence for files programatically added
		
		if ( $import_array != null )
		{
			foreach ( $import_array as $strInclude )
			{
				// but only if a distro copy exists
				
				$distro_include = $this->distro_xsl_dir . '/' . $strInclude;
				$local_include = $this->local_xsl_dir . '/' . $strInclude;
				
				// don't include the distro includes.xsl, since this messes things up!
				// @todo: figure out why includes.xsl is this weird exception
				
				if ( file_exists($distro_include) && $strInclude != "includes.xsl")
				{
					array_push($files_to_import, $distro_include);
				}
				
				// see if there is a local version, and include it too
				
				if ( file_exists($local_include) )
				{
					array_push($files_to_import, $local_include);
				}
			}
		}
		
		### add a refence to the local file
		
		if ( $local_exists )
		{
			$this->addIncludeReference( $generated_xsl, $local_path );
		}
		

		### if the distro file  xsl:includes or xsl:imports other files
		### check if there is a corresponding local file, and import it too
		
		// We import instead of include in case the local stylesheet does erroneously 
		// 'include', to avoid a conflict. We import LAST to make sure it takes 
		// precedence over distro. 
		
		if ( $distro_exists )
		{
			$distroXml = simplexml_load_file( $distro_path );
		
			$distroXml->registerXPathNamespace( 'xsl', 'http://www.w3.org/1999/XSL/Transform' );
			
			// find anything include'd or import'ed in original base file
			
			$array_merged = array_merge( $distroXml->xpath( "//xsl:include" ), $distroXml->xpath ( "//xsl:import" ) );
			
			foreach ( $array_merged as $extra )
			{
				// path to local copy
				
				$local_candidate = $this->local_xsl_dir . '/' . dirname ( $path_to_file ) . '/' . $extra['href'];
				
				// path to distro copy as a check
				
				$distro_check = $this->distro_xsl_dir . '/' . dirname ( $path_to_file ) . '/' . $extra['href'];
				
				// make sure local copy exists, and they are both not pointing at the same file 
				
				if ( file_exists( $local_candidate ) && realpath($distro_check) != realpath($local_candidate) )
				{
					array_push($files_to_import, $local_candidate);
				}
			}
		}
		
		// now make sure no dupes
		
		$files_to_import = array_unique($files_to_import);
		
		
		### now the actual mechanics of the import
		
		foreach ( $files_to_import as $import )
		{
			$this->addImportReference ( $generated_xsl, $import, $output );
		}
		
		// header("Content-type: text/xml"); echo $generated_xsl->saveXML(); exit;
		
		return $generated_xsl;
	}
	
	/**
	 * Internal function used to add another import statement to a supplied
	 * XSLT stylesheet. An insertPoint is also passed in--a reference to a 
	 * particular DOMElement which the 'import' will be added right before.
	 * Ordering of imports matters. 
	 * 
	 * @param DomDocument $xsltStylesheet	stylesheet to be modified
	 * @param string $absoluteFilePath 		abs filepath of stylesheet to be imported
	 * @param DomElement $insertPoint 		DOM Element to insert before. 
	 */ 
	
	private function addImportReference($xsltStylesheet, $absoluteFilePath, $insertPoint)
	{
		$absoluteFilePath = str_replace('\\', '/', $absoluteFilePath); // darn windows
		
		$import_element = $xsltStylesheet->createElementNS("http://www.w3.org/1999/XSL/Transform", "xsl:import");
		$import_element->setAttribute("href", $absoluteFilePath);
		$xsltStylesheet->documentElement->insertBefore( $import_element, $insertPoint);
		
		return $xsltStylesheet;
	}
	
	/**
	 * Internal function used to add another inlude statement to a supplied
	 * XSLT stylesheet. Include will be added at end of stylesheet. 
	 * 
	 * @param DomDocument $xsltStylesheet	stylesheet to be modified
	 * @param string $absoluteFilePath abs filepath of stylesheet to be imported
	 */
	
	private function addIncludeReference($xsltStylesheet, $absoluteFilePath)
	{
		$absoluteFilePath = str_replace('\\', '/', $absoluteFilePath); // darn windows
		
		$include_element = $xsltStylesheet->createElementNS("http://www.w3.org/1999/XSL/Transform", "xsl:include");
		$include_element->setAttribute("href", $absoluteFilePath);
		$xsltStylesheet->documentElement->appendChild( $include_element );
	}
}