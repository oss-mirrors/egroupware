-- $Id$

CREATE TABLE p_projects (
   id          serial,
   num         varchar(20) NOT NULL,
   owner       int,
   access      varchar(25),
   entry_date  int,
   date        int,
   end_date    int,
   coordinator int,
   customer    int,
   status      text check(status in('active','nonactive','archiv','template')) DEFAULT 'nonactive' NOT NULL,
   descr       text,
   title       varchar(50),
   budget      decimal(20,2)
);

CREATE TABLE p_activities (
   id          serial,
   num         varchar(20),
   descr       varchar(50),
   remarkreq   boolean DEFAULT 'n' NOT NULL,
   minperae    decimal(4,0),
   billperae   decimal(20,2)
);

CREATE TABLE p_projectactivities (
   id          serial,
   project_id  int,
   activity_id int,
   billable    boolean DEFAULT 'n' NOT NULL
);

CREATE TABLE p_hours (
   id          serial,
   employee    int NOT NULL,
   project_id  int,
   activity_id int,
   entry_date  int,
   date        int,
   end_date    int,
   remark      text,
   minutes     int,
   minperae    decimal(4,0),
   billperae   decimal(20,2),
   status      text check(status in('open','done','billed')) DEFAULT 'done' NOT NULL
);

CREATE TABLE p_projectmembers (
   id          serial,
   project_id  int,
   account_id  int
);

CREATE TABLE p_invoice (
   id          serial,
   num         varchar(11) NOT NULL,
   date        int,
   project_id  int,
   customer    int,
   sum         decimal(20,2),
   UNIQUE (num)
);

CREATE TABLE p_invoicepos (
   id          serial,
   invoice_id  varchar(11) NOT NULL,
   hours_id    int
);

CREATE TABLE p_delivery (
   id          serial,
   num         varchar(11) NOT NULL,
   date        int,
   project_id  int,
   customer    int,
   UNIQUE (num)
);

CREATE TABLE p_deliverypos (
   id          serial,
   delivery_id varchar(11) NOT NULL,
   hours_id    int
);

create unique index project_num on p_projects (num);
