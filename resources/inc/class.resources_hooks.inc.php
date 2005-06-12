<?php
	/**************************************************************************\
	* eGroupWare - resources hooks                                             *
	* http://www.eGroupWare.org                                                *
	* Originally written by Ralf Becker <RalfBecker@outdoor-training.de>       *
	* Changes for resources by Cornelius Wei�<egw@von-und-zu-weiss.de>        *
	*                                                                          *
	* -------------------------------------------------------                  *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

class resources_hooks
{
	function admin_prefs_sidebox($args)
	{
		$appname = 'resources';
		$location = is_array($args) ? $args['location'] : $args;
		
		if ($location == 'sidebox_menu')
		{
			$title = $GLOBALS['egw_info']['apps']['resources']['title'].' '.lang('Menu');
			$file[] = array(
					'text' => lang('resources list'),
					'no_lang' => true,
					'link' => $GLOBALS['egw']->link('/index.php',array('menuaction' => 'resources.ui_resources.index' )),
// 					'icon' => 
			);
			$file[] = array(
					'text' => '<a class="textSidebox" href="'.$GLOBALS['egw']->link('/index.php',array('menuaction' => 'resources.ui_resources.edit')).
						'" onclick="window.open(this.href,\'_blank\',\'dependent=yes,width=800,height=600,scrollbars=yes,status=yes\'); 
						return false;">'.lang('add resource').'</a>',
					'no_lang' => true,
					'link' => false
			);
			display_sidebox($appname,$title,$file);
		}

/*		if ($GLOBALS['egw_info']['user']['apps']['preferences'] && $location != 'admin')
		{
			$file = array(
				'Preferences'     => $GLOBALS['egw']->link('/preferences/preferences.php','appname='.$appname),
				'Grant Access'    => $GLOBALS['egw']->link('/index.php','menuaction=preferences.uiaclprefs.index&acl_app='.$appname),
				'Edit Categories' => $GLOBALS['egw']->link('/index.php','menuaction=preferences.uicategories.index&cats_app=' . $appname . '&cats_level=True&global_cats=True')
			);
			if ($location == 'preferences')
			{
				display_section($appname,$file);
			}
			else
			{
				display_sidebox($appname,lang('Preferences'),$file);
			}
		}
*/
		if ($GLOBALS['egw_info']['user']['apps']['admin'] && $location != 'preferences')
		{
			$file = Array(
				'Site configuration' => $GLOBALS['egw']->link('/index.php',array(
					'menuaction' => 'resources.ui_resources.admin' )),
				'Global Categories'  => $GLOBALS['egw']->link('/index.php',array(
					'menuaction' => 'admin.uicategories.index',
					'appname'    => $appname,
					'global_cats'=> True)),
				'Configure Access Permissions' => $GLOBALS['egw']->link('/index.php',
					'menuaction=resources.ui_acl.acllist')
// 				'Custom fields, typ and status' => $GLOBALS['egw']->link('/index.php',array(
// 					'menuaction' => 'infolog.uicustomfields.edit')),
// 				'CSV-Import'         => $GLOBALS['egw']->link('/infolog/csv_import.php')
			);
			if ($location == 'admin')
			{
				display_section($appname,$file);
			}
			else
			{
				display_sidebox($appname,lang('Admin'),$file);
			}
		}
	}
	function search_link($args)
	{
		return array(
			'query' => 'resources.bo_resources.link_query',
			'title' => 'resources.bo_resources.link_title',
			'view' => array('menuaction' => 'resources.ui_resources.show'),
			'view_id' => 'id'
		);
	}

	function calendar_resources($args)
	{
		return array(	
			'select_template' => 'resources.resource_selectbox',
			'info' => 'resources.bo_resources.get_calendar_info',
			'new_status' => 'resources.bo_resources.get_calendar_new_status',
			'type' => 'r',
		);
	}
}

?>
