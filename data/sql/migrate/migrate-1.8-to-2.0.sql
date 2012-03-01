DROP TABLE IF EXISTS xerxes_lti;

CREATE TABLE xerxes_lti (
	id 		MEDIUMINT NOT NULL AUTO_INCREMENT,
	context_id	VARCHAR(20),
	record_id	MEDIUMINT,
	record_order	INTEGER,

	PRIMARY KEY (id),
	FOREIGN KEY (record_id) REFERENCES xerxes_records(id) ON DELETE CASCADE
);