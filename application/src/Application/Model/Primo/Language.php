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

class Language
{
	/**
	 * Human readable language name for code
	 * @param unknown $code
	 * @return string
	 */
	
	public static function getLanguageLabel($code)
	{
		$lanuages = array(
				'abk' => 'Abkhazian',
				'ace' => 'Achinese',
				'ach' => 'Acoli',
				'afr' => 'Afrikaans',
				'alb' => 'Albanian',
				'ale' => 'Aleut',
				'amh' => 'Amharic',
				'ara' => 'Arabic',
				'arm' => 'Armenian',
				'aze' => 'Azerbaijani',
				'baq' => 'Basque',
				'ben' => 'Bengali',
				'bos' => 'Bosnian',
				'bre' => 'Breton',
				'bur' => 'Burmese',
				'cat' => 'Catalan',
				'ces' => 'Czech',
				'chi' => 'Chinese',
				'cop' => 'Coptic',
				'cor' => 'Cornish',
				'cym' => 'Welsh',
				'cze' => 'Czech',
				'dan' => 'Danish',
				'deu' => 'German',
				'dut' => 'Dutch',
				'ell' => 'Greek',
				'eng' => 'English',
				'epo' => 'Esperanto',
				'est' => 'Estonian',
				'eus' => 'Basque',
				'fas' => 'Persian',
				'fil' => 'Filipino',
				'fin' => 'Finnish',
				'fra' => 'French',
				'fre' => 'French',
				'gem' => 'Germanic languages',
				'geo' => 'Georgian',
				'ger' => 'German',
				'gla' => 'Gaelic',
				'gle' => 'Irish',
				'glg' => 'Galician',
				'grc' => 'Greek',
				'gre' => 'Greek',
				'guj' => 'Gujarati',
				'hat' => 'Haitian',
				'haw' => 'Hawaiian',
				'heb' => 'Hebrew',
				'hin' => 'Hindi',
				'hmn' => 'Hmong',
				'hrv' => 'Croatian',
				'hun' => 'Hungarian',
				'hye' => 'Armenian',
				'ice' => 'Icelandic',
				'ilo' => 'Iloko',
				'ind' => 'Indonesian',
				'ira' => 'Iranian',
				'isl' => 'Icelandic',
				'ita' => 'Italian',
				'jav' => 'Javanese',
				'jpn' => 'Japanese',
				'kal' => 'Greenlandic',
				'kat' => 'Georgian',
				'kor' => 'Korean',
				'kur' => 'Kurdish',
				'lat' => 'Latin',
				'lit' => 'Lithuanian',
				'lol' => 'Mongo',
				'mac' => 'Macedonian',
				'mal' => 'Malayalam',
				'mao' => 'Maori',
				'map' => 'Austronesian languages',
				'may' => 'Malay',
				'mkd' => 'Macedonian',
				'mkh' => 'Mon-Khmer',
				'mon' => 'Mongolian',
				'mri' => 'Maori',
				'msa' => 'Malay',
				'mya' => 'Burmese',
				'nav' => 'Navajo',
				'nep' => 'Nepali',
				'nld' => 'Dutch',
				'nob' => 'Norwegian',
				'nor' => 'Norwegian',
				'pan' => 'Panjabi',
				'per' => 'Persian',
				'phi' => 'Philippine languages',
				'pol' => 'Polish',
				'por' => 'Portuguese',
				'pus' => 'Pashto',
				'rum' => 'Romanian',
				'rus' => 'Russian',
				'san' => 'Sanskrit',
				'sco' => 'Scots',
				'scc' => 'Serbo-Croatian',
				'scr' => 'Serbian',
				'slk' => 'Slovak',
				'slo' => 'Slovak',
				'slv' => 'Slovenian',
				'smo' => 'Samoan',
				'som' => 'Somali',
				'spa' => 'Castilian',
				'sqi' => 'Albanian',
				'srp' => 'Serbian',
				'sun' => 'Sundanese',
				'sux' => 'Sumerian',
				'swa' => 'Swahili',
				'swe' => 'Swedish',
				'syr' => 'Syriac',
				'tah' => 'Tahitian',
				'tam' => 'Tamil',
				'tgl' => 'Tagalog',
				'tha' => 'Thai',
				'tib' => 'Tibetan',
				'tlh' => 'Klingon',
				'tur' => 'Turkish',
				'ukr' => 'Ukrainian',
				'urd' => 'Urdu',
				'uzb' => 'Uzbek',
				'vie' => 'Vietnamese',
				'wel' => 'Welsh',
				'yid' => 'Yiddish',
				'zho' => 'Chinese',
				'zul' => 'Zulu',
				'und' => 'Undefined',
		);
	
		if ( array_key_exists($code, $lanuages) )
		{
			return $lanuages[$code];
		}
		else
		{
			return $code;
		}
	}
}
