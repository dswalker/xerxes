<?php

namespace Application\View;

use Zend\Http\Request,
	Zend\Mvc\MvcEvent,
	Zend\Mvc\Router\RouteMatch;

class Helper
{
	protected $router; // route stack
	
	public function __construct(MvcEvent $e)
	{
		$this->router = $e->getRouter();
	}	
	
	public function url_for($params = array(), $full = false, $force_secure = false, $options = array())
	{
		// use the default route if no option supplied
	
		if ( count($options) == 0 )
		{
			$options["name"] = "default";
		}
	
		if ( null === $this->router )
		{
			return '';
		}
	
		
		// this only returns the route, no querystring
	
		$url = $this->router->assemble($params, $options);
		
		// remove trailing '/index' from generated URLs.
	
		if ((6 <= strlen($url)) && '/index' == substr($url, -6))
		{
			$url = substr($url, 0, strlen($url) - 6);
		}
		
		
		// append query string
		
		$query_string = array();
		
		// figure out which of our params matched the route
		
		$request = new Request();
		$request->setUri($url);

		$route_match = $this->router->match($request);
		
		// remove those from the supplied params
		
		if ( $route_match instanceof RouteMatch )
		{
			$in_route = array_keys($route_match->getParams());
			
			foreach ( $params as $key => $value )
			{
				if ( ! in_array($key, $in_route) )
				{
					$query_string[$key] = $value;
				}
			}
		}
	
		// take any remaining as the query string
	
		if ( count($query_string) > 0 )
		{
			$url .= "?";
	
			$x = 0;
	
			foreach ( $query_string as $name => $value )
			{
				if ( $value == "" )
				{
					continue;
				}
				
				if ( $x > 0 ) // first param doesn't need & prefix
				{
					$url .= '&';
				}
	
				$url .= $name . '=' . urlencode($value);
	
				$x++;
			}
		}
	
		// is it supposed to be a full url?
	
		if ( $full == true )
		{
			$base = "http://";
	
			if ( $force_secure == true )
			{
				$base = "https://";
			}
	
			$base .= $this->server()->get('SERVER_NAME');
	
			$url = $base .= $url;
		}
	
		return $url;
	}
}