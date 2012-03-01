DROP TABLE IF EXISTS xerxes_lti;
DROP TABLE IF EXISTS xerxes_search_stats;

CREATE TABLE xerxes_lti (
	id 		MEDIUMINT NOT NULL AUTO_INCREMENT,
	context_id	VARCHAR(20),
	record_id	MEDIUMINT,
	record_order	INTEGER,

	PRIMARY KEY (id),
	FOREIGN KEY (record_id) REFERENCES xerxes_records(id) ON DELETE CASCADE
);

CREATE TABLE xerxes_search_stats (
	ip_address	VARCHAR(20),
	timestamp	TIMESTAMP,
	module		VARCHAR(20),
	field		VARCHAR(20),
	phrase		VARCHAR(1000),
	hits		INTEGER,
);