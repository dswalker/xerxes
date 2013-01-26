<?php

namespace Application\Model\Availability;

/**
 * Availability factory
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class AvailabilityFactory
{
	/**
	 * Create Availability object
	 * 
	 * @param string $name
	 * @return AvailabilityInterface
	 */
	
	public function getAvailabilityObject($name)
	{	
		$name = preg_replace('/\W/', '', $name);
		
		$upper_name = ucfirst($name);
		
		// main class
		
		$class_name = 'Application\Model\Availability' . '\\' . $upper_name . '\\' . $upper_name;
		
		// local custom version
		
		$local_file = "custom/Availability/$upper_name/$upper_name.php";
		
		if ( file_exists($local_file) )
		{
			require_once($local_file);
			
			$class_name = 'Local\Availability' . '\\' . $upper_name . '\\' . $upper_name;
			
			if ( ! class_exists($class_name) )
			{
				throw new \Exception("the custom availability class '$name' should have a class called '$class_name'");
			}
		}

		// make it

		$availability = new $class_name();
		
		if ( ! $availability instanceof AvailabilityInterface )
		{
			throw new \Exception("class '$class_name' for the '$name' availability class must extend AvailabilityInterface");
		}
		
		return $availability;
	}
}