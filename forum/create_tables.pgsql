CREATE TABLE phpgw_forum_body (
  id		serial,
  cat_id	int DEFAULT '0',
  for_id	int DEFAULT '0',
  message text
);

CREATE TABLE phpgw_forum_categories (
  id 	serial,
  name 	varchar(50) DEFAULT '' NOT NULL,
  descr 	varchar(255) DEFAULT '' NOT NULL
);

CREATE TABLE phpgw_forum_forums (
  id 	serial,
  name 	varchar(50) DEFAULT '',
  perm 	int DEFAULT '0',
  groups 	varchar(50) DEFAULT '0',
  descr	varchar(255) DEFAULT '',
  cat_id 	int DEFAULT '0'
);

CREATE TABLE phpgw_forum_threads (
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


#
# Dumping data for table 'phpgw_forum_body'
#

INSERT INTO phpgw_forum_body VALUES ( '1', '1', '1', 'Here is an example message.');
INSERT INTO phpgw_forum_body VALUES ( '2', '1', '1', 'Here is an example of a reply.');
INSERT INTO phpgw_forum_body VALUES ( '3', '1', '2', 'Yup, another example');
INSERT INTO phpgw_forum_body VALUES ( '4', '2', '3', 'I ran out of ideas ... so, heres
another sample.');
INSERT INTO phpgw_forum_body VALUES ( '5', '1', '1', 'emm');
INSERT INTO phpgw_forum_body VALUES ( '6', '2', '3', 'emm');
INSERT INTO phpgw_forum_body VALUES ( '7', '2', '3', 'test');


#
# Dumping data for table 'phpgw_forum_categories'
#

INSERT INTO phpgw_forum_categories VALUES ( '1', 'Just a sample', 'This is a sample
category');
INSERT INTO phpgw_forum_categories VALUES ( '2', 'Another sample category', 'Just another
sample');
INSERT INTO phpgw_forum_categories VALUES ( '4', 'test', 'test');


#
# Dumping data for table 'phpgw_forum_forums'
#

INSERT INTO phpgw_forum_forums VALUES ( '1', 'Sample', '0', '0', 'This is a sample', '1');
INSERT INTO phpgw_forum_forums VALUES ( '2', 'This is another sample', '0', '0',
'sub-category', '1');
INSERT INTO phpgw_forum_forums VALUES ( '3', 'Sample', '0', '0', 'Wow, what a suprise,
another sample :)', '2');
INSERT INTO phpgw_forum_forums VALUES ( '4', 'a ha', '0', '0', 'emm', '4');

#
# Dumping data for table 'phpgw_forum_threads'
#

INSERT INTO phpgw_forum_threads VALUES ( '1', '2000-12-27 04:19:54', '1', '-1', '1', '1',
'Joseph Engo', 'Example', 'jengo@phpgroupware.org', '', '0', '1', '0', '0', '1');
INSERT INTO phpgw_forum_threads VALUES ( '2', '2000-12-27 04:20:12', '2', '1', '1', '1',
'Joseph Engo', 'Re: Example', 'jengo@phpgroupware.org', '', '0', '1', '1', '1', '1');
INSERT INTO phpgw_forum_threads VALUES ( '3', '2000-12-27 04:21:15', '3', '-1', '1', '2',
'Joseph Engo', 'Example message', 'jengo@phpgroupware.org', '', '0', '3', '0', '0', '0');
INSERT INTO phpgw_forum_threads VALUES ( '4', '2000-12-27 04:21:58', '4', '-1', '2', '3',
'Joseph Engo', '', 'jengo@phpgroupware.org', '', '0', '4', '0', '0', '0');
INSERT INTO phpgw_forum_threads VALUES ( '5', '2001-07-03 09:37:41', '5', '-1', '1', '1',
'Kaede Rokawa', 'looks nice with the new icons', 'Just to be nice', '192.168.0.88', '0',
'5', '0', '0', '0');
INSERT INTO phpgw_forum_threads VALUES ( '6', '2001-07-03 09:38:57', '6', '-1', '2', '3',
'Kaede Rokawa', 'nice amran', 'hehe', '192.168.0.88', '0', '6', '0', '0', '0');
INSERT INTO phpgw_forum_threads VALUES ( '7', '2001-07-03 09:49:24', '7', '-1', '2', '3',
'Kaede Rokawa', 'test', 'test', '192.168.0.88', '0', '7', '0', '0', '0');



INSERT INTO phpgw_applications VALUES ('forum','Forum',1);
