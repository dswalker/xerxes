<?php

namespace Application\Model\Ebsco;

use Xerxes\Utility\Registry;

use Xerxes;

class Record extends Xerxes\Record
{
	protected $source = "Ebsco";
	
	public function map()
	{
		$xml = simplexml_load_string($this->document->saveXML());
		$control_info = $xml->header->controlInfo;
		
		$this->database_name = (string) $xml->header["longDbName"];
		$short_db_name = (string) $xml->header["shortDbName"];
		
		$book = $control_info->bkinfo;
		$journal = $control_info->jinfo;
		$publication = $control_info->pubinfo;
		$article = $control_info->artinfo;

		if ( count($book) > 0 )
		{
			// usually an editor
			
			if ( count($book->aug) > 0 )
			{
				if ( count($book->aug->au) > 0 )
				{
					foreach ( $book->aug->au as $auth )
					{
						$author = new Xerxes\Record\Author((string) $auth, "", "personal");
						
						if ( (string) $auth["type"] == "editor" )
						{
							$this->editor = true;
						}
						
						array_push($this->authors, $author);
					}
				}
			}
			
			// isbn
			
			if ( count($book->isbn) > 0 )
			{
				foreach ( $book->isbn as $isbn )
				{
					array_push($this->isbns, $isbn);
				}
			}
		}		
		
		if ( count($journal) > 0 )
		{
			// journal title
			
			$this->journal_title = (string) $journal->jtl;
			
			// issn
			
			foreach ( $journal->issn as $issn  )
			{
				array_push($this->issns, $issn);
			}
		}
		
		if ( count($publication) > 0 )
		{
			// year 
			$this->year = (string) $publication->dt["year"];
			
			// volume 
			$this->volume = (string) $publication->vid;
			
			// issue
			$this->issue = (string) $publication->iid;
		}
		
		if ( count($article) > 0 )
		{
			// identifiers
			
			foreach ( $article->ui as $ui )
			{
				$id_number = (string) $ui;
				
				if ( (string) $ui["type"] == "doi" )
				{
					// doi
					$this->doi = $id_number;
				}
				elseif ( (string) $ui["type"] == "" )
				{
					// ebsco id
					$this->record_id = $short_db_name . "-" . $id_number;
					
					// eric doc number
					
					if ( $short_db_name == "eric" && substr($id_number, 0, 2) == "ED" )
					{
						$this->eric_number = $id_number;
						$this->issns = array();
					}
				}
			}
			
			// full-text
			
			if ( count($article->formats->fmt) > 0 )
			{
				foreach ( $article->formats->fmt as $fmt )
				{
					$link = '';
					$type = '';
					
					if ( (string) $fmt["type"] == "T" )
					{
						$link = $xml->plink;
						$type = Xerxes\Record\Link::HTML;
					}
					elseif ( (string) $fmt["type"] == "P" )
					{
						// pdf link is set only if there is both html and pdf full-text?
						
						$link = $xml->pdfLink;
						
						if ( $link == "" )
						{
							$link = $xml->plink;
						}

						$type = Xerxes\Record\Link::PDF;
					}
					
					$this->links[] = new Xerxes\Record\Link($link, $type );
				}
			}
			
			// start page
			
			$this->start_page = (string) $article->ppf;
			
			// extent
			
			$this->extent = (string) $article->ppct;
			
			// end page 
			
			$pages = explode('-',(string) $article->pages);
			
			if ( count($pages) > 1 )
			{
				$this->end_page = $pages[1];
			}

			// title
			$this->title = (string) $article->tig->atl;
			
			// authors
			
			if ( count($article->aug->au) > 0 )
			{
				foreach ( $article->aug->au as $auth )
				{
					$author = new Xerxes\Record\Author((string) $auth, "", "personal");
					array_push($this->authors, $author);
				}
			}

			// subjects
			
			foreach ( $article->su as $subject )
			{
				$subject_object = new Xerxes\Record\Subject();
				$subject_object->value = (string) $subject;
				$subject_object->display = (string) $subject;
				
				array_push($this->subjects, $subject_object);
			}			
			
			// abstract
			
			$this->abstract = (string) $article->ab;
			$this->summary = $this->abstract;
			
			// format
			
			$formats = array();
			
			foreach ( $article->doctype as $doc_type )
			{
				array_push($formats, (string) $doc_type);
			}

			foreach ( $article->pubtype as $pubtype )
			{
				array_push($formats, (string) $pubtype);
			}
			
			$this->notes = array_merge_recursive($this->notes, $formats);
			
			// format 
			// @todo map this to internal
			
			$this->format->determineFormat($formats);
			
			// language
			
			$this->language = (string)$article->language;
		}
	}
}
