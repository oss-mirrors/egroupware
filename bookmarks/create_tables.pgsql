-- $Id$

CREATE TABLE bookmarks (
  bm_id          serial,
  bm_owner       int,
  bm_access      varchar(255),
  bm_url         varchar(255),
  bm_name        varchar(255),
  bm_desc        varchar(255),
  bm_keywords    varchar(255),
  bm_category    int,
  bm_subcategory int,
  bm_rating      int,
  bm_info        varchar(255),
  bm_visits      int
);

CREATE TABLE bookmarks_search (
  id             serial,
  name           varchar(30) DEFAULT '' NOT NULL,
  query          varchar(255) DEFAULT '' NOT NULL,
  username       varchar(32) DEFAULT '' NOT NULL
);
