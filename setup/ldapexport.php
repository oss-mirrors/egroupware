<?php
  /**************************************************************************\
  * eGroupWare - Setup                                                       *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$GLOBALS['egw_info'] = array(
		'flags' => array(
			'noheader'   => True,
			'nonavbar'   => True,
			'currentapp' => 'home',
			'noapi'      => True
	));
	include('./inc/functions.inc.php');

	// Authorize the user to use setup app and load the database
	if (!$GLOBALS['egw_setup']->auth('Config'))
	{
		Header('Location: index.php');
		exit;
	}
	// Does not return unless user is authorized

	class egw
	{
		var $common;
		var $accounts;
		var $applications;
		var $db;
	}
	$phpgw = new egw;
	$egw->common = CreateObject('phpgwapi.common');

	$common = $egw->common;
	$GLOBALS['egw_setup']->loaddb();
	$egw->db = clone($GLOBALS['egw_setup']->db);

	$tpl_root = $GLOBALS['egw_setup']->html->setup_tpl_dir('setup');
	$setup_tpl = CreateObject('setup.Template',$tpl_root);
	$setup_tpl->set_file(array(
		'ldap'   => 'ldap.tpl',
		'T_head' => 'head.tpl',
		'T_footer' => 'footer.tpl',
		'T_alert_msg' => 'msg_alert_msg.tpl'
	));

	$GLOBALS['egw_setup']->db->select($GLOBALS['egw_setup']->config_table,'config_name,config_value',array(
		"config_name LIKE 'ldap%'",
	),__LINE__,__FILE__);
	while ($GLOBALS['egw_setup']->db->next_record())
	{
		$GLOBALS['egw_info']['server'][$GLOBALS['egw_setup']->db->f('config_name')] = $GLOBALS['egw_setup']->db->f('config_value');
	}
	$GLOBALS['egw_info']['server']['account_repository'] = 'ldap';

	$egw->accounts     = CreateObject('phpgwapi.accounts');
	$acct              = $egw->accounts;

	// First, see if we can connect to the LDAP server, if not send `em back to config.php with an
	// error message.

	// connect to ldap server
	if(!$ldap = $common->ldapConnect())
	{
		$noldapconnection = True;
	}

	if($noldapconnection)
	{
		Header('Location: config.php?error=badldapconnection');
		exit;
	}

	$sql = "SELECT * FROM ".$GLOBALS['egw_setup']->accounts_table." WHERE account_type='u'";
	$GLOBALS['egw_setup']->db->query($sql,__LINE__,__FILE__);
	while($GLOBALS['egw_setup']->db->next_record())
	{
		$i = $GLOBALS['egw_setup']->db->f('account_id');
		$account_info[$i]['account_id']        = $GLOBALS['egw_setup']->db->f('account_id');
		$account_info[$i]['account_lid']       = $GLOBALS['egw_setup']->db->f('account_lid');
		$account_info[$i]['account_firstname'] = $GLOBALS['egw_setup']->db->f('account_firstname');
		$account_info[$i]['account_lastname']  = $GLOBALS['egw_setup']->db->f('account_lastname');
		$account_info[$i]['account_status']    = $GLOBALS['egw_setup']->db->f('account_status');
		$account_info[$i]['account_expires']   = $GLOBALS['egw_setup']->db->f('account_expires');
		$account_info[$i]['account_primary_group'] = $GLOBALS['egw_setup']->db->f('account_primary_group');
	}
	
	$sql = "SELECT * FROM ".$GLOBALS['egw_setup']->accounts_table." WHERE account_type='g'";
	$GLOBALS['egw_setup']->db->query($sql,__LINE__,__FILE__);
	while($GLOBALS['egw_setup']->db->next_record())
	{
		$i = $GLOBALS['egw_setup']->db->f('account_id');
		$group_info[$i]['account_id']        = $GLOBALS['egw_setup']->db->f('account_id');
		$group_info[$i]['account_lid']       = $GLOBALS['egw_setup']->db->f('account_lid');
		$group_info[$i]['account_firstname'] = $GLOBALS['egw_setup']->db->f('account_firstname');
		$group_info[$i]['account_lastname']  = $GLOBALS['egw_setup']->db->f('account_lastname');
		$group_info[$i]['account_status']    = $GLOBALS['egw_setup']->db->f('account_status');
		$group_info[$i]['account_expires']   = $GLOBALS['egw_setup']->db->f('account_expires');
	}

	$cancel = get_var('cancel','POST');
	$submit = get_var('submit','POST');
	$users  = get_var('users','POST');
	$admins = get_var('admins','POST');
	$s_apps = get_var('s_apps','POST');
	$ldapgroups = get_var('ldapgroups','POST');

	if($cancel)
	{
		Header('Location: ldap.php');
		exit;
	}

	if($submit)
	{
		if($ldapgroups)
		{
			while(list($key,$groupid) = each($ldapgroups))
			{
				$id_exist = 0;
				$thisacctid    = $group_info[$groupid]['account_id'];
				$thisacctlid   = $group_info[$groupid]['account_lid'];
				$thisfirstname = $group_info[$groupid]['account_firstname'];
				$thislastname  = $group_info[$groupid]['account_lastname'];
				$thismembers   = $group_info[$groupid]['members'];

				// Do some checks before we try to import the data to LDAP.
				if(!empty($thisacctid) && !empty($thisacctlid))
				{
					$groups = CreateObject('phpgwapi.accounts',(int)$thisacctid);

					// Check if the account is already there.
					// If so, we won't try to create it again.
					$acct_exist = $acct->name2id($thisacctlid);
					if($acct_exist)
					{
						$thisacctid = $acct_exist;
					}
					$id_exist = $groups->exists((int)$thisacctid);
					
					echo '<br />accountid: ' . $thisacctid;
					echo '<br />accountlid: ' . $thisacctlid;
					echo '<br />exists: ' . $id_exist;
					
					/* If not, create it now. */
					if(!$id_exist)
					{
						$thisaccount_info = array(
							'account_type'      => 'g',
							'account_id'        => $thisacctid,
							'account_lid'       => $thisacctlid,
							'account_passwd'    => 'x',
							'account_firstname' => $thisfirstname,
							'account_lastname'  => $thislastname,
							'account_status'    => 'A',
							'account_expires'   => -1,
						);
						$groups->create($thisaccount_info);
					}
				}
			}
		}

		if($users)
		{
			while(list($key,$accountid) = each($users))
			{
				$id_exist = 0; $acct_exist = 0;
				$thisacctid    = $account_info[$accountid]['account_id'];
				$thisacctlid   = $account_info[$accountid]['account_lid'];
				$thisfirstname = $account_info[$accountid]['account_firstname'];
				$thislastname  = $account_info[$accountid]['account_lastname'];
				$thisprimarygroup = $account_info[$accountid]['account_primary_group'];

				// Do some checks before we try to import the data.
				if(!empty($thisacctid) && !empty($thisacctlid))
				{
					$accounts = CreateObject('phpgwapi.accounts',(int)$thisacctid);

					// Check if the account is already there.
					// If so, we won't try to create it again.
					$acct_exist = $acct->name2id($thisacctlid);
					if($acct_exist)
					{
						$thisacctid = $acct_exist;
					}
					$id_exist = $accounts->exists((int)$thisacctid);
					// If not, create it now.
					if(!$id_exist)
					{
						echo '<br />Adding' . $thisacctid;
						$thisaccount_info = array(
							'account_type'      => 'u',
							'account_id'        => $thisacctid,
							'account_lid'       => $thisacctlid,
							'account_passwd'    => 'x',
							'account_firstname' => $thisfirstname,
							'account_lastname'  => $thislastname,
							'account_status'    => 'A',
							'account_expires'   => -1,
							'homedirectory'     => $GLOBALS['egw_info']['server']['ldap_account_home'] . '/' . $thisacctlid,
							'loginshell'        => $GLOBALS['egw_info']['server']['ldap_account_shell'],
							'account_primary_group' => $thisprimarygroup,
						);
						$accounts->create($thisaccount_info);
					}
				}
			}
		}
		$setup_complete = True;
	}

	$GLOBALS['egw_setup']->html->show_header(lang('LDAP Export'),False,'config',$GLOBALS['egw_setup']->ConfigDomain . '(' . $GLOBALS['egw_domain'][$GLOBALS['egw_setup']->ConfigDomain]['db_type'] . ')');

	if($error)
	{
		//echo '<br /><center><b>Error:</b> '.$error.'</center>';
		$GLOBALS['egw_setup']->html->show_alert_msg('Error',$error);
	}

	if($setup_complete)
	{
		echo '<br /><center>'.lang('Export has been completed!  You will need to set the user passwords manually.').'</center>';
		echo '<br /><center>'.lang('Click <a href="index.php">here</a> to return to setup.').'</center>';
		$GLOBALS['egw_setup']->html->show_footer();
		exit;
	}

	$setup_tpl->set_block('ldap','header','header');
	$setup_tpl->set_block('ldap','user_list','user_list');
	$setup_tpl->set_block('ldap','admin_list','admin_list');
	$setup_tpl->set_block('ldap','group_list','group_list');
	$setup_tpl->set_block('ldap','app_list','app_list');
	$setup_tpl->set_block('ldap','submit','submit');
	$setup_tpl->set_block('ldap','footer','footer');

	while(list($key,$account) = @each($account_info))
	{
		$user_list .= '<option value="' . $account['account_id'] . '">'
			. $common->display_fullname($account['account_lid'],$account['account_firstname'],$account['account_lastname'])
			. '</option>';
	}

	@reset($account_info);
	while(list($key,$account) = @each($account_info))
	{
		$admin_list .= '<option value="' . $account['account_id'] . '">'
			. $common->display_fullname($account['account_lid'],$account['account_firstname'],$account['account_lastname'])
			. '</option>';
	}

	while(list($key,$group) = @each($group_info))
	{
		$group_list .= '<option value="' . $group['account_id'] . '">'
			. $group['account_lid']
			. '</option>';
	}

	$setup_tpl->set_var('action_url','ldapexport.php');
	$setup_tpl->set_var('users',$user_list);
	$setup_tpl->set_var('admins',$admin_list);
	$setup_tpl->set_var('ldapgroups',$group_list);
	$setup_tpl->set_var('s_apps',$app_list);

	$setup_tpl->set_var('ldap_import',lang('LDAP export users'));
	$setup_tpl->set_var('description',lang("This section will help you export users and groups from eGroupWare's account tables into your LDAP tree").'.');
	$setup_tpl->set_var('select_users',lang('Select which user(s) will be exported'));
	$setup_tpl->set_var('select_groups',lang('Select which group(s) will be exported (group membership will be maintained)'));
	$setup_tpl->set_var('form_submit','export');
	$setup_tpl->set_var('cancel',lang('Cancel'));

	$setup_tpl->pfp('out','header');
	if($account_info)
	{
		$setup_tpl->pfp('out','user_list');
	}
	if($group_info)
	{
		$setup_tpl->pfp('out','group_list');
	}
	$setup_tpl->pfp('out','submit');
	$setup_tpl->pfp('out','footer');

	$GLOBALS['egw_setup']->html->show_footer();
?>
