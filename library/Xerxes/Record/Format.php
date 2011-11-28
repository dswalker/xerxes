<?php

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

class Xerxes_Record_Format
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
			case Xerxes_Record_Format::Newspaper :
			case Xerxes_Record_Format::Periodical:	
			case Xerxes_Record_Format::Serial :
				
				return "journal";
				break;
			
			case Xerxes_Record_Format::Article :
			case Xerxes_Record_Format::BookReview :
			case Xerxes_Record_Format::Review :
			case Xerxes_Record_Format::SerialComponentPart :
			case Xerxes_Record_Format::TestMeasure :
				
				return "article";
				break;
			
			case Xerxes_Record_Format::ConferenceProceeding :
				
				return "proceeding";
				break;

			case Xerxes_Record_Format::ConferencePaper :
				
				return "conference";
				break;				
				
			case Xerxes_Record_Format::PrePrint :
				
				return "preprint";
				break;
			
			case Xerxes_Record_Format::Atlas :
			case Xerxes_Record_Format::Book :

				return "book";
				break;

			case Xerxes_Record_Format::BookChapter :
			case Xerxes_Record_Format::BookComponentPart :

				return "bookitem";
				break;
			
			case Xerxes_Record_Format::Report :
				
				return "report";
				break;
			
			case Xerxes_Record_Format::Dissertation :
			case Xerxes_Record_Format::Thesis :
				
				// not an actual openurl genre
				return "dissertation";
				break;

			case Xerxes_Record_Format::BookCollection :
			case Xerxes_Record_Format::BookSeries :
			case Xerxes_Record_Format::BookSubunit :
			case Xerxes_Record_Format::Hearing :
			case Xerxes_Record_Format::Manuscript :
			case Xerxes_Record_Format::Patent :
			case Xerxes_Record_Format::Pamphlet :
			case Xerxes_Record_Format::SpecialInstructionalMaterial :

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
		
		$data_fields = Xerxes_Framework_Parser::strtolower( $data_fields );
		
		if ( strstr( $data_fields, 'dissertation' ) ) return  Xerxes_Record_Format::Dissertation; 
		if ( strstr( $data_fields, 'proceeding' ) ) return  Xerxes_Record_Format::ConferenceProceeding; 
		if ( strstr( $data_fields, 'conference' ) ) return  Xerxes_Record_Format::ConferencePaper; 
		if ( strstr( $data_fields, 'hearing' ) ) return  Xerxes_Record_Format::Hearing; 
		if ( strstr( $data_fields, 'working' ) ) return  Xerxes_Record_Format::WorkingPaper; 
		if ( strstr( $data_fields, 'book review' ) || strstr( $data_fields, 'review-book' ) ) return  Xerxes_Record_Format::BookReview; 
		if ( strstr( $data_fields, 'film review' ) || strstr( $data_fields, 'film-book' ) ) return  Xerxes_Record_Format::Review;
		if ( strstr( "$data_fields ", 'review ' ) ) return  Xerxes_Record_Format::Review;
		if ( strstr( $data_fields, 'book art' ) || strstr( $data_fields, 'book ch' ) || strstr( $data_fields, 'chapter' ) ) return  Xerxes_Record_Format::BookChapter; 
		if ( strstr( $data_fields, 'journal' ) ) return  Xerxes_Record_Format::Article; 
		if ( strstr( $data_fields, 'periodical' ) || strstr( $data_fields, 'serial' ) ) return  Xerxes_Record_Format::Article; 
		if ( strstr( $data_fields, 'book' ) ) return  Xerxes_Record_Format::Book;
        if ( strstr( $data_fields, 'pamphlet' ) ) return  Xerxes_Record_Format::Pamphlet;  
        if ( strstr( $data_fields, 'essay' ) ) return  Xerxes_Record_Format::Article;
		if ( strstr( $data_fields, 'article' ) ) return  Xerxes_Record_Format::Article;

		// if we got this far, just return unknown
		
		return Xerxes_Record_Format::Unknown;		
	}
	
	public function __toString()
	{
		return $this->public;
	}
}