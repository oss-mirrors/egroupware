CREATE TABLE f_body (
  id		serial,
  cat_id	int DEFAULT '0',
  for_id	int DEFAULT '0',
  message text
);

CREATE TABLE f_categories (
  id 	serial,
  name 	varchar(50) DEFAULT '' NOT NULL,
  descr 	varchar(255) DEFAULT '' NOT NULL
);

CREATE TABLE f_forums (
  id 	serial,
  name 	varchar(50) DEFAULT '',
  perm 	int DEFAULT '0',
  groups 	varchar(50) DEFAULT '0',
  descr	varchar(255) DEFAULT '',
  cat_id 	int DEFAULT '0'
);

CREATE TABLE f_threads (
  id        serial,
  postdate  varchar(255),
  main      int DEFAULT '0' NOT NULL,
  parent    int DEFAULT '0' NOT NULL,
  cat_id    int DEFAULT '0' NOT NULL,
  for_id    int DEFAULT '0' NOT NULL,
  author    varchar(50) DEFAULT '' NOT NULL,
  subject   varchar(50) DEFAULT '' NOT NULL,
  email     varchar(50) DEFAULT '' NOT NULL,
  host      varchar(18) DEFAULT '' NOT NULL,
  stat      int DEFAULT '0' NOT NULL,
  thread    int DEFAULT '0' NOT NULL,
  depth     int DEFAULT '0' NOT NULL,
  pos       int DEFAULT '0' NOT NULL,
  n_replies int DEFAULT '0' NOT NULL
);

INSERT INTO applications VALUES ('forum','Forum',1);
