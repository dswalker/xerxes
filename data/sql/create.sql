# author: David Walker
# copyright: 2009 California State University
# version: $Id: create-kb.sql 1612 2011-01-11 17:22:13Z dwalker@calstate.edu $
# package: Xerxes
# link: http://xerxes.calstate.edu
# license: http://www.gnu.org/licenses/

CREATE DATABASE IF NOT EXISTS xerxes DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE xerxes;

SET storage_engine = INNODB;

DROP TABLE IF EXISTS xerxes_databases_search;
DROP TABLE IF EXISTS xerxes_subcategory_databases;
DROP TABLE IF EXISTS xerxes_subcategories;
DROP TABLE IF EXISTS xerxes_databases;
DROP TABLE IF EXISTS xerxes_categories;
DROP TABLE IF EXISTS xerxes_types;
DROP TABLE IF EXISTS xerxes_reading_list;
DROP TABLE IF EXISTS xerxes_search_stats;
DROP TABLE IF EXISTS xerxes_user_usergroups;
DROP TABLE IF EXISTS xerxes_cache;
DROP TABLE IF EXISTS xerxes_tags;
DROP TABLE IF EXISTS xerxes_sfx;
DROP TABLE IF EXISTS xerxes_refereed;
DROP TABLE IF EXISTS xerxes_users;
DROP TABLE IF EXISTS xerxes_records;
DROP TABLE IF EXISTS xerxes_user_subcategory_databases;
DROP TABLE IF EXISTS xerxes_user_subcategories;
DROP TABLE IF EXISTS xerxes_user_categories;

CREATE TABLE xerxes_databases(
	database_id    		VARCHAR(10),
	title_display		VARCHAR(100),
	type                    VARCHAR(50),
	data			MEDIUMTEXT,
	PRIMARY KEY (database_id)
);

CREATE TABLE xerxes_databases_search (
	database_id     	VARCHAR(10),
	field			VARCHAR(50),
	term			VARCHAR(50),

	FOREIGN KEY (database_id) REFERENCES xerxes_databases(database_id)	 ON DELETE CASCADE
);

CREATE INDEX xerxes_databases_search_field_idx ON xerxes_databases_search(field);
CREATE INDEX xerxes_databases_search_term_idx ON xerxes_databases_search(term);

CREATE TABLE xerxes_categories(
	id 			MEDIUMINT NOT NULL AUTO_INCREMENT,
	name     		VARCHAR(255),
	old			VARCHAR(255),
	normalized		VARCHAR(255),
	lang			VARCHAR(5),

	PRIMARY KEY (id)
);

CREATE TABLE xerxes_subcategories(
	metalib_id	VARCHAR(20),
	name     	VARCHAR(255),
	sequence	MEDIUMINT NOT NULL,
  	category_id	MEDIUMINT NOT NULL,

	PRIMARY KEY (metalib_id),
 	FOREIGN KEY (category_id) REFERENCES xerxes_categories(id) ON DELETE CASCADE
);

CREATE TABLE xerxes_subcategory_databases(

	database_id	VARCHAR(10),
  	subcategory_id	VARCHAR(20),
    	sequence 	MEDIUMINT,

 	FOREIGN KEY (database_id) REFERENCES xerxes_databases(database_id)	 ON DELETE CASCADE,
	FOREIGN KEY (subcategory_id) REFERENCES xerxes_subcategories(metalib_id) ON DELETE CASCADE
);

CREATE TABLE xerxes_types(
	id 			MEDIUMINT NOT NULL AUTO_INCREMENT,
	name     		VARCHAR(255),
	normalized		VARCHAR(255),

	PRIMARY KEY (id)
);

CREATE TABLE xerxes_users (
	username 	VARCHAR(50),
	last_login	DATE,
	suspended	INTEGER(1),
	first_name	VARCHAR(50),
	last_name	VARCHAR(50),
	email_addr	VARCHAR(120),

	PRIMARY KEY (username)
);

CREATE INDEX xerxes_users_username_idx ON xerxes_users(username);

