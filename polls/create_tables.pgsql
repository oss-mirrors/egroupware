CREATE TABLE phpgw_polls_data (
   poll_id      int NOT NULL,
   option_text  char(50) NOT NULL,
   option_count int DEFAULT '0' NOT NULL,
   vote_id      serial
);

CREATE TABLE phpgw_polls_desc (
   poll_id        serial,
   poll_title     char(100) NOT NULL,
   poll_timestamp int NOT NULL
);

CREATE TABLE phpgw_polls_user (
   poll_id        int NOT NULL,
   vote_id        int DEFAULT '0' NOT NULL,
   user_id        int NOT NULL,
   vote_timestamp int
);

insert into phpgw_polls_desc (poll_title,poll_timestamp) values ('What came first ?',date_part('epoch',now()));
insert into phpgw_polls_data (poll_id,option_text,vote_id) values ('1','The chicken','1');
insert into phpgw_polls_data (poll_id,option_text,vote_id) values ('1','The egg','2');
insert into applications (app_name,app_title,app_enabled,app_order,app_tables,app_version) values ('polls','Vooting Booth','1','','phpgw_polls_user:user_id','0.8.1pre1');

