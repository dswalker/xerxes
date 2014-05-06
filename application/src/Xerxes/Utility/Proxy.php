<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xerxes\Utility;

/**
 * Proxy URLs
 * 
 * @author David Walker <dwalker@calstate.edu>
 */ 

class Proxy
{
	/**
	 * Proxy URL
	 */
	
	public static function getProxyLink( $url )
	{
		$registy = Registry::getInstance();
		
		$final = ""; // final link to send back
		$should_proxy = true; // proxy  
		
		$proxy_server = $registy->getConfig("PROXY_SERVER", false);
		
		// make sure the link doesn't include the proxy server prefix already
		
		if ( preg_match('/http:\/\/[0-9]{1,3}-.*/', $url) != 0 )
		{
			// WAM proxy: this is kind of a rough estimate of a WAM-style
			// proxy link, but I think sufficient for our purposes?
		
			$should_proxy = false;
		}
		elseif ( stristr($url, $proxy_server) )
		{
			// EZProxy
		
			$should_proxy = false;
		}
		
		// finally, if the proxy server entry is blank, then no proxy available
		
		if ( $proxy_server == "" )
		{
			$should_proxy = false;
		}
		
		// if we need to proxy, prefix the proxy server url to the full-text
		// or database link and be done with it!
		
		if ( $should_proxy == true )
		{
			// if WAM proxy, take the base url and port out and 'prefix';
			// otherwise we only support EZPRoxy, so cool to take as else ?
		
			if ( strstr($proxy_server, '{WAM}') )
			{
				$arrMatch = array();
		
				if ( preg_match('/http[s]{0,1}:\/\/([^\/]*)\/{0,1}(.*)/', $url, $arrMatch) != 0 )
				{
					$strPort = "0";
					$arrPort = array();
		
					// port specifically included
		
					if ( preg_match("/:([0-9]{2,5})/", $arrMatch[1], $arrPort) != 0 )
					{
						if ( $arrPort[1] != "80") {
							$strPort = $arrPort[1];
						}
						$arrMatch[1] = str_replace($arrPort[0], "", $arrMatch[1]);
					}
		
					$strBase = str_replace("{WAM}", $strPort . "-" . $arrMatch[1], $proxy_server);
		
					$final =  $strBase . "/" . $arrMatch[2];
		
					$final = str_replace("..", ".", $final);
				}
				else
				{
					throw new \Exception("Could not construct WAM link");
				}
			}
			else
			{
				// check if this is using EZProxy qurl param, in which case urlencode that mo-fo
		
				if ( strstr($proxy_server, "qurl=") )
				{
					$url = urlencode($url);
				}
		
				$final = $proxy_server . $url;
			}
		}
		else
		{
			// just send it along straight-up
		
			$final = $url;
		}
		
		return $final;
	}
}