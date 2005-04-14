CREATE TABLE tblACLs (
  id int(11) NOT NULL auto_increment,
  target int(11) NOT NULL default '0',
  targetType tinyint(4) NOT NULL default '0',
  userID int(11) NOT NULL default '-1',
  groupID int(11) NOT NULL default '-1',
  mode tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

CREATE TABLE tblDocumentContent (
  id int(11) NOT NULL auto_increment,
  document int(11) default NULL,
  version tinyint(4) default NULL,
  comment text,
  date int(12) default NULL,
  createdBy int(11) default NULL,
  dir varchar(10) NOT NULL default '',
  orgFileName varchar(150) NOT NULL default '',
  fileType varchar(10) NOT NULL default '',
  mimeType varchar(70) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

CREATE TABLE tblDocuments (
  id int(11) NOT NULL auto_increment,
  name varchar(150) default NULL,
  comment text,
  date int(12) default NULL,
  expires int(12) default NULL,
  owner int(11) default NULL,
  folder int(11) default NULL,
  inheritAccess tinyint(1) NOT NULL default '1',
  defaultAccess tinyint(4) NOT NULL default '0',
  locked int(11) NOT NULL default '-1',
  keywords text NOT NULL,
  sequence double NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

CREATE TABLE tblFolders (
  id int(11) NOT NULL auto_increment,
  name varchar(70) default NULL,
  parent int(11) default NULL,
  comment text,
  owner int(11) default NULL,
  inheritAccess tinyint(1) NOT NULL default '1',
  defaultAccess tinyint(4) NOT NULL default '0',
  sequence double NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

CREATE TABLE tblGroupMembers (
  id int(11) NOT NULL auto_increment,
  groupID int(11) NOT NULL default '0',
  userID int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

CREATE TABLE tblGroups (
  id int(11) NOT NULL auto_increment,
  name varchar(50) default NULL,
  comment text NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

CREATE TABLE tblNotify (
  id int(11) NOT NULL auto_increment,
  target int(11) NOT NULL default '0',
  targetType int(11) NOT NULL default '0',
  userID int(11) NOT NULL default '-1',
  groupID int(11) NOT NULL default '-1',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

CREATE TABLE tblDocumentLinks (
  id int(11) NOT NULL auto_increment,
  document int(11) NOT NULL default '0',
  target int(11) NOT NULL default '0',
  userID int(11) NOT NULL default '0',
  public tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

CREATE TABLE tblSessions (
  id char(50) NOT NULL default '',
  userID int(11) NOT NULL default '0',
  lastAccess int(11) NOT NULL default '0',
  theme varchar(30) NOT NULL default '',
  language varchar(30) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

CREATE TABLE tblUserImages (
  id int(11) NOT NULL auto_increment,
  userID int(11) NOT NULL default '0',
  image blob NOT NULL,
  mimeType varchar(10) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

CREATE TABLE tblUsers (
  id int(11) NOT NULL auto_increment,
  login varchar(50) default NULL,
  pwd varchar(50) default NULL,
  fullName varchar(100) default NULL,
  email varchar(70) default NULL,
  comment text NOT NULL,
  isAdmin smallint(1) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

CREATE TABLE `tblKeywordCategories` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `owner` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=14 ;

CREATE TABLE `tblKeywords` (
  `id` int(11) NOT NULL auto_increment,
  `category` int(11) NOT NULL default '0',
  `keywords` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=12 ;



INSERT INTO tblFolders VALUES (1, 'Root-Folder', 0, 'no comment', 1, 0, 2, 0);
INSERT INTO tblUsers VALUES (1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'Administrator', 'address@server.com', 'no comment', 1);
INSERT INTO tblUsers VALUES (2, 'guest', NULL, 'Guest User', NULL, '', 0);
