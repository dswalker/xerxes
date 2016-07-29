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
			'Anatomy & Physiology' => 'medicine',
			'Anthropology' => 'anthropology',
			'Applied Sciences' => 'sciences',
			'Architecture' => 'arts_humanities',
			'Astronomy & Astrophysics' => 'sciences',
			'Biology' => 'biology',
			'Botany' => 'sciences',
			'Business' => 'business',
			'Chemistry' => 'chemistry',
			'Computer Science' => 'computer_science',
			'Dance' => 'arts_humanities',
			'Dentistry' => 'medicine',
			'Diet & Clinical Nutrition' => 'medicine',
			'Drama' => 'languages_literature',
			'Ecology' => 'sciences',
			'Economics' => 'economics',
			'Education' => 'education',
			'Engineering' => 'engineering',
			'Environmental Sciences' => 'earth_sciences',
			'Film' => 'arts_humanities',
			'Forestry' => 'earth_sciences',
			'Geography' => 'geography',
			'Geology' => 'earth_sciences',
			'Government' => 'law',
			'History & Archaeology' => 'arts_humanities',
			'Human Anatomy & Physiology' => 'medicine',
			'International Relations' => 'political_sciences',
			'Journalism & Communications' => 'social_sciences',
			'Languages & Literatures' => 'languages_literature',
			'Law' => 'law',
			'Library & Information Science' => 'library_information_science',
			'Mathematics' => 'mathematics',
			'Medicine' => 'medicine',
			'Meteorology & Climatology' => 'earth_sciences',
			'Military & Naval Science' => 'political_sciences',
			'Music' => 'arts_humanities',
			'Nursing' => 'nursing',
			'Occupational Therapy & Rehabilitation' => 'medicine',
			'Oceanography' => 'earth_sciences',
			'Parapsychology & Occult Sciences' => 'philosophy_religion',
			'Pharmacy, Therapeutics, & Pharmacology' => 'pharmacy_therapeutics_pharmacology',
			'Philosophy' => 'philosophy_religion',
			'Physical Therapy' => 'medicine',
			'Physics' => 'physics',
			'Political Science' => 'political_sciences',
			'Psychology' => 'psychology',
			'Public Health' => 'public_health',
			'Recreation & Sports' => 'medicine',
			'Religion' => 'philosophy_religion',
			'Sciences' => 'sciences',
			'Social Sciences' => 'social_sciences',
			'Social Welfare & Social Work' => 'social_sciences',
			'Sociology & Social History' => 'sociology',
			'Statistics' => 'mathematics',
			'Veterinary Medicine' => 'veterinary_medicine',
			'Visual Arts' => 'arts_humanities',
			'Women\'s Studies' => 'social_sciences',
			'Zooogy' => 'veterinary_medicine',
		);
	}
}
