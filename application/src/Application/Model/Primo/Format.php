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
 * Primo Format
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Format
{
	public static function toDisplay($name)
	{
		$map = self::map();
		
		if ( array_key_exists($name, $map) )
		{
			return $map[$name];
		}
		else
		{
			return $name;
		}
	}
	
	public static function fromDisplay($name)
	{
		$map = array_flip(self::map());
		
		if ( array_key_exists($name, $map) )
		{
			return $map[$name];
		}
		else
		{
			return $name;
		}
	}
	
	private function map()
	{
		return array(
			'articles' => 'Articles',
			'newspaper_articles' => 'Newspaper Articles',
			'reviews' => 'Reviews',
			'Dissertations' => 'Dissertations',
			'research_datasets' => 'Research Datasets',
			'books' => 'Books',
			'conference_proceedings' => 'Conference Proceedings',
			'journals' => 'Journals',
			'images' => 'Images',
			'audio_video' => 'Audio/Video',
			'reference_entrys' => 'Reference Entries',
			'databases' => 'Databases',
			'scores' => 'Scores',
			'maps' => 'Maps',
			'websites' => 'Websites',
			'statistical_data_sets' => 'Statistical Data Sets',
			'legal_documents' => 'Legal Documents',
			'patents' => 'Patents',
			'government_documents' => 'Government Documents'
		);
	}
}
