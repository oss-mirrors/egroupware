<?php
	/**************************************************************************\
	* eGroupWare - Preferences                                                 *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */
	
	// ui_userinstance preferences
	create_section(lang('Global Workflow Preferences'));
       create_select_box('Starting page','startpage',array(
               'workflow.ui_userprocesses'     => 'My processes',
               'workflow.ui_useractivities'    => 'My activities',
               'workflow.ui_userinstances'     => 'My instances',
               'workflow.ui_useractivities2'   => 'Global activities',
               'workflow.ui_useropeninstance'  => 'Open Instances'),
               'This is the first screen shown when you click on the workflow application icon');
       
        create_section(lang('User Instances form: columns'));
       create_check_box('Column instance id in instance lists','wf_instances_show_instance_id_column','Do you want the instance id column on instances lists. This is the unique identifier of an instance',1);
       create_check_box('Column start date in instance lists','wf_instances_show_started_column','Do you want the started date column on instances lists.',1);
       create_check_box('Column priority in instance lists','wf_instances_show_priority_column','Do you want the priority column on instances lists. Priority can be set with activities forms',1);
       create_check_box('Column instance status in instance lists','wf_instances_show_instance_status_column','Do you want the instance status on instances lists. The instance status is usefull to disting beteween aborted, completed, exception or active instances',1);
       create_check_box('Column instance name in instance lists','wf_instances_show_instance_name_column','Do you want the instance name column on instances lists. If your instances have name you should really use this',1);
       create_check_box('Column process name in instance lists','wf_instances_show_process_name_column','Do you want the process column on instances lists. Usefull if you have different processes and/or versions of theses processes',1);
       create_check_box('Column category in instance lists','wf_instances_show_category_column','Do you want the category name column on instances lists. If your instances use categories you should use this',1);
       create_check_box('Column activity status in instance lists','wf_instances_show_activity_status_column','Do you want the activity status on instances lists. Most of the time it is "running" but if you use non-autorouted transitions you will have some completed activities.',0);
       create_check_box('Column owner in instance lists','wf_instances_show_owner_column' ,'Do you want the owner column on instances lists. This will show you the actual owner, especially usefull if ownership is defined with special rights',1);
       create_check_box('Column category in instance lists','wf_instances_show_category_column','Do you want the category column on instances lists, if yout instances use categories you should use it',1);
       
        create_section(lang('User Instances form: filters and actions'));
       create_check_box('Search instance filter in the bottom of instance lists','wf_instances_show_instance_search','Do you want the search instance button in the last row of instances list.',0);
       create_check_box('Always show advanced mode','wf_instances_show_advanced_mode','Should we always give you the advanced search row on instances lists?',0);
       create_check_box('Always show advanced actions','wf_instances_show_advanced_actions','When in advanced mode, should we show you advanced actions by default (resume, exception, grab, etc.)?',0);

?>
