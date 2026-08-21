CREATE TABLE tx_jwrolesandcontacts_domain_model_person (
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted smallint(5) unsigned DEFAULT '0' NOT NULL,
	hidden smallint(5) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) DEFAULT '0' NOT NULL,

	name varchar(255) DEFAULT '' NOT NULL,
	image int(11) unsigned DEFAULT '0' NOT NULL,
	address text,
	email varchar(255) DEFAULT '' NOT NULL,
	phone varchar(50) DEFAULT '' NOT NULL,
	mobile varchar(50) DEFAULT '' NOT NULL,
	fax varchar(50) DEFAULT '' NOT NULL
);

CREATE TABLE tx_jwrolesandcontacts_domain_model_role (
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted smallint(5) unsigned DEFAULT '0' NOT NULL,
	hidden smallint(5) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) DEFAULT '0' NOT NULL,

	title varchar(255) DEFAULT '' NOT NULL,
	person int(11) unsigned DEFAULT '0' NOT NULL,
	address text,
	email varchar(255) DEFAULT '' NOT NULL,
	phone varchar(50) DEFAULT '' NOT NULL,
	mobile varchar(50) DEFAULT '' NOT NULL,
	fax varchar(50) DEFAULT '' NOT NULL
);
