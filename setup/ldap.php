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
		'noheader'	=> True,
		'nonavbar'	=> True,
		'currentapp'	=> 'home',
		'noapi'		=> True
	);

	include('../header.inc.php');
	include('./inc/functions.inc.php');

	// Authorize the user to use setup app and load the database
	if (!$phpgw_setup->auth('Config')){
		Header('Location: index.php');
		exit;
	}
	// Does not return unless user is authorized
	class phpgw {
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
	while ($phpgw_setup->db->next_record()) {
		$config[$phpgw_setup->db->f('config_name')] = $phpgw_setup->db->f('config_value');
	}
	$phpgw_info['server']['ldap_host']    = $config['ldap_host'];
	$phpgw_info['server']['ldap_context'] = $config['ldap_context'];
	$phpgw_info['server']['ldap_root_dn'] = $config['ldap_root_dn'];
	$phpgw_info['server']['ldap_root_pw'] = $config['ldap_root_pw'];

	// First, see if we can connect to the LDAP server, if not send `em back to config.php with an
	// error message.

	// connect to ldap server
	if (! $ldap = $common->ldapConnect()) {
		$noldapconnection = True;
	}

	if ($noldapconnection) {
		Header('Location: config.php?error=badldapconnection');
		exit;
	}

	$sr = ldap_search($ldap,$config['ldap_context'],'(|(uid=*))',array('sn','givenname','uid','uidnumber','gidnumber'));
	$info = ldap_get_entries($ldap, $sr);
  
	for ($i=0; $i<$info['count']; $i++) {
		if (! $phpgw_info['server']['global_denied_users'][$info[$i]['uid'][0]]) {
			$account_info[$i]['account_id']        = $info[$i]['uidnumber'][0];
			$account_info[$i]['account_lid']       = $info[$i]['uid'][0];
			$account_info[$i]['account_firstname']  = $info[$i]['givenname'][0];
			$account_info[$i]['account_lastname'] = $info[$i]['sn'][0];
			$account_info[$i]['gidnumber'] = $info[$i]['gidnumber'][0];
		}
	}

	$phpgw_setup->db->query("select app_name,app_title from phpgw_applications where app_enabled != '0' and "
		. "app_name != 'administration'",__LINE__,__FILE__);
	while ($phpgw_setup->db->next_record()) {
		$apps[$phpgw_setup->db->f('app_name')] = $phpgw_setup->db->f('app_title');
	}

	if ($submit) {
		if (! count($admins)) {
			$error = '<br>You must select at least 1 admin';
		}

		if (! count($s_apps)) {
			$error .= '<br>You must select at least 1 application';
		}

		if (! $error) {
			// Create the 'Default' group
			mt_srand((double)microtime()*1000000);
			$defaultgroupid = mt_rand (100, 65535);

			$acct = CreateObject('phpgwapi.accounts',$defaultgroupid);
			$acct->db = $phpgw_setup->db;

			// Check if the group account is already there.
			// If so, set our group_id to that account's id for use below.
			$acct_exist = $acct->name2id('Default');
			if ($acct_exist)
			{
				$defaultgroupid = $acct_exist;
			}
			$id_exist   = $acct->exists(intval($defaultgroupid));
			// if not, create it, using our original groupid.
			$thisgroup_info = array(
				'account_type'      => 'g',
				'account_id'        => $defaultgroupid,
				'account_lid'       => 'Default',
				'account_passwd'    => $passwd,
				'account_firstname' => 'Default',
				'account_lastname'  => 'Group',
				'account_status'    => 'A',
				'account_expires'   => -1
			);
			if(!$id_exist)
			{
				$acct->create($thisgroup_info);
			}
			else
			{
				// Delete first, so ldap does not return an error, then recreate
				$acct->delete($defaultgroupid);
				$acct->create($thisgroup_info);
			}

			$acl = CreateObject('phpgwapi.acl',$defaultgroupid);
			$acl->db = $phpgw_setup->db;
			$acl->read_repository();
			while ($app = each($s_apps))
			{
				$acl->delete($app[1],'run',1);
				$acl->add($app[1],'run',1);
			}
			$acl->save_repository();

			while (list($nul,$account) = each($account_info))
			{
				$id_exist = 0;
				$thisacctid  = $account['account_id'];
				$thisacctlid = $account['account_lid'];

				// Do some checks before we try to import the data.
				if (!empty($thisacctid) && !empty($thisacctlid))
				{
					$accounts = CreateObject('phpgwapi.accounts',intval($thisacctid));
					$accounts->db = $phpgw_setup->db;

					// Check if the account is already there.
					// If so, we won't try to create it again.
					$acct_exist = $acct->name2id($thisacctlid);	// name2id checks only SQL
					if ($acct_exist)										// this gives the SQL account_id a preference over LDAP uidnummber
					{															// this could be fatal if one already has an user with account_lid == uid
						// $thisacctid = $acct_exist;					// and uses the LDAP uidnumber for an unix account
						if ($acct_exist != $thisacctid)
							echo "<p>WARNING: user '$thisacctlid'=$thisacctid already exist in SQL under the account_id=$acct_exist</p>";
					}
					$id_exist = $accounts->exists(intval($thisacctid));
					// If the account does not exist in _both_ (== returnvalue < 3) create it
					if($id_exist < 3)
					{
						$thisaccount_info = array(
							'account_type'      => 'u',
							'account_id'		  => $thisacctid,
							'account_lid'       => $thisacctlid,
							'account_passwd'    => 'x',
							'account_firstname' => $account['account_firstname'],
							'account_lastname'  => $account['account_lastname'],
							'account_status'    => 'A',
							'account_expires'   => -1
						);
						$accounts->create($thisaccount_info);
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

					// Check if user has a group assigned in LDAP and this group exists (in SQL)
					// --> make him member of this group instead of the 'Default' group
					if (($gid = $account['gidnumber']) && ($gname = $acct->id2name($gid))) 
					{
						// echo "<p>putting '$thisacctlid' in Group gid=$gid</p>\n";

						$acl->delete('phpgw_group',$gid,1);
						$acl->add('phpgw_group',$gid,1);
					} else 					
					
					// Now make them a member of the 'Default' group.
					// But, only if the current user is not the group itself.
					if ($defaultgroupid != $thisacctid)
					{
						$acl->delete('phpgw_group',$defaultgroupid,1);
						$acl->add('phpgw_group',$defaultgroupid,1);
					}

					// Now add the acl to let them change their password
					$acl->delete('preferences','changepassword',$thisacctid,1);
					$acl->add('preferences','changepassword',$thisacctid,1);

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
    <td colspan="2">&nbsp;This section will help you import users from your LDAP tree into phpGroupWare's account tables.<br>&nbsp;</td>
   </tr>

   <tr bgcolor="e6e6e6">
    <td align="left" valign="top">
     &nbsp;Select which user(s) will have the admin privileges
    </td>
    <td align="center">
     <select name="admins[]" multiple size="5">
      <?php
        while ($account = each($account_info)) {
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
     &nbsp;Select the default applications your users will have access to.
     <br>&nbsp;Note: You will be able to customize this later.
    </td>
    <td>
     <select name="s_apps[]" multiple size="5">
      <?php
        while ($app = each($apps)) {
          echo '<option value="' . $app[0] . '" selected>' . $app[1] . '</option>';
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
