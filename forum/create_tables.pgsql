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

INSERT INTO phpgw_applications VALUES ('forum','Forum',1);


INSERT INTO f_body (cat_id, for_id, message) VALUES ('1', '1','Here is an example message.');
INSERT INTO f_body (cat_id, for_id, message) VALUES ('1', '1', 'Here is an example of a reply.');
INSERT INTO f_body (cat_id, for_id, message) VALUES ('1', '2', 'Yup, another example');
INSERT INTO f_body (cat_id, for_id, message) VALUES ('2', '3', 'I ran out of ideas ... so, heres another sample.');

INSERT INTO f_categories (name,descr) VALUES ('Just a sample', 'This is a sample category');
INSERT INTO f_categories (name,descr) VALUES ('Another sample category', 'Just another sample');

INSERT INTO f_forums (name,perm,groups,descr, cat_id) VALUES ('Sample', '0', '0', 'This is a sample', '1');
INSERT INTO f_forums (name,perm,groups,descr, cat_id) VALUES ('This is another sample', '0', '0', 'sub-category', '1');
INSERT INTO f_forums (name,perm,groups,descr, cat_id) VALUES ('Sample', '0', '0', 'Wow, what a suprise, another sample :)', '2');

INSERT INTO f_threads (postdate,main,parent,cat_id,for_id,author,subject,email,host,stat,thread,depth,pos,n_replies) VALUES ('2000-12-27 04:19:54', '1', '-1', '1', '1', 'Joseph Engo', 'Example', 'jengo@phpgroupware.org', '', '0', '1', '0', '0', '1');
INSERT INTO f_threads (postdate,main,parent,cat_id,for_id,author,subject,email,host,stat,thread,depth,pos,n_replies) VALUES ('2000-12-27 04:20:12', '2', '1', '1', '1', 'Joseph Engo', 'Re: Example', 'jengo@phpgroupware.org', '', '0', '1', '1', '1', '1');
INSERT INTO f_threads (postdate,main,parent,cat_id,for_id,author,subject,email,host,stat,thread,depth,pos,n_replies) VALUES ('2000-12-27 04:21:15', '3', '-1', '1', '2', 'Joseph Engo', 'Example message', 'jengo@phpgroupware.org', '', '0', '3', '0', '0', '0');
INSERT INTO f_threads (postdate,main,parent,cat_id,for_id,author,subject,email,host,stat,thread,depth,pos,n_replies) VALUES ('2000-12-27 04:21:58', '4', '-1', '2', '3', 'Joseph Engo', '', 'jengo@phpgroupware.org', '', '0', '4', '0', '0', '0');

