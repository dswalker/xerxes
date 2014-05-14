<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Knowledgebase;

use Xerxes\Utility\Proxy;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Database
 *
 * @author David Walker <dwalker@calstate.edu>
 * 
 * @Entity @Table(name="research_databases")
 */

class Database 
{
	/** @Id @Column(type="integer") @GeneratedValue **/
	protected $id;

	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $owner;	
	
	/**
	 * @Column(type="string", nullable=true)
	 * @var string 
	 */
	protected $source_id;
	
	/** 
	 * @Column(type="string")
	 * @var string 
	 */
	protected $title;
	
	/** 
	 * @Column(type="string", length=1000)
	 * @var string 
	 */
	protected $link;
	
	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $description;
	
	/**
	 * @Column(type="boolean")
	 * @var bool
	 */
	protected $active = true;
	
	/** 
	 * @Column(type="string", nullable=true)
	 * @var string
	 */
	protected $language = null;
	
	/** 
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $notes = null;
	
	/** 
	 * @Column(type="boolean")
	 * @var bool
	 */
	protected $proxy = true;
	
	/**
	 * @Column(type="date", nullable=true)
	 * @var DateTime
	 */
	protected $date_new_expiry = null;
	
	/**
	 * @Column(type="date", nullable=true)
	 * @var DateTime
	 */
	protected $date_trial_expiry = null;
	
	/**
	 * @Column(type="string", nullable=true)
	 * @var string
	 */
	protected $creator = null;
	
	/**
	 * @Column(type="string", nullable=true)
	 * @var string
	 */
	protected $publisher = null;
	
	/**
	 * @Column(type="string", nullable=true)
	 * @var string
	 */
	protected $coverage = null;
	
	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $search_hints = null;
	
	/**
	 * @Column(type="string", nullable=true)
	 * @Assert\Url()
	 * @var string
	 */
	protected $link_guide = null;
	
	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $type;
	
	/**
	 * @OneToMany(targetEntity="AlternateTitle", mappedBy="database", cascade={"persist", "remove"}, orphanRemoval=true)
	 * @var ArrayCollection AlternateTitle[]
	 */
	protected $alternate_titles;
	
	/**
	 * @OneToMany(targetEntity="Keyword", mappedBy="database", cascade={"persist", "remove"}, orphanRemoval=true)
	 * @var ArrayCollection Keyword[]
	 */
	protected $keywords;
	
	/**
	 * @OneToMany(targetEntity="DatabaseSequence", mappedBy="database")
	 * @var ArrayCollection DatabaseSequence[]
	 */
	protected $database_sequence;
	
	/**
	 * Create new Database
	 */
	
	public function __construct()
	{
		$this->keywords = new ArrayCollection();
		$this->alternate_titles = new ArrayCollection();
		$this->database_sequence = new ArrayCollection();
		$this->types = new ArrayCollection();
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}	
	
	/**
	 * @return string
	 */
	public function getOwner()
	{
		return $this->owner;
	}
	
	/**
	 * @param string $source_id
	 */
	public function setOwner($owner)
	{
		$this->owner = $owner;
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
	 * @return bool
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
	public function getLinkGuide() 
	{
		return $this->link_guide;
	}

	/**
	 * @param string $link_guide
	 */
	public function setLinkGuide($link_guide) 
	{
		$this->link_guide = $link_guide;
	}
	
	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * @param string $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}
	
	/**
	 * @return AlternateTitle[]
	 */
	public function getAlternateTitles() 
	{
		return $this->alternate_titles->toArray();
	}

	/**
	 * @param string $name
	 */
	public function addAlternateTitle($name) 
	{
		$alternate_title = new AlternateTitle($name);
		$alternate_title->setDatabase($this);
		
		$this->alternate_titles->add($alternate_title);
	}

	/**
	 * @return Keyword[]
	 */
	public function getKeywords() 
	{
		return $this->keywords->toArray();
	}
	
	/**
	 * @param string $values
	 */
	public function setKeywords($values)
	{
		$keywords = explode(',', $values);
		
		// remove existing ones
		
		$this->keywords->clear();
		
		// add new ones
		
		foreach ( $keywords as $keyword )
		{
			$this->addKeyword($keyword);
		}
	}

	/**
	 * @param Keyword $keywords
	 */
	public function addKeyword($keyword) 
	{
		// don't add a keyword that already exists
		
		foreach ( $this->keywords as $keyword_object )
		{
			if ( $keyword_object->getValue() == $keyword )
			{
				return null;
			}
		}
		
		$keyword_object = new Keyword($keyword);
		$keyword_object->setDatabase($this);
		
		$this->keywords->add($keyword_object);
	}

	/**
	 * @param DatabaseSequence $subcategory
	 */
	public function addDatabaseSequence(DatabaseSequence $sequence) 
	{
		$this->database_sequence[] = $sequence;
	}
	
	/**
	 * Proxied version of URL
	 * 
	 * @return string
	 */
	
	public function getProxyUrl()
	{
		$url = $this->getLink(); // main link
		
		// databases marked as subscription should be proxied
		
		if ( $this->getProxy() == true )
		{
			$url = Proxy::getProxyLink($url);
		}
		
		return $url;
	}
	
	/**
	 * @return array
	 */
	
	public function toArray()
	{
		$final = array();
		
		foreach ( $this as $key => $value )
		{
			if ( $value == "")
			{
				continue;
			}
			
			if ( $key == 'database_sequence')
			{
				continue;
			}
			elseif ( $key == 'keywords' || $key == 'alternate_titles' )
			{
				$second = array();
				
				foreach ( $this->$key->toArray() as $object )
				{
					$second[] = $object->getValue();
				}
				
				if (count($second) == 0 )
				{
					continue;
				}
				
				$final[$key] = $second;
			}
			else
			{
				$final[$key] = $value;
			}
		}
		
		return $final;
	}
}