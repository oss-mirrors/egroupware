# $Id$
#######################
# update : 11/26/2000 #
#######################

delete from applications where app_name='projectstatistics';
delete from applications where app_name='projectbilling';
delete from applications where app_name='projectdelivery';
delete from applications where app_name='projecthours';
delete from lang where app_name='projecthours';
delete from lang where app_name='projectstatistics';
delete from lang where app_name='projectbilling';
delete from lang where app_name='projectdelivery';

#######################
# update : 11/30/2000 #
#######################
replace into applications(app_name,app_title,app_enabled,app_tables,app_version) values('projects','Projects',1,'p_activities,p_projectactivities,p_delivery,p_deliverypos,p_hours,p_invoice,p_invoicepos,p_projectmembers,p_projects','0.8.3.1');
drop table p_projectaddress;

#######################                                                                                                                                                                     
# update : 01/29/2001 #                                                                                                                                                                     
#######################
alter table p_projects modify column access varchar(25);