-- $Id$

    create table temp as select * from p_projects;
    drop sequence p_projects_id_seq;
    drop table p_projects;

CREATE TABLE phpgw_p_projects (
    id          serial,
    num         varchar(20) NOT NULL,
    owner       int,
    access      char(7),
    category	int,
    entry_date  int,
    start_date  int,
    end_date    int,
    coordinator int,
    customer    int,
    status      text check(status in('active','nonactive','archive')) DEFAULT 'active' NOT NULL,
    descr       text,
    title       varchar(255) NOT NULL,
    budget      decimal(20,2)
);

insert into phpgw_p_projects select * from temp;
drop table temp;

CREATE INDEX phpgw_p_projects_key ON phpgw_p_projects(id,num);

------------------

    create table temp as select * from p_activities;                                                                                                                                        
    drop sequence p_activities_id_seq;
    drop table p_activities;


CREATE TABLE phpgw_p_activities (
    id          serial,
    num         varchar(20) NOT NULL,
    descr       varchar(255) NOT NULL,
    remarkreq   boolean DEFAULT 'n' NOT NULL,
    minperae    decimal(4,0),
    billperae   decimal(20,2)
);

insert into phpgw_p_activities select * from temp;
drop table temp;

CREATE INDEX phpgw_p_activities_key ON phpgw_p_activities(id,num);

-------------------

    create table temp as select * from p_projectactivities;
    drop sequence p_projectactivities_id_seq;
    drop table p_projectactivities;


CREATE TABLE phpgw_p_projectactivities (
    id          serial,
    project_id  int,
    activity_id int,
    billable    boolean DEFAULT 'n' NOT NULL
);

insert into phpgw_p_projectactivities select * from temp;
drop table temp;

--------------------

    create table temp as select * from p_hours;
    drop sequence p_hours_id_seq;
    drop table p_hours;

CREATE TABLE phpgw_p_hours (
    id          serial,
    employee    int,
    project_id  int,
    activity_id int,
    entry_date  int,
    start_date  int,
    end_date    int,
    hours_descr	varchar(255) NOT NULL,
    remark      text,
    minutes     int,
    minperae    decimal(4,0),
    billperae   decimal(20,2),
    status      text check(status in('done','open','billed')) DEFAULT 'done' NOT NULL
);

insert into phpgw_p_hours select * from temp;
drop table temp;

--------------------

    create table temp as select * from p_projectmembers;
    drop sequence p_projectmembers_id_seq;
    drop table p_projectmembers;


CREATE TABLE phpgw_p_projectmembers (
    id          serial,
    project_id  int,
    account_id  int
);

insert into phpgw_p_projectmembers select * from temp;
drop table temp;

-----------------------

    create table temp as select * from p_invoice;
    drop sequence p_invoice_id_seq;
    drop table p_invoice;

CREATE TABLE phpgw_p_invoice (
    id          serial,
    num         varchar(20) NOT NULL,
    date        int,
    project_id  int,
    customer    int,
    sum         decimal(20,2)
);

insert into phpgw_p_invoice select * from temp;
drop table temp;

CREATE INDEX phpgw_p_invoice_key ON phpgw_p_invoice(id,num);

---------------------------

    create table temp as select * from p_invoicepos;
    drop sequence p_invoicepos_id_seq;
    drop table p_invoicepos;

CREATE TABLE phpgw_p_invoicepos (
    id          serial,
    invoice_id  int,
    hours_id    int
);

insert into phpgw_p_invoicepos select * from temp;
drop table temp;

---------------------------

    create table temp as select * from p_delivery;
    drop sequence p_delivery_id_seq;
    drop table p_delivery;

CREATE TABLE phpgw_p_delivery (
    id          serial,
    num         varchar(20) NOT NULL,
    date        int,
    project_id  int,
    customer    int
);

insert into phpgw_p_delivery select * from temp;
drop table temp;

CREATE INDEX phpgw_p_delivery_key ON phpgw_p_delivery(id,num);

---------------------------

    create table temp as select * from p_deliverypos;
    drop sequence p_deliverypos_id_seq;
    drop table p_deliverypos;

CREATE TABLE phpgw_p_deliverypos (
   id          serial,
   delivery_id int,
   hours_id    int
);

insert into phpgw_p_deliverypos select * from temp;
drop table temp;