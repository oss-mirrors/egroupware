create table ticket (
  t_id		serial,
  t_category	varchar(40) not null,
  t_detail	text,
  t_priority	smallint,
  t_user	varchar(10) not null,
  t_assignedto	varchar(10) not null,
  t_timestamp_opened	int,
  t_timestamp_closed	int,
  t_department	varchar(25)
);

create table category (
  c_id		 serial,
  c_department	 varchar(25) not null,
  c_name	 varchar(40) not null
);

create table department (
  d_name	 varchar(25) not null
);