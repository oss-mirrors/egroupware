DELETE from lang WHERE app_name='projects' and lang='en';
DELETE from lang WHERE app_name='common' and message_id='Projects' and content='Projects' and lang='en';
DELETE from lang WHERE message_id='Project preferences' and app_name='preferences' and lang='en' and content='Project preferences';
DELETE from lang WHERE message_id='Project access' and app_name='preferences' and lang='en' and content='Project access';
DELETE from lang WHERE message_id='Project categories' and app_name='preferences' and lang='en' and content='Project categories';
DELETE from lang WHERE message_id='Project administration' and app_name='admin' and lang='en' and content='Project administration';
DELETE from lang WHERE message_id='Projects' and app_name='admin' and lang='en' and content='Projects';

INSERT INTO lang (message_id, app_name, lang, content) VALUES('Projects','common','en','Projects');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Project preferences','projects','en','Project preferences');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Project preferences','preferences','en','Project preferences');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Please select currency,tax and your address in preferences!','projects','en','Please select currency,tax and your address in preferences!');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Please select your address in preferences!','projects','en','Please select your address in preferences!');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Please select your currency in preferences!','projects','en','Please select your currency in preferences!'); 
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Projects','admin','en','Projects');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Project administration','admin','en','Project administration');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Project administration','projects','en','Project administration');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Edit project administrator list','projects','en','Edit project administrator list');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Select per button !','projects','en','Select per button !');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('You have entered an invalid invoice date !','projects','en','You have entered an invalid invoice date !');  
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Select project','projects','en','Select project');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Select users','projects','en','Select users');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Select groups','projects','en','Select groups');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Projects','projects','en','Projects');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Username / Group','projects','en','Username / Group');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Project access','preferences','en','Project access');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Project categories','preferences','en','Project categories');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Invoice has been created !','projects','en','Invoice has been created !');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('per','projects','en','per');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Invoice has been updated !','projects','en','Invoice has been updated !');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Delivery has been updated !','projects','en','Delivery has been updated !');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('That Delivery ID has been used already !','projects','en','That Delivery ID has been used already !');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Please enter a Delivery ID for that delivery !','projects','en','Please enter a Delivery ID for that delivery !');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('You have entered an invalid delivery date !','projects','en','You have entered an invalid delivery date !');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Delivery has been created !','projects','en','Delivery has been created !');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Activities','projects','en','Activities');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Job','projects','en','Job');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Jobs','projects','en','Jobs');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('View job','projects','en','View job');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Job date','projects','en','Job date');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Job description','projects','en','Job description');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Project','projects','en','Project');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Project hours','projects','en','Project hours');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Project statistics','projects','en','Project statistics');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Project billing','projects','en','Project billing');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Project delivery','projects','en','Project delivery');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('All open hours','projects','en','All open hours');           
INSERT INTO lang (message_id, app_name, lang, content) VALUES('All done hours','projects','en','All done hours');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Activity','projects','en','Activity');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Auto generate Delivery ID ?','projects','en','Auto generate Delivery ID ?');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Auto generate Project ID ?','projects','en','Auto generate Project ID ?');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Auto generate Invoice ID ?','projects','en','Auto generate Invoice ID ?');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Active','projects','en','Active');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('New project','projects','en','New project');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Add hours','projects','en','Add hours');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Activities list','projects','en','Activities list');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Address book','projects','en','Address book');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Add Activity','projects','en','Add activity');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Edit Activity','projects','en','Edit activity');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Project name','projects','en','Project name');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Add project','projects','en','Add project');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Update project','projects','en','Update project');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Update invoice','projects','en','Update invoice');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Update delivery','projects','en','Update delivery');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Title','projects','en','Title');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('net','projects','en','net');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Invoice','projects','en','Invoice');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('User statistics','projects','en','User statistics');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('End date','projects','en','End date');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Date due','projects','en','Date due');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Work date','projects','en','Work date');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Work time','projects','en','Work time');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Statistics','projects','en','Statistics');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Position','projects','en','Position');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Project statistic','projects','en','Project statistic');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Hours','projects','en','Hours');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Account','projects','en','Account');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Billing','projects','en','Billing');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('User statistic','projects','en','User statistic');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Delivery','projects','en','Delivery');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Billed only','projects','en','Billed only');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('List hours','projects','en','List hours');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Job list','projects','en','Job list');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Overall','projects','en','Overall');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Search','projects','en','Search');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Username','projects','en','Username');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Submit','projects','en','Submit');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Date','projects','en','Date');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Budget','projects','en','Budget');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Bookable activities','projects','en','Bookable activities');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Billable activities','projects','en','Billable activities');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Bill per workunit','projects','en','Bill per workunit');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('per workunit','projects','en','per workunit');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Minutes per workunit','projects','en','Minutes per workunit');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Project list','projects','en','Project list');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Edit project','projects','en','Edit project');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Add project hours','projects','en','Add project hours');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Status','projects','en','Status');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Short description','projects','en','Short description');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Nonactive','projects','en','Nonactive');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Description','projects','en','Description');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Template','projects','en','Template');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Remark','projects','en','Remark');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Remark required','projects','en','Remark required');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Time','projects','en','Time');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Start time','projects','en','Start time');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('End time','projects','en','End time');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('archive','projects','en','Archive');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('done','projects','en','Done');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('open','projects','en','Open');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('billed','projects','en','Billed');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Employee','projects','en','Employee');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Edit hours','projects','en','Edit Hours');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Number','projects','en','Number');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Delete hours','projects','en','Delete hours');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Select customer','projects','en','Select customer');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Calculate','projects','en','Calculate');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Statistic','projects','en','Statistic');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Start date','projects','en','Start date');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('There are no entries','projects','en','There are no entries!');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Project ID','projects','en','Project ID');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Activity ID','projects','en','Activity ID');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Please enter a remark !','projects','en','Please enter a remark !');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('You have entered an invalid start date !','projects','en','You have entered an invalid start date !');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('You have entered an invalid end date !','projects','en','You have entered an invalid end date !');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Are you sure you want to delete this entry','projects','en','Are you sure you want to delete this entry?');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Select tax for work hours','projects','en','Select tax for work hours');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Select your address','projects','en','Select your address');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Return to projects','projects','en','Return to projects');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Hours has been added !','projects','en','Hours has been added !');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Hours has been updated !','projects','en','Hours has been updated !');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Please enter the bill per workunit !','projects','en','Please enter the bill per workunit !');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Please enter the minutes per workunit !','projects','en','Please enter the minutes per workunit !');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Activity has been added !','projects','en','Activity has been added !');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Project has been updated !','projects','en','Project has been updated !');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Project has been added !','projects','en','Project has been added !');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Invoice ID','projects','en','Invoice ID');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('That Invoice ID has been used already !','projects','en','That Invoice ID has been used already !');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Please enter an Invoice ID for that invoice !','projects','en','Please enter an Invoice ID for that invoice !');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Please enter an ID for that Activity !','projects','en','Please enter an ID for that Activity !');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('That Activity ID has been used already !','projects','en','That Activity ID has been used already !');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Workunits','projects','en','Workunits');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('You have no customer selected !','projects','en','You have no customer selected !');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Invoice list','projects','en','Invoice list');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('All invoices','projects','en','All invoices');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Invoice date','projects','en','Invoice date');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Create invoice','projects','en','Create invoice');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Update','projects','en','Update');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Firstname','projects','en','Firstname');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Lastname','projects','en','Lastname');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Company','projects','en','Company');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Select','projects','en','Select');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Workunit','projects','en','Workunit');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Sum','projects','en','Sum');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Sum workunits','projects','en','Sum workunits');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Edit','projects','en','Edit');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Coordinator','projects','en','Coordinator');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Admin','projects','en','Admin');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Customer','projects','en','Customer');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Edit project hours','projects','en','Edit project hours');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Print invoice','projects','en','Print invoice');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Entry date','projects','en','Entry date');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('tax','projects','en','tax');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Delivery note','projects','en','Delivery note');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('All delivery notes','projects','en','ALL delivery notes');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Deliveries','projects','en','Deliveries');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Invoices','projects','en','Invoices');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Delivery ID','projects','en','Delivery ID');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Delivery list','projects','en','Delivery list');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Delivery notes','projects','en','Delivery notes');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Projects list','projects','en','Projects list');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Projects archive','projects','en','Projects archive');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('All deliverys','projects','en','All deliverys');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Delivery date','projects','en','Delivery date');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Create delivery','projects','en','Create delivery');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('Print delivery','projects','en','Print delivery');
INSERT INTO lang (message_id, app_name, lang, content) VALUES('You have to CREATE a delivery or invoice first !','projects','en','You have to CREATE a delivery or invoice first !');