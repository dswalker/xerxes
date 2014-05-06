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
use Xerxes\Utility\Parser;

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
	 * @OneToMany(targetEntity="LibrarianSequence", mappedBy="librarian")
	 * @var LibrarianSequence[]
	 */
	protected $librarian_sequence;	
	
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
	public function getImageUrl()
	{
		return $this->image;
	}
	
	/**
	 * @return resource  image
	 */
	
	public function getImage()
	{
		$url = $this->image;
		
		if ( ! function_exists("gd_info") )
		{
			return null;
		}
		
		if ( $url == "" )
		{
			return $this->createblank();
		}
		
		$size = $this->config()->getConfig("LIBRARIAN_IMAGE_SIZE", false, 150);
		$domains = $this->config()->getConfig("LIBRARIAN_IMAGE_DOMAINS", false);
		
		// images can only come from these domains, for added security
		
		if ( $domains != null )
		{
			$bolPassed = Parser::withinDomain($url,$domains);
		
			if ( $bolPassed == false )
			{
				throw new \Exception("librarian image not allowed from that domain");
			}
		}
		
		$image_string = file_get_contents($url);
		
		if ( $image_string == "")
		{
			return $this->createblank();
		}
		else
		{
			// convert to a thumbnail
		
			$original = imagecreatefromstring($image_string);
				
			if ( $original == false )
			{
				return $this->createblank();
			}
					
			$old_x = imagesx($original);
			$old_y = imagesy($original);
		
			if ($old_x > $old_y)
			{
				$thumb_w = $size;
				$thumb_h = $old_y*($size/$old_x);
			}
			if ($old_x < $old_y)
			{
				$thumb_w = $old_x*($size/$old_y);
				$thumb_h = $size;
			}
			if ($old_x == $old_y)
			{
				$thumb_w = $size;
				$thumb_h = $size;
			}
				
			$thumb = imagecreatetruecolor($thumb_w,$thumb_h);
				
			imagecopyresampled($thumb,$original,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y);
	
			imagedestroy($original);
			
			return $thumb;
		}
	}
	
	protected function createblank()
	{
		return imagecreatetruecolor(1,1);
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
	 * @param LibrarianSequence $sequence
	 */
	public function addLibrarianSequence(LibrarianSequence $sequence) 
	{
		$this->librarian_sequence[] = $sequence;
	}
	
	/**
	 * @return Config
	 */
	protected function config()
	{
		return Config::getInstance();
	}
	
	/**
	 * @return array
	 */
	
	public function toArray()
	{
		$final = array();
	
		foreach ( $this as $key => $value )
		{
			if ( $key != 'librarian_sequence' )
			{
				$final[$key] = $value;
			}
		}
	
		return $final;
	}
}