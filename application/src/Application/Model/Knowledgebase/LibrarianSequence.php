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

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Librarian Category Join
 *
 * @author David Walker <dwalker@calstate.edu>
 * 
 * @Entity @Table(name="librarians_categories")
 */

class LibrarianSequence
{
	/** @Id @Column(type="integer") @GeneratedValue **/
	protected $id;
	
	/**
	 * @Column(type="integer", nullable=true)
	 * @var int
	 */
	protected $sequence = 999;
	
	/**
	 * @ManyToOne(targetEntity="Librarian", inversedBy="librarian_sequences", cascade={"persist"})
	 * @JoinColumn(name="librarian_id", referencedColumnName="id", onDelete="CASCADE")
	 * @var Librarian
	 */	
	protected $librarian;
	
	/**
	 * @ManyToOne(targetEntity="Category", inversedBy="librarian_sequences")
	 * @JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
	 * @var Category
	 */
	protected $category;
	
	/**
	 * Create new Librarian Sequence
	 */
	
	public function __construct()
	{
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * @return int
	 */
	public function getSequence()
	{
		return $this->sequence;
	}

	/**
	 * @param int $sequence
	 */
	public function setSequence($sequence)
	{
		$this->sequence = $sequence;
	}

	/**
	 * @return Librarian
	 */
	public function getLibrarian()
	{
		return $this->librarian;
	}

	/**
	 * @param Librarian $librarian
	 */
	public function setLibrarian(Librarian $librarian)
	{
		$this->librarian = $librarian;
	}

	/**
	 * @param Category $category
	 */
	public function setCategory(Category $category)
	{
		$this->category = $category;
	}

	/**
	 * @return Category
	 */
	public function getCategory()
	{
		return $this->category;
	}	
	
	/**
	 * @return array
	 */
	
	public function toArray()
	{
		$final = array();
	
		foreach ( $this as $key => $value )
		{
			if ( $key == 'category')
			{
				continue;
			}
			
			if ( $key == 'librarian')
			{
				$final[$key] = $value->toArray();
			}
			else
			{
				$final[$key] = $value;
			}
		}
	
		return $final;
	}
}