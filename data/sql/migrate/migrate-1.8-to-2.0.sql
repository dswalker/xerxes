DROP TABLE IF EXISTS xerxes_types;
DROP TABLE IF EXISTS xerxes_user_subcategory_databases;
DROP TABLE IF EXISTS xerxes_user_subcategories;
DROP TABLE IF EXISTS xerxes_user_categories;
DROP TABLE IF EXISTS xerxes_search_stats;
DROP TABLE IF EXISTS xerxes_reading_list;

CREATE TABLE xerxes_reading_list (
	id 		MEDIUMINT NOT NULL AUTO_INCREMENT,
	context_id	VARCHAR(20),
	record_id	MEDIUMINT,
	record_order	INTEGER,
	title		VARCHAR(1000),
	author		VARCHAR(500),
	publication	VARCHAR(1000),
	description	TEXT,

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

ALTER TABLE xerxes_records MODIFY original_id VARCHAR(255);