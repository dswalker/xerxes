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
 * Librarian
 *
 * @author David Walker <dwalker@calstate.edu>
 * 
 * @Entity @Table(name="librarians")
 */

class Librarian
{
	/** @Id @Column(type="integer") @GeneratedValue **/
	protected $id;	
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $name;
	
	/**
	 * @Column(type="string", nullable=true)
	 * @var string
	 */
	protected $link;

	/**
	 * @Column(type="string", nullable=true)
	 * @var string
	 */
	protected $image;	
	
	/**
	 * @Column(type="string", nullable=true)
	 * @var string
	 */
	protected $email;
	
	/**
	 * @Column(type="string", nullable=true)
	 * @var string
	 */
	protected $phone;
	
	/**
	 * @Column(type="string", nullable=true)
	 * @var string
	 */
	protected $office;
	
	/**
	 * @Column(type="string", nullable=true)
	 * @var string
	 */
	protected $office_hours;
	
    /**
     * @ManyToMany(targetEntity="Category", inversedBy="librarians")
     * @JoinTable(name="librarians_categories")
     */
	protected $categories;
	
	/**
	 * New Librarian
	 */
	public function __construct()
	{
		$this->categories = new ArrayCollection();
	}	
	
	/**
	 * @return @int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param field_type $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return @string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return @string
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
	 * @return @string
	 */
	public function getImage()
	{
		return $this->image;
	}

	/**
	 * @param string $image
	 */
	public function setImage($image)
	{
		$this->image = $image;
	}

	/**
	 * @return @string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @param string $email
	 */
	public function setEmail($email)
	{
		$this->email = $email;
	}

	/**
	 * @return @string
	 */
	public function getPhone()
	{
		return $this->phone;
	}

	/**
	 * @param string $phone
	 */
	public function setPhone($phone)
	{
		$this->phone = $phone;
	}

	/**
	 * @return @string
	 */
	public function getOffice()
	{
		return $this->office;
	}

	/**
	 * @param string $office
	 */
	public function setOffice($office)
	{
		$this->office = $office;
	}

	/**
	 * @return @string
	 */
	public function getOfficeHours()
	{
		return $this->office_hours;
	}

	/**
	 * @param string $office_hours
	 */
	public function setOfficeHours($office_hours)
	{
		$this->office_hours = $office_hours;
	}

	/**
	 * @param Category $category
	 */
	public function addCategory(Category $category) 
	{
		$this->categories[] = $category;
	}
	
	/**
	 * @return array
	 */
	
	public function toArray()
	{
		$final = array();
	
		foreach ( $this as $key => $value )
		{
			if ( $key != 'categories' )
			{
				$final[$key] = $value;
			}
		}
	
		return $final;
	}
}