-- $Id$

CREATE TABLE phpgw_bookmarks (
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
