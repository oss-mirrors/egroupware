<?php
  /**************************************************************************\
  * eGroupWare                                                               *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	// Little file to setup a demo install

	$GLOBALS['egw_info'] = array(
		'flags' => array(
			'noheader'   => True,
			'nonavbar'   => True,
			'currentapp' => 'home',
			'noapi'      => True
	));
	include('./inc/functions.inc.php');

	// Authorize the user to use setup app and load the database
	// Does not return unless user is authorized
	if(!$GLOBALS['egw_setup']->auth('Config') || get_var('cancel',Array('POST')))
	{
		Header('Location: index.php');
		exit;
	}

	if(!get_var('submit',Array('POST')))
	{
		$tpl_root = $GLOBALS['egw_setup']->html->setup_tpl_dir('setup');
		$setup_tpl = CreateObject('setup.Template',$tpl_root);
		$setup_tpl->set_file(array(
			'T_head'       => 'head.tpl',
			'T_footer'     => 'footer.tpl',
			'T_alert_msg'  => 'msg_alert_msg.tpl',
			'T_login_main' => 'login_main.tpl',
			'T_login_stage_header' => 'login_stage_header.tpl',
			'T_setup_demo' => 'setup_demo.tpl'
		));
		$setup_tpl->set_block('T_login_stage_header','B_multi_domain','V_multi_domain');
		$setup_tpl->set_block('T_login_stage_header','B_single_domain','V_single_domain');

		$GLOBALS['egw_setup']->html->show_header(lang('Demo Server Setup'));

		$setup_tpl->set_var('action_url','setup_demo.php');
		$setup_tpl->set_var('description',lang('<b>This will create 1 admin account and 3 demo accounts</b><br />The username/passwords are: demo/guest, demo2/guest and demo3/guest.'));
		$setup_tpl->set_var('lang_deleteall',lang('Delete all existing SQL accounts, groups, ACLs and preferences (normally not necessary)?'));

		$setup_tpl->set_var('detailadmin',lang('Details for Admin account'));
		$setup_tpl->set_var('adminusername',lang('Admin username'));
		$setup_tpl->set_var('adminfirstname',lang('Admin first name'));
		$setup_tpl->set_var('adminlastname',lang('Admin last name'));
		$setup_tpl->set_var('adminpassword',lang('Admin password'));
		$setup_tpl->set_var('adminpassword2',lang('Re-enter password'));
		$setup_tpl->set_var('create_demo_accounts',lang('Create demo accounts'));

		$setup_tpl->set_var('lang_submit',lang('Save'));
		$setup_tpl->set_var('lang_cancel',lang('Cancel'));
		$setup_tpl->pparse('out','T_setup_demo');
		$GLOBALS['egw_setup']->html->show_footer();
	}
	else
	{
		/* Posted admin data */
		$passwd   = get_var('passwd',Array('POST'));
		$passwd2  = get_var('passwd2',Array('POST'));
		$username = get_var('username',Array('POST'));
		$fname    = get_var('fname',Array('POST'));
		$lname    = get_var('lname',Array('POST'));

		if($passwd != $passwd2)
		{
			echo lang('Passwords did not match, please re-enter') . '.';
			exit;
		}
		if(!$username)
		{
			echo lang('You must enter a username for the admin') . '.';
			exit;
		}

		$GLOBALS['egw_setup']->loaddb();
		/* Begin transaction for acl, etc */
		$GLOBALS['egw_setup']->db->transaction_begin();

		if($_POST['delete_all'])
		{
			/* Now, clear out existing tables */
			foreach(array($GLOBALS['egw_setup']->accounts_table,$GLOBALS['egw_setup']->prefs_table,$GLOBALS['egw_setup']->acl_table) as $table)
			{
				$GLOBALS['egw_setup']->db->delete($table,'1=1');
			}
			/* Clear the access log, since these are all new users anyway */
			$GLOBALS['egw_setup']->db->query('DELETE FROM egw_access_log');
		}
		/* Create the demo groups */
		$defaultgroupid = (int)$GLOBALS['egw_setup']->add_account('Default','Default','Group',False,False);
		$admingroupid   = (int)$GLOBALS['egw_setup']->add_account('Admins','Admin','Group',False,False);
		
		if (!$defaultgroupid || !$admingroupid)
		{
			echo '<p><b>'.lang('Error in group-creation !!!')."</b></p>\n";
			echo '<p>'.lang('click <a href="index.php">here</a> to return to setup.')."</p>\n";
			$GLOBALS['egw_setup']->db->transaction_abort();
			exit;
		}

		/* Group perms for the default group */
		$GLOBALS['egw_setup']->add_acl(array('addressbook','calendar','infolog','email','preferences','manual'),'run',$defaultgroupid);

		// give admin access to all apps, to save us some support requests
		$all_apps = array();
		$GLOBALS['egw_setup']->db->select($GLOBALS['egw_setup']->applications_table,'app_name','app_enabled < 3',__LINE__,__FILE__);
		while ($GLOBALS['egw_setup']->db->next_record())
		{
			$all_apps[] = $GLOBALS['egw_setup']->db->f('app_name');
		}
		$GLOBALS['egw_setup']->add_acl($all_apps,'run',$admingroupid);

		function insert_default_prefs($accountid)
		{
			$defaultprefs = array(
				'common' => array(
					'maxmatchs'     => 15,
					'template_set'  => 'idots',
					'theme'         => 'idots',
					'navbar_format' => 'icons',
					'tz_offset'     => 0,
					'dateformat'    => 'Y/m/d',
					'timeformat'    => '24',
					'lang'          => get_var('ConfigLang',Array('POST','COOKIE'),'en'),
					'default_app'   => 'calendar',
					'currency'      => '$',
					'show_help'     => True,
					'max_icons'		=> 12,
				),
				'calendar' => array(
					'workdaystarts' => 9,
					'workdayends'   => 17,
					'weekdaystarts' => 'Monday',
					'defaultcalendar' => 'day',
					'planner_start_with_group' => $GLOBALS['defaultgroupid'],
				),
			);

			foreach ($defaultprefs as $app => $prefs)
			{
				// only insert them, if they not already exist
				$GLOBALS['egw_setup']->db->select($GLOBALS['egw_setup']->prefs_table,'*',array(
					'preference_owner' => $accountid,
					'preference_app'   => $app,
				),__LINE__,__FILE__);
				if (!$GLOBALS['egw_setup']->db->next_record())
				{
					$GLOBALS['egw_setup']->db->insert($GLOBALS['egw_setup']->prefs_table,array(
						'preference_value' => serialize($prefs)
					),array(
						'preference_owner' => $accountid,
						'preference_app'   => $app,
					),__LINE__,__FILE__);
				}
			}
		}
		insert_default_prefs(-2);	// set some default prefs

		/* Creation of the demo accounts is optional - the checkbox is on by default. */
		if(get_var('create_demo',Array('POST')))
		{
			// Create 3 demo accounts
			$GLOBALS['egw_setup']->add_account('demo','Demo','Account','guest');
			$GLOBALS['egw_setup']->add_account('demo2','Demo2','Account','guest');
			$GLOBALS['egw_setup']->add_account('demo3','Demo3','Account','guest');
		}

		/* Create records for administrator account, with Admins as primary and Default as additional group */
		$accountid = $GLOBALS['egw_setup']->add_account($username,$fname,$lname,$passwd,'Admins',True);
		if (!$accountid)
		{
			echo '<p><b>'.lang('Error in admin-creation !!!')."</b></p>\n";
			echo '<p>'.lang('click <a href="index.php">here</a> to return to setup.')."</p>\n";
			$GLOBALS['egw_setup']->db->transaction_abort();
			exit;
		}
		$GLOBALS['egw_setup']->add_acl('phpgw_group',$admingroupid,$accountid);
		$GLOBALS['egw_setup']->add_acl('phpgw_group',$defaultgroupid,$accountid);

		$GLOBALS['egw_setup']->db->transaction_commit();

		Header('Location: index.php');
		exit;
	}
?>