CREATE TABLE xerxes_user_usergroups (
	username	VARCHAR(50),
	usergroup	VARCHAR(50),

	PRIMARY KEY (username, usergroup),
	FOREIGN KEY (username) REFERENCES xerxes_users(username) ON DELETE CASCADE
);

CREATE TABLE xerxes_sfx (
	issn 		VARCHAR(8),
	title		VARCHAR(100),
	startdate	INTEGER(4),
	enddate		INTEGER(4),
	embargo		INTEGER(5),
	updated		DATE,
	live		INTEGER(1)
);

CREATE INDEX xerxes_sfx_issn_idx ON xerxes_sfx(issn);

CREATE TABLE xerxes_refereed (
	issn		VARCHAR(8),
	title		VARCHAR(1000),
	timestamp	VARCHAR(8)
);

CREATE INDEX xerxes_refereed_issn_idx ON xerxes_refereed(issn);

CREATE TABLE xerxes_records (
	id 		MEDIUMINT NOT NULL AUTO_INCREMENT,
	source 		VARCHAR(10),
	original_id 	VARCHAR(100),
	timestamp 	DATE,
	username 	VARCHAR(50),
	nonsort 	VARCHAR(5),
	title 		VARCHAR(255),
	author 		VARCHAR (150),
	year		SMALLINT(4),
	format 		VARCHAR(50),
	refereed 	SMALLINT(1),
	record_type	VARCHAR(100),
	marc		MEDIUMTEXT,

	PRIMARY KEY (id)
);

CREATE INDEX xerxes_records_username_idx ON xerxes_records(username);
CREATE INDEX xerxes_records_original_id_idx ON xerxes_records(original_id);

CREATE TABLE xerxes_tags (
	username	VARCHAR(50),
	record_id	MEDIUMINT,
	tag 		VARCHAR(100),

 	FOREIGN KEY (username) REFERENCES xerxes_users(username) ON DELETE CASCADE,
	FOREIGN KEY (record_id) REFERENCES xerxes_records(id) ON DELETE CASCADE
);

CREATE TABLE xerxes_cache (
	source		VARCHAR(20),
	id 		VARCHAR(80),
	data		MEDIUMTEXT,
	timestamp	INTEGER,
	expiry		INTEGER,

	PRIMARY KEY (source,id)
);

CREATE TABLE xerxes_user_categories(
	id 		MEDIUMINT NOT NULL AUTO_INCREMENT,
	name		VARCHAR(255),
	username	VARCHAR(50),
	published	INTEGER(1) NOT NULL DEFAULT 0, 
	normalized	VARCHAR(255),
	
	PRIMARY KEY (id)
);

CREATE INDEX xerxes_user_categories_normalized_idx ON xerxes_user_categories(username, normalized);

CREATE TABLE xerxes_user_subcategories(
	id		MEDIUMINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name		VARCHAR(255),
	sequence	MEDIUMINT NOT NULL,
	category_id	MEDIUMINT NOT NULL,

	FOREIGN KEY (category_id) REFERENCES xerxes_user_categories(id) ON DELETE CASCADE
);

CREATE TABLE xerxes_user_subcategory_databases(

	database_id	VARCHAR(10),
	subcategory_id	MEDIUMINT,
	sequence 	MEDIUMINT,

	PRIMARY KEY(database_id, subcategory_id),
	FOREIGN KEY (subcategory_id) REFERENCES xerxes_user_subcategories (id) ON DELETE CASCADE
);

CREATE TABLE xerxes_reading_list (
	id 		MEDIUMINT NOT NULL AUTO_INCREMENT,
	context_id	VARCHAR(20),
	record_id	MEDIUMINT,
	record_order	INTEGER,

	PRIMARY KEY (id),
	FOREIGN KEY (record_id) REFERENCES xerxes_records(id) ON DELETE CASCADE
);

CREATE TABLE xerxes_search_stats (
	ip_address	VARCHAR(20),
	stamp		TIMESTAMP,
	module		VARCHAR(20),
	field		VARCHAR(20),
	phrase		VARCHAR(1000),
	hits		INTEGER
);