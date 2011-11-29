<?php

namespace Xerxes\Record;

use Xerxes\Utility\Parser;

/**
 * Record Format
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: Format.php 2045 2011-11-28 14:17:37Z dwalker.calstate@gmail.com $
 * @package Xerxes
 */

class Format
{
	protected $internal = "";
	protected $normalized = "";
	protected $public = "";
	
	// marc content types

	const Art = "Art";
	const ArtReproduction = "ArtReproduction";
	const Atlas = "Atlas";
	const Book = "Book";
	const BookCollection = "BookCollection";
	const BookComponentPart = "BookComponentPart";
	const BookSeries = "BookSeries";
	const BookSubunit = "BookSubunit";
	const Chart = "Chart";
	const ComputerBibliographicData = "ComputerBibliographicData";
	const ComputerCombination = "ComputerCombination";
	const ComputerDocument = "ComputerDocument";
	const ComputerFile = "ComputerFile";
	const ComputerFont = "ComputerFont";
	const ComputerGame = "ComputerGame";
	const ComputerInteractiveMultimedia = "ComputerInteractiveMultimedia";
	const ComputerNumericData = "ComputerNumericData";
	const ComputerOnlineSystem = "ComputerOnlineSystem";
	const ComputerProgram = "ComputerProgram";
	const ComputerRepresentational = "ComputerRepresentational";
	const ComputerSound = "ComputerSound";
	const Database = "Database";
	const Diorama = "Diorama";
	const Filmstrip = "Filmstrip";
	const FlashCard = "FlashCard";
	const Game = "Game";
	const Globe = "Globe";
	const Graphic = "Graphic";
	const Image = "Image";
	const Kit = "Kit";
	const LooseLeaf = "LooseLeaf";
	const Manuscript = "Manuscript";
	const Map = "Map";
	const MapBound = "MapBound";
	const MapManuscript = "MapManuscript";
	const MapSeparate = "MapSeparate";
	const MapSerial = "MapSerial";
	const MapSeries = "MapSeries";
	const MapSingle = "MapSingle";
	const MicroscopeSlide = "MicroscopeSlide";
	const MixedMaterial = "MixedMaterial";
	const Model = "Model";
	const MotionPicture = "MotionPicture";
	const MusicalScore = "MusicalScore";
	const MusicalScoreManuscript = "MusicalScoreManuscript";
	const MusicRecording = "MusicRecording";
	const Newspaper = "Newspaper";
	const Pamphlet = "Pamphlet";
	const Periodical = "Periodical";
	const PhysicalObject = "PhysicalObject";
	const Picture = "Picture";
	const ProjectedMedium = "ProjectedMedium";
	const Realia = "Realia";
	const Serial = "Serial";
	const SerialComponentPart = "SerialComponentPart";
	const SerialIntegratingResource = "SerialIntegratingResource";
	const Slide = "Slide";
	const SoundRecording = "SoundRecording";
	const SpecialInstructionalMaterial = "SpecialInstructionalMaterial";
	const TechnicalDrawing = "TechnicalDrawing";
	const Thesis = "Thesis";
	const Toy = "Toy";
	const Transparency = "Transparency";
	const Video = "Video";
	const Website = "Website";
	
	// non-marc types

	const Article = "Article"; 
	const Archive = "Archive";
	const BookChapter = "BookChapter"; 
	const BookReview = "BookReview"; 
	const ConferencePaper = "ConferencePaper"; 
	const ConferenceProceeding = "ConferenceProceeding"; 
	const Dissertation = "Dissertation"; 
	const Hearing = "Hearing";
	const Patent = "Patent";
	const PrePrint = "PrePrint";
	const Report = "Report";
	const Review = "Review";
	const TestMeasure = "TestMeasure";
	const WorkingPaper = "WorkingPaper";
	
	// unknown
	
	const Unknown = "Unkown";
	
	public function determineFormat($data_fields)
	{
		$this->setFormat($this->extractFormat($data_fields));
	}
	
	public function setFormat($format)
	{
		$this->internal = $format;
		$this->normalized = $format;
		$this->public = $format;
	}
	
	public function getInternalFormat()
	{
		return $this->internal;
	}
	
	public function getNormalizedFormat()
	{
		return $this->normalized;
	}

	public function getPublicFormat()
	{
		return $this->public;
	}

		/**
	 * Crosswalk the internal identified genre to one available in OpenURL 1.0
	 *
	 * @param string $strFormat		original internal genre/format
	 * @return string				OpenURL genre value
	 */
	
	public function getOpenURLGenre()
	{
		switch ( $this->internal )
		{
			case self::Newspaper :
			case self::Periodical:	
			case self::Serial :
				
				return "journal";
				break;
			
			case self::Article :
			case self::BookReview :
			case self::Review :
			case self::SerialComponentPart :
			case self::TestMeasure :
				
				return "article";
				break;
			
			case self::ConferenceProceeding :
				
				return "proceeding";
				break;

			case self::ConferencePaper :
				
				return "conference";
				break;				
				
			case self::PrePrint :
				
				return "preprint";
				break;
			
			case self::Atlas :
			case self::Book :

				return "book";
				break;

			case self::BookChapter :
			case self::BookComponentPart :

				return "bookitem";
				break;
			
			case self::Report :
				
				return "report";
				break;
			
			case self::Dissertation :
			case self::Thesis :
				
				// not an actual openurl genre
				return "dissertation";
				break;

			case self::BookCollection :
			case self::BookSeries :
			case self::BookSubunit :
			case self::Hearing :
			case self::Manuscript :
			case self::Patent :
			case self::Pamphlet :
			case self::SpecialInstructionalMaterial :

				return "document";
				break;				
				
			default :
				
				return "unknown";
		}
	}
	
	public function extractFormat($data_fields)
	{
		// combine them into a string and lowercase it

		if ( is_array($data_fields) )
		{
			$data_fields = implode(" ", $data_fields);
		}
		
		$data_fields = Parser::strtolower( $data_fields );
		
		if ( strstr( $data_fields, 'dissertation' ) ) return  self::Dissertation; 
		if ( strstr( $data_fields, 'proceeding' ) ) return  self::ConferenceProceeding; 
		if ( strstr( $data_fields, 'conference' ) ) return  self::ConferencePaper; 
		if ( strstr( $data_fields, 'hearing' ) ) return  self::Hearing; 
		if ( strstr( $data_fields, 'working' ) ) return  self::WorkingPaper; 
		if ( strstr( $data_fields, 'book review' ) || strstr( $data_fields, 'review-book' ) ) return  self::BookReview; 
		if ( strstr( $data_fields, 'film review' ) || strstr( $data_fields, 'film-book' ) ) return  self::Review;
		if ( strstr( "$data_fields ", 'review ' ) ) return  self::Review;
		if ( strstr( $data_fields, 'book art' ) || strstr( $data_fields, 'book ch' ) || strstr( $data_fields, 'chapter' ) ) return  self::BookChapter; 
		if ( strstr( $data_fields, 'journal' ) ) return  self::Article; 
		if ( strstr( $data_fields, 'periodical' ) || strstr( $data_fields, 'serial' ) ) return  self::Article; 
		if ( strstr( $data_fields, 'book' ) ) return  self::Book;
        if ( strstr( $data_fields, 'pamphlet' ) ) return  self::Pamphlet;  
        if ( strstr( $data_fields, 'essay' ) ) return  self::Article;
		if ( strstr( $data_fields, 'article' ) ) return  self::Article;

		// if we got this far, just return unknown
		
		return self::Unknown;		
	}
	
	public function __toString()
	{
		return $this->public;
	}
}