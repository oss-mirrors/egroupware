<?php
	/**************************************************************************\
	* phpGroupWare - projects                                                  *
	* (http://www.phpgroupware.org)                                            *
	* Written by Bettina Gille  [ceb@phpgroupware.org]                         * 
	*          & Jens Lentfoehr <sw@lf.shlink.de>                              *
	* ------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU General Public License as published by the    *
	* Free Software Foundation; either version 2 of the License, or (at your   *
	* option) any later version.                                               *
	\**************************************************************************/
	/* $Id$ */

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array('projects_header' => 'header.tpl'));

	$admin_info = lang('Administrator');
	$t->set_var('admin_info',$admin_info);
	$t->set_var('th_bg',$phpgw_info['theme']['th_bg']);
	$t->set_var('row_on',$phpgw_info['theme']['row_on']);
	$isadmin = isprojectadmin();

	if ($isadmin==1) {
		$t->set_var('link_activities',$phpgw->link('/projects/activities.php'));
		$t->set_var('lang_activities',lang('Activities'));
	}
	else
	{
		$t->set_var('link_activities','');
		$t->set_var('lang_activities','');
	}

	$t->set_var('link_billing',$phpgw->link('/projects/bill_index.php'));
	$t->set_var('lang_billing',lang('Billing'));
	$t->set_var('link_jobs',$phpgw->link('/index.php','menuaction=projects.uiprojects.list_sub_projects'));
	$t->set_var('lang_jobs',lang('Jobs'));
	$t->set_var('link_hours',$phpgw->link('/projects/hours_listhours.php'));
	$t->set_var('lang_hours',lang('Work hours'));
	$t->set_var('link_statistics',$phpgw->link('/projects/stats_projectlist.php'));
	$t->set_var('lang_statistics',lang("Statistics"));
	$t->set_var('link_delivery',$phpgw->link('/projects/del_index.php'));
	$t->set_var('lang_delivery',lang('Delivery'));
	$t->set_var('link_projects',$phpgw->link('/index.php','menuaction=projects.uiprojects.list_projects'));
	$t->set_var('lang_projects',lang('Projects'));
	$t->set_var('link_archiv',$phpgw->link('/projects/archive.php'));
	$t->set_var('lang_archiv',lang('archive'));

	$t->pparse('out','projects_header');
?>
