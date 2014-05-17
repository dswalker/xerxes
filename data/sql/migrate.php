<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */ 

use Application\Model\Knowledgebase\Knowledgebase;
use Application\Model\Knowledgebase\Category;
use Application\Model\Knowledgebase\Subcategory;
use Application\Model\Knowledgebase\Database;
use Application\Model\Knowledgebase\DatabaseSequence;
use Application\Model\Knowledgebase\Librarian;
use Application\Model\Knowledgebase\LibrarianSequence;
use Xerxes\Utility\User;


$instance = $argv[1];


// reference path to root, two directories up

$root = dirname(dirname(__DIR__));

// working directory is the instance

chdir("$root/home/$instance");

// composer autoloading

$autoloader = include_once("$root/vendor/autoload.php");

if ( ! $autoloader ) 
{
	throw new \Exception("$root/vendor/autoload.php could not be found. Did you run `php composer.phar install`?");
}

$user = new User();
$user->username = 'admin';

$knowledgebase = new Knowledgebase($user);

// run the application


$owner = 'admin';

// databases

$sql = 'SELECT * FROM xerxes_databases';
$results = $knowledgebase->datamap()->select($sql);

$x = 0;

foreach ( $results as $result )
{
	$xml = simplexml_load_string($result['data']);

	$title = trim((string) $xml->title_display);

	if ( $title == "" )
	{
		continue;
	}

	$metalib_id = (string) $xml->metalib_id;

	$active = (int) $xml->active;
	$proxy = (int) $xml->proxy;
	$subscription = (int) $xml->subscription;

	$creator = (string) $xml->creator;
	$publisher = (string) $xml->publisher;
	$description = (string) $xml->description;
	$link = (string) $xml->link_native_home;
	$time_span = (string) $xml->time_span;
	$link_guide = (string) $xml->link_guide;

	$email = (string) $xml->library_email;
	$phone = (string) $xml->library_telephone;
	$office = (string) $xml->library_address;
	$image = (string) $xml->library_contact;
	$office_hours = (string) $xml->library_hours;

	$type = (string) $xml->type;

	if ( $type == 'Librarian')
	{
		$librarian = new Librarian();
		$librarian->setSourceId($metalib_id);
		$librarian->setImage($image);
		$librarian->setEmail($email);
		$librarian->setLink($link);
		$librarian->setName($title);
		$librarian->setPhone($phone);
		$librarian->setOffice($office);
		$librarian->setOfficeHours($office_hours);

		$knowledgebase->entityManager()->persist($librarian);
	}
	else
	{
		$database = new Database();

		$database->setOwner($owner);
		$database->setTitle($title);
		$database->setSourceId($metalib_id);
		$database->setCreator($creator);
		$database->setPublisher($publisher);
		$database->setDescription($description);
		$database->setLink($link);
		$database->setCoverage($time_span);
		$database->setLinkGuide($link_guide);
		$database->setType($type);

		foreach ( $xml->title_alternate as $title_alternate )
		{
			$database->addAlternateTitle((string) $title_alternate);
		}

		$notes = "";

		foreach ( $xml->note_cataloger as $note )
		{
			$notes = " " . (string) $note;
		}

		foreach ( $xml->note as $note )
		{
			$notes = " " . (string) $note;
		}

		$notes = trim($notes);

		$database->setNotes($notes);

		foreach ( $xml->keyword as $keyword )
		{
			$keyword_array = explode(',', (string) $keyword);
				
			foreach ( $keyword_array as $keyword_term )
			{
				$keyword_term = trim($keyword_term);

				$database->addKeyword($keyword_term);
			}
		}

		// databases marked as subscription should be proxied

		$should_proxy =  false;

		if ( $subscription == 1 )
		{
			$should_proxy = true;
		}

		// override the behavior if proxy flag specifically set

		if ( $proxy != null )
		{
			if ( $proxy == 1 )
			{
				$should_proxy = true;
			}
			elseif ( $proxy == 0 )
			{
				$should_proxy = false;
			}
		}

		$database->setProxy($should_proxy);

		$knowledgebase->entityManager()->persist($database);
	}

	$x++;
}

$knowledgebase->entityManager()->flush();

// index 'em

foreach ( $knowledgebase->getDatabases() as $database )
{
	$knowledgebase->indexDatabase($database);
}

$knowledgebase->entityManager(true);

// url

$url = "http://library.calstate.edu/$instance/databases/?format=xerxes";
$xml = simplexml_load_file($url);

foreach ( $xml->categories->category as $category_xml )
{
	$name = (string) $category_xml->name;
	$path = (string) $category_xml->url;

	$category = new Category();
	$category->setName($name);
	$category->setOwner($owner);

	$url = 'http://library.calstate.edu' . $path . '?format=xerxes';

	$subject_xml = simplexml_load_file($url);

	// subcategories

	$nodes = $subject_xml->xpath('//category|//sidebar');

	foreach ( $nodes as $node )
	{
		$sidebar = false;

		if ( $node->getName() == 'sidebar')
		{
			$sidebar = true;
		}

		foreach ( $node->subcategory as $subcategory_xml )
		{
			if ( (string) $subcategory_xml->database->type == 'Librarian')
			{
				foreach ( $subcategory_xml->database->metalib_id as $metalib )
				{
					$metalib_id = (string) $metalib;
						
					$librarian = $knowledgebase->getLibrarianBySourceId($metalib_id);
						
					$librarian_sequence = new LibrarianSequence();
					$librarian_sequence->setLibrarian($librarian);
						
					$category->addLibrarianSequence($librarian_sequence);
				}

				continue;
			}
				
				
			$name = (string) $subcategory_xml['name'];
			$sequence = (string) $subcategory_xml['position'];
			$metalib_sucategory_id = (string) $subcategory_xml['id'];
				
			$subcategory = new Subcategory();
			$subcategory->setSourceId($metalib_sucategory_id);
			$subcategory->setName($name);
			$subcategory->setSidebar($sidebar);
			$subcategory->setSequence($sequence);
				
			$knowledgebase->entityManager()->persist($subcategory);
				
			// databases
				
			foreach ( $subcategory_xml->database as $database )
			{
				$metalib_id = (string) $database->metalib_id;

				$database = $knowledgebase->getDatabaseBySourceId($metalib_id);

				if ( $database != null )
				{
					$sequence = new DatabaseSequence();
					$sequence->setDatabase($database);
					$subcategory->addDatabaseSequence($sequence);
				}
			}
				
			$category->addSubcategory($subcategory);
		}
	}

	$knowledgebase->entityManager()->persist($category);
}

$knowledgebase->entityManager()->flush();