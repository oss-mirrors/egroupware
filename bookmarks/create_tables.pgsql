CREATE TABLE bookmarks (
  id             serial,
  url            varchar(255) DEFAULT '' NOT NULL,
  name           varchar(255) DEFAULT '' NOT NULL,
  ldesc          varchar(255) DEFAULT '' NOT NULL,
  keywords       varchar(255),
  category_id    int DEFAULT '0' NOT NULL,
  subcategory_id int DEFAULT '0' NOT NULL,
  rating_id      int DEFAULT '0' NOT NULL,
  username       varchar(32) DEFAULT '' NOT NULL,
  bm_timestamps  varchar(255),
  bm_vists       int default '0' NOT NULL,
  public_f       char(1) DEFAULT 'N' NOT NULL
);

CREATE TABLE bookmarks_rating (
  id             serial,
  name           varchar(30) DEFAULT '' NOT NULL,
  username       varchar(32) DEFAULT '' NOT NULL,
  list_tag       varchar(255)
);

CREATE TABLE bookmarks_category (
  id             serial,
  name           varchar(30) DEFAULT '' NOT NULL,
  username       varchar(32) DEFAULT '' NOT NULL
);

CREATE TABLE bookmarks_subcategory (
  id             serial,
  name           varchar(30) DEFAULT '' NOT NULL,
  username       varchar(32) DEFAULT '' NOT NULL
);

CREATE TABLE bookmarks_search (
  id             serial,
  name           varchar(30) DEFAULT '' NOT NULL,
  query          varchar(255) DEFAULT '' NOT NULL,
  username       varchar(32) DEFAULT '' NOT NULL
);
