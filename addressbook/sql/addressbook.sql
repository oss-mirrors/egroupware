# Host: localhost Database : phpgroupware
# --------------------------------------------------------

#
# Table structure for table 'phpgw_addressbook'
#

CREATE TABLE phpgw_addressbook (
   id int(8) DEFAULT '0' NOT NULL,
   lid varchar(32),
   tid char(1),
   owner int(8),
   fn varchar(64),
   sound varchar(64),
   org_name varchar(64),
   org_unit varchar(64),
   title varchar(64),
   n_family varchar(64),
   n_given varchar(64),
   n_middle varchar(64),
   n_prefix varchar(64),
   n_suffix varchar(64),
   label text,
   adr_poaddr varchar(64),
   adr_extaddr varchar(64),
   adr_street varchar(64),
   adr_locality varchar(32),
   adr_region varchar(32),
   adr_postalcode varchar(32),
   adr_countryname varchar(32),
   adr_work enum('y','n') DEFAULT 'n' NOT NULL,
   adr_home enum('y','n') DEFAULT 'n' NOT NULL,
   adr_parcel enum('y','n') DEFAULT 'n' NOT NULL,
   adr_postal enum('y','n') DEFAULT 'n' NOT NULL,
   tz varchar(8),
   geo varchar(32),
   a_tel varchar(40) DEFAULT '+1 (000) 000-0000' NOT NULL,
   a_tel_work enum('y','n') DEFAULT 'n' NOT NULL,
   a_tel_home enum('y','n') DEFAULT 'n' NOT NULL,
   a_tel_voice enum('y','n') DEFAULT 'n' NOT NULL,
   a_tel_msg enum('y','n') DEFAULT 'n' NOT NULL,
   a_tel_fax enum('y','n') DEFAULT 'n' NOT NULL,
   a_tel_prefer enum('y','n') DEFAULT 'n' NOT NULL,
   b_tel varchar(40) DEFAULT '+1 (000) 000-0000' NOT NULL,
   b_tel_work enum('y','n') DEFAULT 'n' NOT NULL,
   b_tel_home enum('y','n') DEFAULT 'n' NOT NULL,
   b_tel_voice enum('y','n') DEFAULT 'n' NOT NULL,
   b_tel_msg enum('y','n') DEFAULT 'n' NOT NULL,
   b_tel_fax enum('y','n') DEFAULT 'n' NOT NULL,
   b_tel_prefer enum('y','n') DEFAULT 'n' NOT NULL,
   c_tel varchar(40) DEFAULT '+1 (000) 000-0000' NOT NULL,
   c_tel_work enum('y','n') DEFAULT 'n' NOT NULL,
   c_tel_home enum('y','n') DEFAULT 'n' NOT NULL,
   c_tel_voice enum('y','n') DEFAULT 'n' NOT NULL,
   c_tel_msg enum('y','n') DEFAULT 'n' NOT NULL,
   c_tel_fax enum('y','n') DEFAULT 'n' NOT NULL,
   c_tel_prefer enum('y','n') DEFAULT 'n' NOT NULL,
   d_emailtype enum('INTERNET','CompuServe','AOL','Prodigy','eWorld','AppleLink','AppleTalk','PowerShare','IBMMail','ATTMail','MCIMail','X.400','TLX') DEFAULT 'INTERNET' NOT NULL,
   d_email varchar(64),
   d_email_work enum('y','n') DEFAULT 'n' NOT NULL,
   d_email_home enum('y','n') DEFAULT 'n' NOT NULL,
   PRIMARY KEY (id),
   UNIQUE id (id)
);

CREATE TABLE phpgw_addressbook_extra (
   contact_id int(11),
   contact_owner int(11),
   contact_name varchar(255),
   contact_value varchar(255)
);
