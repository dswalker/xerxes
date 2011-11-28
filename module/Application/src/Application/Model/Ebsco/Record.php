<?php

class Xerxes_Model_Ebsco_Record extends Xerxes_Record
{
	protected $source = "ebsco";
	
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
						$author = new Xerxes_Record_Author((string) $auth, "", "personal");
						
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
				if ( (string) $ui["type"] == "doi" )
				{
					// doi
					$this->doi = (string) $ui;
				}
				elseif ( (string) $ui["type"] == "" )
				{
					// ebsco id
					$this->record_id = $short_db_name . "-" . (string) $ui;
				}
			}
			
			// full-text
			
			if ( count($article->formats->fmt) > 0 )
			{
				foreach ( $article->formats->fmt as $fmt )
				{
					if ( (string) $fmt["type"] == "T" )
					{
						$this->links[] = new Xerxes_Record_Link($xml->plink, Xerxes_Record_Link::HTML);
					}
					if ( (string) $fmt["type"] == "P" )
					{
						// pdf link is set only if there is both html and pdf full-text?
						
						$pdf_link = $xml->pdfLink;
						
						if ( $pdf_link == "" )
						{
							$pdf_link = $xml->plink;
						}
						
						$this->links[] = new Xerxes_Record_Link($xml->plink, Xerxes_Record_Link::PDF);
					}
				}
			}
			
			// start page
			$this->start_page = $article->ppf;
			
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
					$author = new Xerxes_Record_Author((string) $auth, "", "personal");
					array_push($this->authors, $author);
				}
			}

			// subjects
			
			foreach ( $article->su as $subject )
			{
				$subject_object = new Xerxes_Record_Subject();
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
				array_push($this->notes, (string) $doc_type);
			}

			foreach ( $article->pubtype as $pubtype )
			{
				array_push($formats, (string) $pubtype);
			}			
			
			// format 
			
			$this->format->determineFormat($formats);
			
			//language
			
			$this->language = (string)$article->language;
		}
	}
}
