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
	create_check_box('Column View in instance lists','wf_instances_show_view_column','Do you want the view column on instances lists, link to a read-only view on the instance datas',0);
	create_check_box('Always show advanced mode','wf_instances_show_advanced_mode','Should we always give you the advanced search row on instances lists?',0);
	create_check_box('Always show advanced actions','wf_instances_show_advanced_actions','When in advanced mode, should we show you advanced actions by default (resume, exception, grab, etc.)?',0);
	show_list(lang('User Instances'));
?>
