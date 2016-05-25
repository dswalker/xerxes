<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Primo;

/**
 * Primo Discipline
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Discipline
{
	public static function convertToPrimoDisciplines(array $disciplines)
	{
		$mapping = self::map();
		$final = array();
		
		foreach ( $disciplines as $discipline )
		{
			if ( in_array($discipline, $mapping) )
			{
				$final[] = $discipline;
			}
			elseif ( array_key_exists($discipline, $mapping) )
			{
				$value = $mapping[$discipline];
				
				if ( $value != "" )
				{
					$final[] = $value;
				}
			}
		}
		
		return $final;
	}
	
	/**
	 * Human readable language name for code
	 * @param unknown $code
	 * @return string
	 */
	
	private function map()
	{
		return array(
			'Agriculture' => 'agriculture_forestry',
			'Anatomy & Physiology' => '',
			'Anthropology' => 'anthropology',
			'Applied Sciences' => '',
			'Architecture' => '',
			'Astronomy & Astrophysics' => '',
			'Biology' => 'biology',
			'Botany' => '',
			'Business' => 'business',
			'Chemistry' => 'chemistry',
			'Computer Science' => 'computer_science',
			'Dance' => '',
			'Dentistry' => '',
			'Diet & Clinical Nutrition' => '',
			'Drama' => '',
			'Ecology' => '',
			'Economics' => 'economics',
			'Education' => 'education',
			'Engineering' => 'engineering',
			'Environmental Sciences' => '',
			'Film' => '',
			'Forestry' => '',
			'Geography' => 'geography',
			'Geology' => '',
			'Government' => '',
			'History & Archaeology' => 'arts_humanities',
			'Human Anatomy & Physiology' => '',
			'International Relations' => '',
			'Journalism & Communications' => '',
			'Languages & Literatures' => 'languages_literature',
			'Law' => 'law',
			'Library & Information Science' => 'library_information_science',
			'Mathematics' => 'mathematics',
			'Medicine' => 'medicine',
			'Meteorology & Climatology' => '',
			'Military & Naval Science' => '',
			'Music' => '',
			'Nursing' => 'nursing',
			'Occupational Therapy & Rehabilitation' => '',
			'Oceanography' => '',
			'Parapsychology & Occult Sciences' => '',
			'Pharmacy, Therapeutics, & Pharmacology' => 'pharmacy_therapeutics_pharmacology',
			'Philosophy' => 'philosophy_religion',
			'Physical Therapy' => '',
			'Physics' => 'physics',
			'Political Science' => 'political_sciences',
			'Psychology' => 'psychology',
			'Public Health' => 'public_health',
			'Recreation & Sports' => '',
			'Religion' => 'philosophy_religion',
			'Sciences' => 'sciences',
			'Social Sciences' => 'social_sciences',
			'Social Welfare & Social Work' => '',
			'Sociology & Social History' => 'sociology',
			'Statistics' => '',
			'Veterinary Medicine' => 'veterinary_medicine',
			'Visual Arts' => '',
			'Women\'s Studies' => '',
			'Zooogy' => '',
		);
	}
}
