<?php
    /**************************************************************************\
    * phpGroupWare - about                                                     *
    * http://www.phpgroupware.org                                              *
    * --------------------------------------------                             *
    * This program is free software; you can redistribute it and/or modify it  *
    * under the terms of the GNU General Public License as published by the    *
    * Free Software Foundation; either version 2 of the License, or (at your   *
    * option) any later version.                                               *
    \**************************************************************************/
    /* $Id$ */

	function about_app($tpl,$handle)
	{
		$t = CreateObject('phpgwapi.Template',$GLOBALS['phpgw']->common->get_tpl_dir('phpgwapi'));
		$s = $t->set_file(array('about' => 'about_app.tpl'));
		$s .= $t->set_var('app_title',lang('Backup'));
		$s .= $t->set_var('lang_version',lang('Version'));
		$s .= $t->set_var('app_version',$GLOBALS['phpgw_info']['apps']['backup']['version']);
		$s .= $t->set_var('written_by',lang('written by'));
		$s .= $t->set_var('developers','Bettina Gille&nbsp;&nbsp;[ceb@phpgroupware.org]');
		$s .= $t->set_var('description',lang('phpGroupWare data backup for sql,ldap and email'));
		$s .= $t->fp('out','about');
		return $s;
	}
