BEGIN WORK;

create table applications (
  app_name varchar(25) NOT NULL,
  app_title varchar(50),
  app_enabled int,
  unique(app_name)
);

insert into applications (app_name, app_title, app_enabled) values ('admin', 'Administration', 1);
insert into applications (app_name, app_title, app_enabled) values ('tts', 'Trouble Ticket System', 1);
insert into applications (app_name, app_title, app_enabled) values ('inv', 'Inventory', 1);
insert into applications (app_name, app_title, app_enabled) values ('chat', 'Chat', 1);
insert into applications (app_name, app_title, app_enabled) values ('headlines', 'Headlines', 1);
insert into applications (app_name, app_title, app_enabled) values ('filemanager', 'File manager', 1);
insert into applications (app_name, app_title, app_enabled) values ('ftp', 'FTP', 1);
insert into applications (app_name, app_title, app_enabled) values ('addressbook', 'Address Book', 1);
insert into applications (app_name, app_title, app_enabled) values ('todo', 'ToDo List', 1);
insert into applications (app_name, app_title, app_enabled) values ('calendar', 'Calendar', 1);
insert into applications (app_name, app_title, app_enabled) values ('email', 'Email', 1);
insert into applications (app_name, app_title, app_enabled) values ('nntp', 'NNTP', 1);
insert into applications (app_name, app_title, app_enabled) values ('bookmarks', 'Bookmarks', 0);
insert into applications (app_name, app_title, app_enabled) values ('cron_apps', 'cron_apps', 0);
insert into applications (app_name, app_title, app_enabled) values ('napster', 'Napster', 0);

COMMIT;
