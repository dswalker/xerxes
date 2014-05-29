#DROP TABLE IF EXISTS xerxes_search_stats;
DROP TABLE IF EXISTS xerxes_reading_list;
DROP TABLE IF EXISTS xerxes_reading_list_users;

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

CREATE TABLE xerxes_reading_list_users (
	id 		VARCHAR(50),
	username	VARCHAR(50),		

	PRIMARY KEY (id),
	FOREIGN KEY (username) REFERENCES xerxes_users(username) ON DELETE CASCADE
);

#CREATE TABLE xerxes_search_stats (
#	ip_address	VARCHAR(20),
#	stamp		TIMESTAMP,
#	module		VARCHAR(20),
#	field		VARCHAR(20),
#	phrase		VARCHAR(1000),
#	hits		INTEGER
#);

#ALTER TABLE xerxes_records MODIFY original_id VARCHAR(255);