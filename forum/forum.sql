create table f_categories (
	id int(11) default '0' not null auto_increment,
	name varchar(50) default '' not null,
	primary key (id)
);


create table f_forums (
	id int(11) default '0' not null auto_increment,
	name varchar(50) default '' not null,
	perm tinyint(1) default '0' not null,
	groups varchar(50) default '0' not null,
	primary key (id)
);


create table f_threads (
	id int(11) default '0' not null auto_increment,
	date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	main int(11) default '0' not null,
	parent int(11) default '0' not null,
	cat_id int(11) default '0' not null,
	for_id int(11) default '0' not null,
	author varchar(50) default '' not null,
	subject varchar(50) default '' not null,
	email varchar(50) default '' not null,
	host varchar(18) default '' not null,
	stat tinyint(1) default '0' not null,
	primary key (id),
	key date (date)
);

create table f_body ( 
	id int(11) default '0' not null auto_increment,
	cat_id int(11) default '0' not null,
	for_id int(11) default '0' not null,
	message blob not null,
	primary key (id)
);
	