
insert into applications (app_name,app_title,app_enabled,app_tables,app_version) values ('projects','Projects',1,'p_activities,p_delivery,p_deliverypos,p_hours,p_invoive,p_invoicepos,p_projectaddress,p_projectmembers,p_projects','0.8.2');

 CREATE TABLE p_projects (
	id		serial,
	num		varchar(20) NOT NULL,
	owner		int,
	access		varchar(10),
	entry_date	int,
	date		int,
        end_date        int,
	coordinator	int,
	customer	int,
	status		text check(status in('active','nonactive','archiv','template')) DEFAULT 'nonactive' NOT NULL,
	descr		text,
	title		varchar(50),
	budget		decimal(20,2),
	PRIMARY KEY (id)
);

CREATE TABLE p_activities (
	id		serial,
	num		varchar(20),
	descr		varchar(50),
	remarkreq	boolean DEFAULT 'n' NOT NULL,
	minperae	decimal(4,0),
	billperae	decimal(20,2),
	PRIMARY KEY (id)
);

CREATE TABLE p_projectactivities (
	id		serial,
	project_id	int,
	activity_id	int,
	billable	boolean DEFAULT 'n' NOT NULL,
	PRIMARY KEY (id)
);

CREATE TABLE p_hours (
	id		serial,
	employee	int NOT NULL,
	project_id	int,
	activity_id	int,
	entry_date	int,
	date		int,
        end_date        int,
	remark		text,
	minutes		int,
	minperae	decimal(4,0),
	billperae	decimal(20,2),
	status		text check(status in('open','done','billed')) DEFAULT 'done' NOT NULL,
	PRIMARY KEY (id)
);

CREATE TABLE p_projectaddress (
	id		serial,
	project_id	int,
	addressbook_id	int,
	PRIMARY KEY (id)
);

CREATE TABLE p_projectmembers (
	id		serial,
	project_id	int,
	account_id	int,
	PRIMARY KEY (id)
);

CREATE TABLE p_invoice (
	id		serial,
	num		varchar(11) NOT NULL,
	date		int,
	project_id	int,
	customer	int,
	sum		decimal(20,2),
	PRIMARY KEY (id),
	UNIQUE (num)
);

CREATE TABLE p_invoicepos (
	id		serial,
	invoice_id	varchar(11) NOT NULL,
	hours_id	int,
	PRIMARY KEY (id)
);

CREATE TABLE p_delivery (
	id		serial,
	num		varchar(11) NOT NULL,
	date		int,
	project_id	int,
	customer	int,
	PRIMARY KEY (id),
	UNIQUE (num)
);

CREATE TABLE p_deliverypos (
	id		serial,
	delivery_id	varchar(11) NOT NULL,
	hours_id	int,
	PRIMARY KEY (id)
);

create unique index project_num on p_projects (num);
