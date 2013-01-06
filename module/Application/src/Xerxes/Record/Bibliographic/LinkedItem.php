<?php

namespace Xerxes\Record\Bibliographic;

use Xerxes\Marc\DataField;

class LinkedItem
{
	public $title;
	public $edition;
	public $issns = array();
	public $isbns = array();
	public $notes = array();
	
	/**
	 * Creates a new LinkedItem for 78x fields
	 *
	 * @param DataField $datafield
	 */
	
	public function __construct(DataField $datafield)
	{
		foreach ( $datafield->subfield() as $subfield )
		{
			switch ( $subfield->code )
			{
				// $a - Main entry heading (NR)
	
				case 'b': // $b - Edition (NR)
	
					$this->edition = $subfield->value;
					break;
	
					// $c - Qualifying information (NR)
					// $d - Place, publisher, and date of publication (NR)
					// $g - Related parts (R)
					// $h - Physical description (NR)
					// $i - Relationship information (R)
					// $k - Series data for related item (R)
					// $m - Material-specific details (NR)
					// $n - Note (R)
					// $o - Other item identifier (R)
					// $r - Report number (R)
	
				case 's': // $s - Uniform title (NR)
						
					$this->uniform_title = $subfield->value;
					break;
						
				case 't': // $t - Title (NR)
						
					$this->title = $subfield->value;
					break;
						
					// $u - Standard Technical Report Number (NR)
					// $w - Record control number (R)
	
				case 'x': // $x - International Standard Serial Number (NR)
	
					array_push($this->issns, $subfield->value);
					break;
	
					// $y - CODEN designation (NR)
	
				case 'z': // $z - International Standard Book Number (R)
	
					array_push($this->isbns, $subfield->value);
					break;
	
					// $4 - Relationship code (R)
					// $6 - Linkage (NR)
					// $7 - Control subfield (NR)
					//     /0 - Type of main entry heading
					//     /1 - Form of name
					//     /2 - Type of record
					//     /3 - Bibliographic level
					// $8 - Field link and sequence number (R)
	
				default: // everything else is a note
						
					array_push($this->notes, $subfield->value);
					break;
			}
		}
	}
}