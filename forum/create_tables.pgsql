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
  id 	serial,
  postdate varchar(255),
  main 	int DEFAULT '0',
  parent 	int DEFAULT '0',
  cat_id 	int DEFAULT '0',
  for_id 	int DEFAULT '0',
  author 	varchar(50) DEFAULT '',
  subject varchar(50) DEFAULT '',
  email 	varchar(50) DEFAULT '',
  host 	varchar(18) DEFAULT '',
  stat 	int DEFAULT '0',
  thread 	int DEFAULT '0',
  depth 	int DEFAULT '0',
  pos 	int DEFAULT '0'
);

INSERT INTO applications VALUES ('forum','Forum',1);
