-- $Id$

CREATE TABLE phpgw_p_projects (
   id          serial,
   num         varchar(20) NOT NULL,
   owner       int,
   entry_date  date,
   start_date  date,
   end_date    date,
   coordinator int,
   customer    int,
   status      text check(status in('active','nonactive','archiv')) DEFAULT 'active' NOT NULL,
   descr       text,
   title       varchar(255) NOT NULL,
   budget      decimal(20,2)
);

CREATE INDEX phpgw_p_projects_key ON phpgw_p_projects(id,num);

CREATE TABLE phpgw_p_activities (
   id          serial,
   num         varchar(20) NOT NULL,
   descr       varchar(255) NOT NULL,
   remarkreq   boolean DEFAULT 'n' NOT NULL,
   minperae    decimal(4,0),
   billperae   decimal(20,2)
);

CREATE INDEX phpgw_p_activities_key ON phpgw_p_activities(id,num);

CREATE TABLE phpgw_p_projectactivities (
   id          serial,
   project_id  int,
   activity_id int,
   billable    boolean DEFAULT 'n' NOT NULL
);

CREATE TABLE phpgw_p_hours (
   id          serial,
   employee    int,
   project_id  int,
   activity_id int,
   entry_date  date,
   date        date,
   end_date    date,
   remark      text,
   minutes     int,
   minperae    decimal(4,0),
   billperae   decimal(20,2),
   status      text check(status in('open','done','billed')) DEFAULT 'open' NOT NULL
);

CREATE TABLE phpgw_p_projectmembers (
   id          serial,
   project_id  int,
   account_id  int
);

CREATE TABLE phpgw_p_invoice (
   id          serial,
   num         varchar(20) NOT NULL,
   date        date,
   project_id  int,
   customer    int,
   sum         decimal(20,2)
);

CREATE INDEX phpgw_p_invoice_key ON phpgw_p_invoice(id,num);

CREATE TABLE phpgw_p_invoicepos (
   id          serial,
   invoice_id  int,
   hours_id    int
);

CREATE TABLE phpgw_p_delivery (
   id          serial,
   num         varchar(20) NOT NULL,
   date        date,
   project_id  int,
   customer    int
);

CREATE INDEX phpgw_p_delivery_key ON phpgw_p_delivery(id,num);

CREATE TABLE phpgw_p_deliverypos (
   id          serial,
   delivery_id int,
   hours_id    int
);
