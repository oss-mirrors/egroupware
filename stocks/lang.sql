# $Id$ 
# First we will delete all entries for stocks, to prevent dups when updating.                                                                        
DELETE from lang WHERE app_name='stocks';
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Stock Quote preferences','stocks','en','Stock Quote preferences');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Symbol','stocks','en','Symbol');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Company name','stocks','en','Company name');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Display stocks on main screen is disabled','stocks','en','Display stocks on main screen is disabled');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Display stocks on main screen is enabled','stocks','en','Display stocks on main screen is enabled');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Add new stock','stocks','en','Add new stock');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Edit stock','stocks','en','Edit stock');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Stock Quotes','preferences','en','Stock Quotes');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Select displayed stocks','preferences','en','Select displayed stocks');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Stocks','common','en','Stocks');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Stocks','admin','en','Stocks');
							    