-- $Id$
-------------------------
-- update : 11/26/2000  -
-------------------------

delete from applications where app_name='projectstatistics';
delete from applications where app_name='projectbilling';
delete from applications where app_name='projectdelivery';
delete from applications where app_name='projecthours';
delete from lang where app_name='projecthours';
delete from lang where app_name='projectstatistics';
delete from lang where app_name='projectbilling';
delete from lang where app_name='projectdelivery';

--------------------------
-- update : 11/30/2000  --
--------------------------
update applications set app_title='Projects',app_enabled=1,app_tables='p_activities,p_projectactivities,p_delivery,p_deliverypos,p_hours,p_invoice,p_invoicepos,p_projectmembers,p_projects',app_version='0.8.3.1' where app_name='projects';
drop table p_projectaddress;

--------------------------                                                                                                                             
--  update : 01/29/2001 --                                                                                                                             
--------------------------
create table temp as select * from p_projects;
drop sequence p_projects_id_seq;
drop table p_projects;
CREATE TABLE p_projects (                                                                                                                           
   id          serial,                                                                                                                              
   num         varchar(20) NOT NULL,                                                                                                                
   owner       int,                                                                                                                                 
   access      varchar(25),                                                                                                                         
   entry_date  int,                                                                                                                                 
   date        int,                                                                                                                                 
   end_date    int,                                                                                                                                 
   coordinator int,                                                                                                                                 
   customer    int,                                                                                                                                 
   status      text check(status in('active','nonactive','archiv')) DEFAULT 'active' NOT NULL,                                                      
   descr       text,                                                                                                                                
   title       varchar(50),                                                                                                                         
   budget      decimal(20,2)                                                                                                                        
);
insert into p_projects select * from temp;
drop table temp;

--------------------------                                                                                                                             
-- update : 02/05/2001  --                                                                                                                             
--------------------------
create table temp as select * from p_invoicepos;                                                                                                      
drop sequence p_invoicepos_id_seq;                                                                                                                    
drop table p_invoicepos;                                                                                                                              
CREATE TABLE p_invoicepos (                                                                                                                         
   id          serial,                                                                                                                              
   invoice_id  int,                                                                                                                                 
   hours_id    int                                                                                                                                  
);
insert into p_invoicepos select * from temp;                                                                                                         
drop table temp;

create table temp as select * from p_deliverypos;                                                                                                      
drop sequence p_deliverypos_id_seq;                                                                                                                    
drop table p_deliverypos;                                                                                                                              
CREATE TABLE p_deliverypos (                                                                                                                        
   id          serial,                                                                                                                              
   delivery_id int,                                                                                                                                 
   hours_id    int                                                                                                                                  
);
insert into p_deliverypos select * from temp;                                                                                                         
drop table temp;

