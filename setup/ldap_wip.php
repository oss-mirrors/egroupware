<?php
  /**************************************************************************\
  * phpGroupWare - Setup                                                     *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$phpgw_info["flags"] = array(
		'noheader'   => True,
		'nonavbar'   => True,
		'currentapp' => 'home',
		'noapi'      => True
	);

	include('../header.inc.php');
	include('./inc/functions.inc.php');

	// Authorize the user to use setup app and load the database
	if (!$phpgw_setup->auth('Config'))
	{
		Header('Location: index.php');
		exit;
	}
	// Does not return unless user is authorized
	class phpgw
	{
		var $common;
		var $accounts;
		var $applications;
		var $db;
	}
	$phpgw = new phpgw;
	$phpgw->common = CreateObject('phpgwapi.common');

	$common              = $phpgw->common;
	$phpgw_setup->loaddb();
	$phpgw->db           = $phpgw_setup->db;

	$phpgw_info['server']['auth_type'] = 'ldap';
	$phpgw->accounts     = CreateObject('phpgwapi.accounts');
	$acct                = $phpgw->accounts;
	$phpgw->applications = CreateObject('phpgwapi.applications');
	$applications        = $phpgw->applications;

	$phpgw_setup->db->query("select config_name,config_value from phpgw_config where config_name like 'ldap%'",__LINE__,__FILE__);
	while ($phpgw_setup->db->next_record())
	{
		$config[$phpgw_setup->db->f('config_name')] = $phpgw_setup->db->f('config_value');
	}
	$phpgw_info['server']['ldap_host']          = $config['ldap_host'];
	$phpgw_info['server']['ldap_context']       = $config['ldap_context'];
	$phpgw_info['server']['ldap_group_context'] = $config['ldap_group_context'];
	$phpgw_info['server']['ldap_root_dn']       = $config['ldap_root_dn'];
	$phpgw_info['server']['ldap_root_pw']       = $config['ldap_root_pw'];

	// First, see if we can connect to the LDAP server, if not send `em back to config.php with an
	// error message.

	// connect to ldap server
	if (! $ldap = $common->ldapConnect())
	{
		$noldapconnection = True;
	}

	if ($noldapconnection)
	{
		Header('Location: config.php?error=badldapconnection');
		exit;
	}

	$sr = ldap_search($ldap,$config['ldap_context'],'(|(uid=*))',array('sn','givenname','uid','uidnumber'));
	$info = ldap_get_entries($ldap, $sr);
  
	for ($i=0; $i<$info['count']; $i++)
	{
		if (! $phpgw_info['server']['global_denied_users'][$info[$i]['uid'][0]])
		{
			$account_info[$i]['account_id']        = $info[$i]['uidnumber'][0];
			$account_info[$i]['account_lid']       = $info[$i]['uid'][0];
			$account_info[$i]['account_firstname'] = $info[$i]['givenname'][0];
			$account_info[$i]['account_lastname']  = $info[$i]['sn'][0];
		}
	}

	if ($phpgw_info['server']['ldap_group_context'])
	{
		$srg = ldap_search($ldap,$config['ldap_group_context'],'(|(cn=*))',array('gidnumber','cn','memberuid'));
		$info = ldap_get_entries($ldap, $srg);
  
		for ($i=0; $i<$info['count']; $i++)
		{
			if (! $phpgw_info['server']['global_excluded_groups'][$info[$i]['cn'][0]] &&
				! $account_info[$i][$info[$i]['cn'][0]])
			{
				$group_info[$i]['account_id']        = $info[$i]['gidnumber'][0];
				$group_info[$i]['account_lid']       = $info[$i]['cn'][0];
				$group_info[$i]['members']           = $info[$i]['memberuid'];
				$group_info[$i]['account_firstname'] = $info[$i]['cn'][0];
				$group_info[$i]['account_lastname']  = '';
			}
		}
	}

	$phpgw_setup->db->query("select app_name,app_title from phpgw_applications where app_enabled != '0' and "
		. "app_name != 'administration'",__LINE__,__FILE__);
	while ($phpgw_setup->db->next_record()) {
		$apps[$phpgw_setup->db->f('app_name')] = $phpgw_setup->db->f('app_title');
	}

	if ($submit) {
		if (!count($admins)) {
			$error = '<br>You must select at least 1 admin';
		}

		if (!count($s_apps)) {
			$error .= '<br>You must select at least 1 application';
		}

		if (!$error) {
			if ($ldapgroups)
			{
				$groupimport = True;
				while ($group = each($group_info))
				{
					$id_exist = 0;
					$thisacctid    = $group[1]['account_id'];
					$thisacctlid   = $group[1]['account_lid'];
					$thisfirstname = $group[1]['account_firstname'];
					$thislastname  = $group[1]['account_lastname'];
					$thismembers   = $group_info[$i]['members'];

					// Do some checks before we try to import the data.
					if (!empty($thisacctid) && !empty($thisacctlid))
					{
						$groups = CreateObject('phpgwapi.accounts',intval($thisacctid));
						$groups->db = $phpgw_setup->db;
	
						// Check if the account is already there.
						// If so, we won't try to create it again.
						$acct_exist = $acct->name2id($thisacctlid);
						if ($acct_exist)
						{
							$thisacctid = $acct_exist;
						}
						$id_exist = $accounts->exists(intval($thisacctid));
						// If not, create it now.
						if(!$id_exist)
						{
							$accounts->create('g', $thisacctlid, 'x',$thisfirstname, $thislastname,'',$thisacctid);
						}

						// Now make them a member of this group in phpgw.
						while (list($members = each($thismembers))
						{
							// Insert acls for this group based on memberuid field.
							// Since the group has app rights, we don't need to give users
							//  these rights.  Instead, we maintain group membership here.
							$acl = CreateObject('phpgwapi.acl',intval($members));
							$acl->db = $phpgw_setup->db;
							$acl->read_repository();

							$acl->delete('phpgw_group',$thisacctid,1);
							$acl->add('phpgw_group',$thisacctid,1);

							// Now add the acl to let them change their password
							$acl->delete('preferences','changepassword',$thisacctid,1);
							$acl->add('preferences','changepassword',$thisacctid,1);

							$acl->save_repository();
						}
					}
				}
				$setup_complete = True;
			}
			else
			{
				// Create the 'Default' group
				mt_srand((double)microtime()*1000000);
				$defaultgroupid = mt_rand (100, 65535);

				$acct = CreateObject('phpgwapi.accounts',$defaultgroupid);
				$acct->db = $phpgw_setup->db;

				// Check if the group account is already there.
				// If so, set our group_id to that account's id for use below.
				$acct_exist = $acct->name2id('Default');
				if ($acct_exist) {
					$defaultgroupid = $acct_exist;
				}
				$id_exist   = $acct->exists(intval($defaultgroupid));
				// if not, create it, using our original groupid.
				if(!$id_exist) {
					$acct->create('g','Default',$passwd,'Default','Group','A',$defaultgroupid);
				} else {
					// Delete first, so ldap does not return an error, then recreate
					$acct->delete($defaultgroupid);
					$acct->create('g','Default',$passwd,'Default','Group','A',$defaultgroupid);
				}

				$acl = CreateObject('phpgwapi.acl',$defaultgroupid);
				$acl->db = $phpgw_setup->db;
				$acl->read_repository();
				while ($app = each($s_apps)) {
					$acl->delete($app[1],'run',1);
					$acl->add($app[1],'run',1);
				}
				$acl->save_repository();
			} //end default group creation

			while ($account = each($account_info))
			{
				$id_exist = 0;
				$thisacctid  = $account[1]['account_id'];
				$thisacctlid = $account[1]['account_lid'];
				$thisfirstname = $account[1]['account_firstname'];
				$thislastname  = $account[1]['account_lastname'];

				// Do some checks before we try to import the data.
				if (!empty($thisacctid) && !empty($thisacctlid))
				{
					$accounts = CreateObject('phpgwapi.accounts',intval($thisacctid));
					$accounts->db = $phpgw_setup->db;

					// Check if the account is already there.
					// If so, we won't try to create it again.
					$acct_exist = $acct->name2id($thisacctlid);
					if ($acct_exist)
					{
						$thisacctid = $acct_exist;
					}
					$id_exist = $accounts->exists(intval($thisacctid));
					// If not, create it now.
					if(!$id_exist)
					{
						$accounts->create('u', $thisacctlid, 'x',$thisfirstname, $thislastname,'A',$thisacctid);
					}

					// Insert default acls for this user.
					// Since the group has app rights, we don't need to give users
					//  these rights.  Instead, we make the user a member of the Default group
					//  below.
					$acl = CreateObject('phpgwapi.acl',intval($thisacctid));
					$acl->db = $phpgw_setup->db;
					$acl->read_repository();

					// Only give them admin if we asked for them to have it.
					// This is typically an exception to apps for run rights
					//  as a group member.
					for ($a=0;$a<count($admins);$a++)
					{
						if ($admins[$a] == $thisacctid)
						{
							$acl->delete('admin','run',1);
							$acl->add('admin','run',1);
						}
					}

					// Now make them a member of the 'Default' group.
					// But, only if the current user is not the group itself.
					if ($defaultgroupid != $thisacctid)
					{
						$acl->delete('phpgw_group',$defaultgroupid,1);
						$acl->add('phpgw_group',$defaultgroupid,1);
					}

					// Save these new acls.
					$acl->save_repository();
				}
				$setup_complete = True;
			}
		}
	}

	// Add a check to see if there are no users in LDAP, if not create a default user.

	$phpgw_setup->show_header();
  
	if ($error) {
		echo '<br><center><b>Error:</b> '.$error.'</center>';
	}

	if ($setup_complete) {
		$phpgw_setup->db->query("select config_value from phpgw_config where config_name='webserver_url'",__LINE__,__FILE__);
		$phpgw_setup->db->next_record();
		echo '<br><center>Setup has been completed!  Click <a href="' . $phpgw_setup->db->f("config_value")
			. '/login.php">here</a> to login</center>';
		exit;
	}
?>

 <form action="ldap.php" method="POST">
  <table border="0" align="center" width="70%">
   <tr bgcolor="486591">
    <td colspan="2">&nbsp;<font color="fefefe">LDAP import users</font></td>
   </tr>
   <tr bgcolor="e6e6e6">
    <td colspan="2">&nbsp;This section will help you import users and groups from your LDAP tree into phpGroupWare's account tables.<br>&nbsp;</td>
   </tr>

   <tr bgcolor="e6e6e6">
    <td align="left" valign="top">
     &nbsp;Select which user(s) will be imported
    </td>
    <td align="center">
     <select name="users[]" multiple size="5">
      <?php
	while ($account = each($account_info))
	{
		echo '<option value="' . $account[1]['account_id'] . '">'
			. $common->display_fullname($account[1]['account_lid'],$account[1]['account_firstname'],$account[1]['account_lastname'])
			. '</option>';
		echo "\n";
	}
      ?>
     </select>
    </td>
   </tr>

   <tr bgcolor="e6e6e6">
    <td align="left" valign="top">
     &nbsp;Select which user(s) will have admin privileges
    </td>
    <td align="center">
     <select name="admins[]" multiple size="5">
      <?php
	@reset($account_info);
	while ($account = each($account_info))
	{
		echo '<option value="' . $account[1]['account_id'] . '">'
			. $common->display_fullname($account[1]['account_lid'],$account[1]['account_firstname'],$account[1]['account_lastname'])
			. '</option>';
		echo "\n";
	}
      ?>
     </select>
    </td>
   </tr>

   <tr bgcolor="e6e6e6">
    <td align="left" valign="top">
     &nbsp;Select which group(s) will be imported (group membership will be maintained)
    </td>
    <td align="center">
     <select name="ldapgroups[]" multiple size="5">
      <?php
	while ($group = each($group_info))
	{
		echo '<option value="' . $account[1]['account_id'] . '">'
			. $group[1]['account_lid']
			. '</option>';
		echo "\n";
	}
      ?>
     </select>
    </td>
   </tr>

   <tr bgcolor="e6e6e6">
    <td align="left" valign="top">
     &nbsp;Select the default applications your users will have access to.
     <br>&nbsp;Note: You will be able to customize this later.
    </td>
    <td>
     <select name="s_apps[]" multiple size="5">
      <?php
	while ($app = each($apps))
	{
		if ($app[0] != 'admin')
		{
			echo '<option value="' . $app[0] . '" selected>' . $app[1] . '</option>';
		}
		else
		{
			echo '<option value="' . $app[0] . '">' . $app[1] . '</option>';
		}
		echo "\n";
	}
      ?>
     </select>
    </td>
   </tr>

   <tr bgcolor="e6e6e6">
    <td colspan="2" align="center">
     <input type="submit" name="submit" value="import">
    </td>
   </tr> 
    
  </table>
 </form>
