//the tables_baseline.inc.php

$phpgw_baseline = array(
	'f_body' => array(
        	'fd' => array(
            		'id' => array('type' => 'auto','nullable' => false),
            		'cat_id' => array('type' => 'int','nullable' => false, 'precision' => 25, 'default' => 0),
            		'for_id' => array('type' => 'int','nullable' => false, 'precision' => 25, 'default' => 0),
            		'message' => array('type' => 'blob')
        	),
        	'pk' => array('id'),
        	'fk' => array(),
        	'ix' => array(),
        	'uc' => array()
    		) 
	'f_categories' => array(
        	'fd' => array(
            		'id' => array('type' => 'auto','nullable' => false),
            		'name' => array('type' => 'varchar','nullable' => false, 'precision' => 50),
            		'desc' => array('type' => 'varchar','nullable' => false, 'precision' => 255)
            	    	),
        	'pk' => array('id'),
        	'fk' => array(),
        	'ix' => array(),
        	'uc' => array()
		)
	'f_forums' => array(
        	'fd' => array(
            		'id' => array('type' => 'auto','nullable' => false),
            		'name' => array('type' => 'varchar','nullable' => false, 'precision' => 50),
            		'perm' => array('type' => 'tinyint','nullable' => false, 'precision' => 1, 'default' => 0),
            	    	'groups' => array('type' => 'varchar','nullable' => false, 'precision' => 50, 'default' => 0),
            	    	'desc' => array('type' => 'varchar','nullable' => false, 'precision' => 255),
            	    	'cat_id' => array('type' => 'int','nullable' => false, 'precision' => 11, 'default' => 0)
            	    	),
        	'pk' => array('id'),
        	'fk' => array(),
        	'ix' => array(),
        	'uc' => array()
		)
		

		
	'f_threads' => array(
        	'fd' => array(
            		'id' => array('type' => 'auto','nullable' => false),
            		'postdate' => array('type' => 'datetime','nullable' => false, 'default' => '0000-00-00 00:00:00'),
            		'main' => array('type' => 'int','nullable' => false, 'precision' => 11, 'default' => 0),
            	    	'parent' => array('type' => 'int','nullable' => false, 'precision' => 11, 'default' => 0),
            	    	'cat_id' => array('type' => 'int','nullable' => false, 'precision' => 11, 'default' => 0),
            	    	'for_id' => array('type' => 'int','nullable' => false, 'precision' => 11, 'default' => 0),
            	    	'author' => array('type' => 'varchar','nullable' => false, 'precision' => 50, 'default' => 0),
            	    	'subject' => array('type' => 'varchar','nullable' => false, 'precision' => 50),
            	    	'email' => array('type' => 'varchar','nullable' => false, 'precision' => 11),
            	    	'host' => array('type' => 'varchar','nullable' => false, 'precision' => 18),
            	    	'stat' => array('type' => 'tinyint','nullable' => false, 'precision' => 255,'default' => 0),
            	    	'thread' => array('type' => 'int','nullable' => false, 'precision' => 11, 'default' => 0),
            	    	'depth' => array('type' => 'int','nullable' => false, 'precision' => 11, 'default' => 0),
            	    	'pos' => array('type' => 'int','nullable' => false, 'precision' => 11, 'default' => 0),
            	    	'n_replies' => array('type' => 'int','nullable' => false, 'precision' => 11, 'default' => 0)
            	    	),

        	'pk' => array('id'),
        	'fk' => array(),
        	'ix' => array(),
        	'uc' => array()
		)
);
    
/*
CREATE TABLE f_threads (
  id int(11) DEFAULT '0' NOT NULL auto_increment,
  postdate datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  main int(11) DEFAULT '0' NOT NULL,
  parent int(11) DEFAULT '0' NOT NULL,
  cat_id int(11) DEFAULT '0' NOT NULL,
  for_id int(11) DEFAULT '0' NOT NULL,
  author varchar(50) DEFAULT '' NOT NULL,
  subject varchar(50) DEFAULT '' NOT NULL,
  email varchar(50) DEFAULT '' NOT NULL,
  host varchar(18) DEFAULT '' NOT NULL,
  stat tinyint(1) DEFAULT '0' NOT NULL,
  thread int(11) DEFAULT '0' NOT NULL,
  depth int(11) DEFAULT '0' NOT NULL,
  pos int(11) DEFAULT '0' NOT NULL,
  n_replies int(11) DEFAULT '0' NOT NULL,
  PRIMARY KEY (id),
  KEY date (postdate)
);
CREATE TABLE f_forums (
  id int(11) DEFAULT '0' NOT NULL auto_increment,
  name varchar(50) DEFAULT '' NOT NULL,
  perm tinyint(1) DEFAULT '0' NOT NULL,
  groups varchar(50) DEFAULT '0' NOT NULL,
  descr varchar(255) DEFAULT '' NOT NULL,
  cat_id int(11) DEFAULT '0' NOT NULL,
  PRIMARY KEY (id)
);
CREATE TABLE f_categories (
  id int(11) DEFAULT '0' NOT NULL auto_increment,
  name varchar(50) DEFAULT '' NOT NULL,
  descr varchar(255) DEFAULT '' NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE f_body (
  id int(11) DEFAULT '0' NOT NULL auto_increment,
  cat_id int(11) DEFAULT '0' NOT NULL,
  for_id int(11) DEFAULT '0' NOT NULL,
  message blob NOT NULL,
  PRIMARY KEY (id)
);

*/

$phpgw_setup->oProc->CreateTable(
    'categories', array(
        'fd' => array(
            'cat_id' => array('type' => 'auto','nullable' => false),
            'account_id' => array('type' => 'int','precision' => 4,'nullable' => false, 'default' => 0),
            'app_name' => array('type' => 'varchar','precision' => 25,'nullable' => false),
            'cat_name' => array('type' => 'varchar', 'precision' => 150, 'nullable' => false),
            'cat_description' => array('type' => 'text', 'nullable' => false)
        ),
        'pk' => array('cat_id'),
        'ix' => array(),
        'fk' => array(),
        'uc' => array()
    )
);