<?php
/**************************************************************************\
* eGroupWare - Addressbook Admin-, Preferences- and SideboxMenu-Hooks      *
* http://www.eGroupWare.org                                                *
* Written and (c) 2006 by Ralf Becker <RalfBecker@outdoor-training.de>     *
* ------------------------------------------------------------------------ *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

/* $Id$ */

/**
 * Class containing admin, preferences and sidebox-menus (used as hooks)
 *
 * @package addressbook
 * @author Ralf Becker <RalfBecker@outdoor-training.de>
 * @copyright (c) 2006 by Ralf Becker <RalfBecker@outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 */
class contacts_admin_prefs
{
	var $contacts_repository = 'sql';
	
	/**
	 * constructor
	 */
	function contacts_admin_prefs()
	{
		if($GLOBALS['egw_info']['server']['contact_repository'] == 'ldap') $this->contacts_repository = 'ldap';
	}

	/**
	 * hooks to build projectmanager's sidebox-menu plus the admin and preferences sections
	 *
	 * @param string/array $args hook args
	 */
	function all_hooks($args)
	{
		$appname = 'addressbook';
		$location = is_array($args) ? $args['location'] : $args;
		//echo "<p>contacts_admin_prefs::all_hooks(".print_r($args,True).") appname='$appname', location='$location'</p>\n";

		if ($location == 'sidebox_menu')
		{
			$file = array(
				array(
					'text' => '<a class="textSidebox" href="'.$GLOBALS['egw']->link('/index.php',array('menuaction' => 'addressbook.uicontacts.edit')).
						'" onclick="window.open(this.href,\'_blank\',\'dependent=yes,width=850,height=440,scrollbars=yes,status=yes\'); 
						return false;">'.lang('Add').'</a>',
					'no_lang' => true,
					'link' => false
				),
// Disabled til they are working again
//				'Advanced search'=>$GLOBALS['egw']->link('/index.php','menuaction=addressbook.uicontacts.search'),
//				'import contacts' => $GLOBALS['egw']->link('/index.php','menuaction=addressbook.uiXport.import'),
//				'export contacts' => $GLOBALS['egw']->link('/index.php','menuaction=addressbook.uiXport.export')
//				'CSV-Import'      => $GLOBALS['egw']->link('/addressbook/csv_import.php')
			);
			display_sidebox($appname,lang('Addressbook menu'),$file);
		}

		if ($GLOBALS['egw_info']['user']['apps']['preferences'] && $location != 'admin')
		{
			$file = array(
				'Preferences'     => $GLOBALS['egw']->link('/index.php','menuaction=preferences.uisettings.index&appname='.$appname),
				'Grant Access'    => $GLOBALS['egw']->link('/index.php','menuaction=preferences.uiaclprefs.index&acl_app='.$appname),
				'Edit Categories' => $GLOBALS['egw']->link('/index.php','menuaction=preferences.uicategories.index&cats_app=' . $appname . '&cats_level=True&global_cats=True')
			);
			if ($this->contacts_repository == 'ldap' || $GLOBALS['egw_info']['server']['deny_user_grants_access'])
			{
				unset($file['Grant Access']);
			}
			if ($location == 'preferences')
			{
				display_section($appname,$file);
			}
			else
			{
				display_sidebox($appname,lang('Preferences'),$file);
			}
		}

		if ($GLOBALS['egw_info']['user']['apps']['admin'] && $location != 'preferences')
		{
			$file = Array(
				'Site configuration' => $GLOBALS['egw']->link('/index.php',array(
					'menuaction' => 'admin.uiconfig.index',
					'appname'    => $appname,
				)),
				'Global Categories'  => $GLOBALS['egw']->link('/index.php',array(
					'menuaction' => 'admin.uicategories.index',
					'appname'    => $appname,
					'global_cats'=> True)),
				'Custom fields' => $GLOBALS['egw']->link('/index.php',array(
					'menuaction' => 'admin.customfields.edit',
					'appname'    => $appname,
				)),
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
	
	/**
	 * populates $GLOBALS['settings'] for the preferences
	 */
	function settings()
	{
		$GLOBALS['settings']['mainscreen_showbirthdays'] = array(
			'type'   => 'check',
			'label'  => 'Show birthday reminders on main screen',
			'name'   => 'mainscreen_showbirthdays',
			'help'   => 'Displays a remider for birthdays happening today or tomorrow on the startpage (page you get when you enter eGroupWare or click on the homepage icon).',
			'xmlrpc' => True,
			'admin'  => False,
		);
		$column_display_options = array(
			''       => lang('only if there is content'),
			'always' => lang('always'),
			'never'  => lang('never'),
		);
		foreach(array(
			'photo_column' => lang('Photo'),
			'home_column'  => lang('Home address'),
			'custom_colum' => lang('custom fields'),
		) as $name => $label)
		{
			$GLOBALS['settings'][$name] = array(
				'type'   => 'select',
				'label'  => lang('Show a column for %1',$label),
				'run_lang' => -1,
				'name'   => $name,
				'values' => $column_display_options,
				'help'   => 'When should the contacts list display that colum. "Only if there is content" hides the column, unless there is some content in the view.',
				'xmlrpc' => True,
				'admin'  => false,
			);
		}
		if ($this->contacts_repository == 'sql')
		{
			$GLOBALS['settings']['private_addressbook'] = array(
				'type'   => 'check',
				'label'  => 'Enable an extra private addressbook',
				'name'   => 'private_addressbook',
				'help'   => 'Do you want a private addressbook, which can not be viewed by users, you grant access to your personal addressbook?',
				'xmlrpc' => True,
				'admin'  => False,
			);
		}
		return true;	// otherwise prefs say it cant find the file ;-)
	}

	/**
	 * add an Addressbook tab to Admin >> Edit user
	 */
	function edit_user()
	{
		global $menuData;

		$menuData[] = array(
			'description' => 'Addressbook',
			'url'         => '/index.php',
			'extradata'   => 'menuaction=addressbook.uicontacts.edit',
			'options'     => "onclick=\"window.open(this,'_blank','dependent=yes,width=850,height=440,scrollbars=yes,status=yes'); return false;\"".
				' title="'.htmlspecialchars(lang('Edit extra account-data in the addressbook')).'"',
		);
	}
}
