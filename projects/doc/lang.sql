# $Id$
# First we will delete all entries for projects, to prevent dups when updating.                                                                                  
DELETE from lang WHERE app_name='projects';
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Projects','common','en','Projects');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Project preferences','projects','en','Project preferences');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Project preferences','preferences','en','Project preferences');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Please select currency,tax and your address in preferences!','projects','en','Please select currency,tax and your address in preferences!');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Please select your address in preferences!','projects','en','Please select your address in preferences!');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Please select your currency in preferences!','projects','en','Please select your currency in preferences!'); 
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Projects','admin','en','Projects');  
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Select per button !','projects','en','Select per button !');                                                                
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Projects','projects','en','Projects');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Project','projects','en','Project');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('All open hours','projects','en','All open hours');           
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('All done hours','projects','en','All done hours');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Activity','projects','en','Activity');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Active','projects','en','Active');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('New project','projects','en','New project');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Add hours','projects','en','Add hours');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Activities list','projects','en','Activities list');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Address book','projects','en','Address book');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Add Activity','projects','en','Add activity');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Edit Activity','projects','en','Edit activity');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Project name','projects','en','Project name');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Add project','projects','en','Add project');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Update project','projects','en','Update project');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Title','projects','en','Title');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('net','projects','en','net');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Invoice','projects','en','Invoice');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('User statistics','projects','en','User statistics');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Date due','projects','en','Date due');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('End date','projects','en','End date');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Project statistics','projects','en','Project statistics');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Position','projects','en','Position');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Project statistic','projects','en','Project statistic');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Project hours','projects','en','Project hours');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Account','projects','en','Account');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Project billing','projects','en','Project billing');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('User statistic','projects','en','User statistic');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Project delivery','projects','en','Project delivery');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Hours','projects','en','Hours');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Billed only','projects','en','Billed only');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('List hours','projects','en','List hours');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('List project hours','projects','en','List project hours');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Copy template','projects','en','Copy template');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Overall','projects','en','Overall');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Search','projects','en','Search');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Username','projects','en','Username');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Submit','projects','en','Submit');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Date','projects','en','Date');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Budget','projects','en','Budget');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Bookable activities','projects','en','Bookable activities');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Billable activities','projects','en','Billable activities');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Bill per workunit','projects','en','Bill per workunit');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Minutes per workunit','projects','en','Minutes per workunit');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Project list','projects','en','Project list');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Edit project','projects','en','Edit project');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Add project hours','projects','en','Add project hours');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Status','projects','en','Status');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Nonactive','projects','en','Nonactive');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Description','projects','en','Description');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Template','projects','en','Template');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Remark','projects','en','Job [ Remark ]');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Remark required','projects','en','Remark required');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Time','projects','en','Time');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Archiv','projects','en','Archiv');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Done','projects','en','Done');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Open','projects','en','Open');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Billed','projects','en','Billed');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Employee','projects','en','Employee');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Edit hours','projects','en','Edit Hours');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Number','projects','en','Number');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Delete hours','projects','en','Delete hours');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Select customer','projects','en','Select customer');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Calculate','projects','en','Calculate');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Statistic','projects','en','Statistic');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Start date','projects','en','Start date');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('There are no entries','projects','en','There are no entries!');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Project ID','projects','en','Project ID');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Activity ID','projects','en','Activity ID');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('You have selected an invalid activity','projects','en','You have selected an invalid activity!');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('You have selected an invalid date','projects','en','You have selected an invalid date!');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('You have to enter a remark','projects','en','You have to enter a remark!');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Are you sure you want to delete this entry','projects','en','Are you sure you want to delete this entry?');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Select tax for work hours','projects','en','Select tax for work hours'); 
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Select your address','projects','en','Select your address');                                                                            
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Return to projects','projects','en','Return to projects');                                                                              
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Invoice ID','projects','en','Invoice ID');                                                                                                    
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Workunits','projects','en','Workunits');                                                                                                
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('No customer selected','projects','en','No customer selected!');                                                                         
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Invoice list','projects','en','Invoice list');                                                                                           
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('All invoices','projects','en','All invoices');                                                                                          
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Invoice date','projects','en','Invoice date');                                                                                          
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Create invoice','projects','en','Create invoice');                                                                                       
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Update','projects','en','Update');                                                                                                      
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Firstname','projects','en','Firstname');                                                                                                
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Lastname','projects','en','Lastname');                                                                                                  
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Company','projects','en','Company');                                                                                                    
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Activity','projects','en','Activity');                                                                                                  
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Active','projects','en','Active');                                                                                                      
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Select','projects','en','Select');                                                                                                      
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Workunit','projects','en','Workunit');                                                                                                       
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Sum','projects','en','Sum');                                                                                                            
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('New project','projects','en','New project');                                                                                            
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Edit','projects','en','Edit');                                                                                                          
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Coordinator','projects','en','Coordinator');                                                                                            
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Number','projects','en','Number');                                                                                                      
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Status','projects','en','Status');                                                                                                      
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Title','projects','en','Title');                                                                                                        
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Admin','projects','en','Admin');                                                                                                        
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Copy template','projects','en','Copy template');                                                                                        
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Search','projects','en','Search');                                                                                                      
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Submit','projects','en','Submit');                                                                                                      
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Date','projects','en','Date');                                                                                                          
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Budget','projects','en','Budget');                                                                                                      
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Customer','projects','en','Customer');                                                                                                  
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Edit project hours','projects','en','Edit project hours');                                                                            
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Print invoice','projects','en','Print invoice');                                                                                        
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Status','projects','en','Status');                                                                                                      
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Entry date','projects','en','Entry date');                                                                                              
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Nonactive','projects','en','Nonactive');                                                                                                
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Description','projects','en','Description');                                                                                            
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Time','projects','en','Time');                                                                                                          
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Archiv','projects','en','Archiv');                                                                                                      
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('tax','projects','en','tax');                                                                                                           
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Done','projects','en','Done');                                                                                                          
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Delivery note','projects','en','Delivery note');                                                                               
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('All delivery notes','projects','en','ALL delivery notes');                                                              
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Delivery ID','projects','en','Delivery ID');                                                                                         
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Delivery list','projects','en','Delivery list');    
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Delivery','projects','en','Delivery');                                                                                
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('All deliverys','projects','en','All deliverys');                                                                               
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Delivery date','projects','en','Delivery date');                                                                               
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Create delivery','projects','en','Create delivery');                                                                            
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('Print delivery','projects','en','Print delivery'); 
REPLACE INTO lang (message_id, app_name, lang, content) VALUES('You have to CREATE a delivery or invoice first !','projects','en','You have to CREATE a delivery or invoice first !');                                                                                              