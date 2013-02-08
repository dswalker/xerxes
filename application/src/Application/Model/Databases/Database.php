<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Databases;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Database
 *
 * @author David Walker <dwalker@calstate.edu>
 * 
 * @Entity @Table(name="databases")
 */

class Database 
{
	/** @Id @Column(type="integer") @GeneratedValue **/
	protected $id;
	
	/**
	 * @Column(type="string")
	 * @var string 
	 */
	protected $source_id;
	
	/**
	 * @Column(type="string")
	 * @var string 
	 */
	protected $owner;
	
	/** 
	 * @Column(type="string")
	 * @var string 
	 */
	protected $title;
	
	/** 
	 * @Column(type="string")
	 * @var string 
	 */
	protected $link;
	
	/**
	 * @Column(type="text")
	 * @var string
	 */
	protected $description;
	
	/**
	 * @Column(type="boolean")
	 * @var bool
	 */
	protected $active;
	
	/** 
	 * @Column(type="string")
	 * @var string
	 */
	protected $language;
	
	/** 
	 * @Column(type="text")
	 * @var string
	 */
	protected $notes;
	
	/** 
	 * @Column(type="boolean")
	 * @var bool
	 */
	protected $proxy;
	
	/**
	 * @Column(type="date")
	 * @var DateTime
	 */
	protected $date_new_expiry;
	
	/**
	 * @Column(type="date")
	 * @var DateTime
	 */
	protected $date_trial_expiry;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $creator;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $publisher;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $coverage;
	
	/**
	 * @Column(type="text")
	 * @var string
	 */
	protected $search_hints;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $link_guide;
	
	/**
	 * @OneToMany(targetEntity="AlternateTitle", mappedBy="database")
	 * @var AlternateTitle[]
	 */
	protected $alternate_titles;
	
	/**
	 * @OneToMany(targetEntity="Keyword", mappedBy="database")
	 * @var Keyword[]
	 */
	protected $keywords;
	
	/**
	 * @ManyToOne(targetEntity="Subcategory", inversedBy="databases")
	 * @var Subcategory
	 */
	protected $subcategory;	
	
	/**
	 * Create new Database
	 */
	
	public function __construct()
	{
		$this->keywords = new ArrayCollection();
		$this->alternate_titles = new ArrayCollection();
	}
	
	/**
	 * @return string
	 */
	public function getSourceId()
	{
		return $this->source_id;
	}

	/**
	 * @param string $source_id
	 */
	public function setSourceId($source_id)
	{
		$this->source_id = $source_id;
	}

	/**
	 * @return string
	 */
	public function getOwner()
	{
		return $this->owner;
	}

	/**
	 * @param string $owner
	 */
	public function setOwner($owner)
	{
		$this->owner = $owner;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getLink()
	{
		return $this->link;
	}

	/**
	 * @param string $link
	 */
	public function setLink($link)
	{
		$this->link = $link;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * @return string
	 */
	public function getActive()
	{
		return $this->active;
	}

	/**
	 * @param boolean $active
	 */
	public function setActive($active)
	{
		$this->active = $active;
	}

	/**
	 * @return boolean
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * @param string $language
	 */
	public function setLanguage($language)
	{
		$this->language = $language;
	}

	/**
	 * @return string
	 */
	public function getNotes()
	{
		return $this->notes;
	}

	/**
	 * @param string $notes
	 */
	public function setNotes($notes)
	{
		$this->notes = $notes;
	}

	/**
	 * @return string
	 */
	public function getProxy()
	{
		return $this->proxy;
	}

	/**
	 * @param boolean $proxy
	 */
	public function setProxy($proxy)
	{
		$this->proxy = $proxy;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateNewExpiry()
	{
		return $this->date_new_expiry;
	}

	/**
	 * @param \DateTime $date_new_expiry
	 */
	public function setDateNewExpiry(\DateTime $date_new_expiry)
	{
		$this->date_new_expiry = $date_new_expiry;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateTrialExpiry()
	{
		return $this->date_trial_expiry;
	}

	/**
	 * @param \DateTime $date_trial_expiry
	 */
	public function setDateTrialExpiry(\DateTime $date_trial_expiry)
	{
		$this->date_trial_expiry = $date_trial_expiry;
	}

	/**
	 * @return string
	 */
	public function getCreator()
	{
		return $this->creator;
	}

	/**
	 * @param string $creator
	 */
	public function setCreator($creator)
	{
		$this->creator = $creator;
	}

	/**
	 * @return string
	 */
	public function getPublisher()
	{
		return $this->publisher;
	}

	/**
	 * @param string $publisher
	 */
	public function setPublisher($publisher)
	{
		$this->publisher = $publisher;
	}

	/**
	 * @return string
	 */
	public function getCoverage()
	{
		return $this->coverage;
	}

	/**
	 * @param string $coverage
	 */
	public function setCoverage($coverage)
	{
		$this->coverage = $coverage;
	}

	/**
	 * @return string
	 */
	public function getSearchHints()
	{
		return $this->search_hints;
	}

	/**
	 * @param string $search_hints
	 */
	public function setSearchHints($search_hints)
	{
		$this->search_hints = $search_hints;
	}

	/**
	 * @return string
	 */
	public function getLink_guide() 
	{
		return $this->link_guide;
	}

	/**
	 * @param string $link_guide
	 */
	public function setLink_guide($link_guide) 
	{
		$this->link_guide = $link_guide;
	}

	/**
	 * @return AlternateTitle[]
	 */
	public function getAlternateTitles() 
	{
		return $this->alternate_titles;
	}

	/**
	 * @param AlternateTitle $alternate_titles
	 */
	public function addAlternateTitle(AlternateTitle $alternate_title) 
	{
		$this->alternate_titles[] = $alternate_title;
	}

	/**
	 * @return Keyword[]
	 */
	public function getKeywords() 
	{
		return $this->keywords;
	}

	/**
	 * @param Keyword $keywords
	 */
	public function addKeyword($keyword) 
	{
		$this->keywords[] = $keyword;
	}

	/**
	 * @param Subcategory $subcategory
	 */
	public function setSubcategory(Subcategory $subcategory) 
	{
		$this->subcategory = $subcategory;
	}


}