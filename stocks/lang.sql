DELETE from lang WHERE app_name='stocks' and lang='en';
DELETE from lang WHERE message_id='Stock Quotes' AND app_name='preferences' AND lang='en' AND content='Stock Quotes'; 
DELETE from lang WHERE message_id='Stock Quotes' AND app_name='common' AND lang='en' AND content='Stock Quotes';
DELETE from lang WHERE message_id='Stock Quotes' AND app_name='admin' AND lang='en' AND content='Stock Quotes';

INSERT INTO lang (message_id, app_name, lang, content) VALUES('Stock Quote preferences','stocks','en','Stock Quote preferences');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Symbol','stocks','en','Symbol');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('disable','stocks','en','disable');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('enable','stocks','en','enable');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Company name','stocks','en','Company name');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Display stocks on main screen is disabled','stocks','en','Display stocks on main screen is disabled');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Display stocks on main screen is enabled','stocks','en','Display stocks on main screen is enabled');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Add new stock','stocks','en','Add new stock');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Edit stock','stocks','en','Edit stock');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Stock Quotes','preferences','en','Stock Quotes');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Select displayed stocks','preferences','en','Select displayed stocks');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Stock Quotes','common','en','Stock Quotes');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Stock Quotes','admin','en','Stock Quotes');

