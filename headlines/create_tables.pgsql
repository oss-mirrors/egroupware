BEGIN WORK;

CREATE TABLE news_site (
  con		serial,
  display	varchar(255),
  base_url	varchar(255),
  newsfile	varchar(255),
  lastread	int,
  newstype	varchar(15),
  cachetime	int,
  listings	int
);
insert into news_site (display,base_url,newsfile,lastread,newstype,cachetime,listings) values ('Slashdot','http://slashdot.org','/slashdot.rdf',0,'rdf',60,20);
insert into news_site (display,base_url,newsfile,lastread,newstype,cachetime,listings) values ('Freshmeat','http://freshmeat.net','/backend/fm.rdf',0,'fm',60,20);
insert into news_site (display,base_url,newsfile,lastread,newstype,cachetime,listings) values ('Linux&nbsp;Today','http://linuxtoday.com','/backend/linuxtoday.xml',0,'lt',60,20);
insert into news_site (display,base_url,newsfile,lastread,newstype,cachetime,listings) values ('Linux&nbsp;Game&nbsp;Tome','http://happypenguin.org','/html/news.rdf',0,'rdf',60,20);
insert into news_site (display,base_url,newsfile,lastread,newstype,cachetime,listings) values ('linux-at-work.de','http://linux-at-work.de','/backend.php',0,'rdf',60,20);
insert into news_site (display,base_url,newsfile,lastread,newstype,cachetime,listings) values ('Segfault','http://segfault.org','/stories.xml',0,'sf',60,20);
insert into news_site (display,base_url,newsfile,lastread,newstype,cachetime,listings) values ('KDE&nbsp;News','http://www.kde.org','/news/kdenews.rdf',0,'rdf',60,20);
insert into news_site (display,base_url,newsfile,lastread,newstype,cachetime,listings) values ('Gnome&nbsp;News','http://news.gnome.org','/gnome-news/rdf',0,'rdf',60,20);
insert into news_site (display,base_url,newsfile,lastread,newstype,cachetime,listings) values ('Gimp&nbsp;News','http://www.xach.com','/gimp/news/channel.rdf',0,'rdf-chan',60,20);
insert into news_site (display,base_url,newsfile,lastread,newstype,cachetime,listings) values ('Mozilla','http://www.mozilla.org','/news.rdf',0,'rdf-chan',60,20);
insert into news_site (display,base_url,newsfile,lastread,newstype,cachetime,listings) values ('MozillaZine','http://www.mozillazine.org','/contents.rdf',0,'rdf',60,20);

CREATE TABLE news_headlines (
  site		int,
  title		varchar(255),
  link		varchar(255)
);

CREATE TABLE users_headlines (
  owner		varchar(25) not null,
  site		int
);

COMMIT;
