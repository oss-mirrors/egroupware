<?php
/**
 * Setup - create admin account
 *
 * @link http://www.egroupware.org
 * @package setup
 * @author Miles Lott <milos@groupwhere.org>
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

	if (strpos($_SERVER['PHP_SELF'],'setup-cli.php') === false)
	{
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
		$GLOBALS['egw_setup']->loaddb(true);
	}
	$error = '';
	if ($_POST['submit'])
	{
		/* Posted admin data */
		$passwd   = get_var('passwd',Array('POST'));
		$passwd2  = get_var('passwd2',Array('POST'));
		$username = get_var('username',Array('POST'));
		$fname    = get_var('fname',Array('POST'));
		$lname    = get_var('lname',Array('POST'));
		$email    = get_var('email',Array('POST'));
	
		if($passwd != $passwd2 || !$username)
		{
			$error = '<p>'.lang('Passwords did not match, please re-enter') . ".</p>\n";
		}
		if(!$username)
		{
			$error = '<p>'.lang('You must enter a username for the admin') . ".</p>\n";
		}
	}
	if(!$_POST['submit'] || $error)
	{
		$tpl_root = $GLOBALS['egw_setup']->html->setup_tpl_dir('setup');
		$setup_tpl = CreateObject('setup.Template',$tpl_root);
		$setup_tpl->set_file(array(
			'T_head'       => 'head.tpl',
			'T_footer'     => 'footer.tpl',
			'T_alert_msg'  => 'msg_alert_msg.tpl',
			'T_login_main' => 'login_main.tpl',
			'T_login_stage_header' => 'login_stage_header.tpl',
			'T_admin_account' => 'admin_account.tpl'
		));
		$setup_tpl->set_block('T_login_stage_header','B_multi_domain','V_multi_domain');
		$setup_tpl->set_block('T_login_stage_header','B_single_domain','V_single_domain');

		$GLOBALS['egw_setup']->html->show_header(lang('Create admin account'));

		$setup_tpl->set_var(array(
			'error'    => $error,
			'username' => $username,
			'fname'    => $fname,
			'lname'    => $lname,
			'email'    => $email,
		));
		$setup_tpl->set_var('action_url','admin_account.php');
		$setup_tpl->set_var('description',lang('This will create a first user in eGroupWare or reset password and admin rights of an exiting user'));
		$setup_tpl->set_var('lang_deleteall',lang('Delete all existing SQL accounts, groups, ACLs and preferences (normally not necessary)?'));

		$setup_tpl->set_var('detailadmin',lang('Details for Admin account'));
		$setup_tpl->set_var('adminusername',lang('Admin username'));
		$setup_tpl->set_var('adminfirstname',lang('Admin first name'));
		$setup_tpl->set_var('adminlastname',lang('Admin last name'));
		$setup_tpl->set_var('adminemail',lang('Admin email address'));
		$setup_tpl->set_var('adminpassword',lang('Admin password'));		
		$setup_tpl->set_var('adminpassword2',lang('Re-enter password'));
		$setup_tpl->set_var('admin_all_apps',lang('Give admin access to all installed apps'));
		$setup_tpl->set_var('all_apps_desc',lang('Usually more annoying.<br />Admins can use Admin >> Manage accounts or groups to give access to further apps.'));
		$setup_tpl->set_var('create_demo_accounts',lang('Create demo accounts'));
		$setup_tpl->set_var('demo_desc',lang('The username/passwords are: demo/guest, demo2/guest and demo3/guest.'));

		$setup_tpl->set_var('lang_submit',lang('Save'));
		$setup_tpl->set_var('lang_cancel',lang('Cancel'));
		$setup_tpl->pparse('out','T_admin_account');
		$GLOBALS['egw_setup']->html->show_footer();
	}
	else
	{
		/* Begin transaction for acl, etc */
		$GLOBALS['egw_setup']->db->transaction_begin();

		if($_POST['delete_all'])
		{
			/* Now, clear out existing tables */
			foreach(array($GLOBALS['egw_setup']->accounts_table,$GLOBALS['egw_setup']->prefs_table,$GLOBALS['egw_setup']->acl_table,'egw_access_log') as $table)
			{
				$GLOBALS['egw_setup']->db->delete($table,'1=1',__LINE__,__FILE__);
			}
		}
		/* Create the demo groups */
		$defaultgroupid = (int)$GLOBALS['egw_setup']->add_account('Default','Default','Group',False,False);
		$admingroupid   = (int)$GLOBALS['egw_setup']->add_account('Admins','Admin','Group',False,False);
		
		if (!$defaultgroupid || !$admingroupid)
		{
			if (strpos($_SERVER['PHP_SELF'],'setup-cli.php') !== false)
			{
				return 42; //lang('Error in group-creation !!!');	// dont exit on setup-cli
			}
			echo '<p><b>'.lang('Error in group-creation !!!')."</b></p>\n";
			echo '<p>'.lang('click <a href="index.php">here</a> to return to setup.')."</p>\n";
			$GLOBALS['egw_setup']->db->transaction_abort();
			exit;
		}

		// Group perms for the default group
		$GLOBALS['egw_setup']->add_acl(array('addressbook','calendar','infolog','felamimail','preferences','home','manual'),'run',$defaultgroupid);

		$apps = array();
		$GLOBALS['egw_setup']->db->select($GLOBALS['egw_setup']->applications_table,'app_name','app_enabled < 3',__LINE__,__FILE__);
		while ($GLOBALS['egw_setup']->db->next_record())
		{
			$apps[] = $GLOBALS['egw_setup']->db->f('app_name');
		}
		// if not otherwise selected, give admin only access to the rest of the default apps, 
		// not yet set for the default group or development only apps like (etemplate, jinn, tt's)
		if (!$_POST['admin_all_apps'])	
		{
			$apps = array_intersect(array('admin','emailadmin','filemanager','mydms','news_admin','phpbrain','phpsysinfo','polls','projectmanager','resources','sambaadmin','sitemgr','timesheet','wiki'),$apps);
		}
		$GLOBALS['egw_setup']->add_acl($apps,'run',$admingroupid);

		// give admin access to default apps, not yet set for the default group
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
		insert_default_prefs(-2);	// -2 = default prefs

		/* Creation of the demo accounts is optional - the checkbox is on by default. */
		if(get_var('create_demo',Array('POST')))
		{
			// Create 3 demo accounts
			$GLOBALS['egw_setup']->add_account('demo','Demo','Account','guest');
			$GLOBALS['egw_setup']->add_account('demo2','Demo2','Account','guest');
			$GLOBALS['egw_setup']->add_account('demo3','Demo3','Account','guest');
		}

		/* Create records for administrator account, with Admins as primary and Default as additional group */
		$accountid = $GLOBALS['egw_setup']->add_account($username,$fname,$lname,$passwd,'Admins',True,$email);
		if (!$accountid)
		{
			if (strpos($_SERVER['PHP_SELF'],'setup-cli.php') !== false)
			{
				return 41; //lang('Error in admin-creation !!!');	// dont exit on setup-cli
			}
			echo '<p><b>'.lang('Error in admin-creation !!!')."</b></p>\n";
			echo '<p>'.lang('click <a href="index.php">here</a> to return to setup.')."</p>\n";
			$GLOBALS['egw_setup']->db->transaction_abort();
			exit;
		}
		$GLOBALS['egw_setup']->set_memberships(array($admingroupid,$defaultgroupid),$accountid);

		$GLOBALS['egw_setup']->db->transaction_commit();

		if (strpos($_SERVER['PHP_SELF'],'setup-cli.php') === false)
		{
			Header('Location: index.php');
		}
	}
