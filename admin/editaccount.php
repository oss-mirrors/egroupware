<?php
  /**************************************************************************\
  * phpGroupWare - administration                                            *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  $phpgw_info = array();
  $phpgw_info["flags"] = array("noheader" => True, 
			       "nonavbar" => True, 
			       "currentapp" => "admin",
			       "parent_page" => "accounts.php");
  include("../header.inc.php");
  include($phpgw_info["server"]["app_inc"]."/accounts_".$phpgw_info["server"]["account_repository"].".inc.php");

  if (! $account_id) {
     Header("Location: " . $phpgw->link("accounts.php"));
  }

  if ($submit) {
     $totalerrors = 0;

     if ($phpgw_info["server"]["account_repository"] == "ldap" && ! $allow_long_loginids) {
        if (strlen($n_loginid) > 8) {
           $error[$totalerrors++] = lang("The loginid can not be more then 8 characters");
        }
     }
    
     if ($old_loginid != $n_loginid) {
        if (account_exsists($n_loginid)) {
           $error[$totalerrors++] = lang("That loginid has already been taken");
        }
//        $c_loginid = $n_loginid;
//        $n_loginid = $old_loginid;
     }
  
     if ($n_passwd || $n_passwd_2) {
        if ($n_passwd != $n_passwd_2) {
           $error[$totalerrors++] = lang("The two passwords are not the same");
        }
        if (! $n_passwd){
           $error[$totalerrors++] = lang("You must enter a password");
        }
     }

     if (count($new_permissions) == 0){
        $error[$totalerrors++] = "<br>" . lang("You must add at least 1 permission to this account");
     }
     
     if (! $totalerrors) {
	$phpgw->db->query("SELECT account_permissions FROM accounts WHERE account_id = ".$account_id);
	$phpgw->db->next_record();
	$apps_before = $phpgw->db->f("account_permissions");

	while ($permission = each($new_permissions)) {
	  if ($phpgw_info["apps"][$permission[0]]["enabled"]) {
	    $phpgw->accounts->add_app($permission[0]);
	  }
	}
	$apps_after = $phpgw->accounts->add_app("",True);
	if($apps_before <> $apps_after) {
	  $after_apps = explode(":",$apps_after);
	  for ($i=1;$i<=count($after_apps);$i++) {
	    if (!strpos(" ".$apps_before." ",$after_apps)) {
	      $new_apps[] = $after_apps;
	    }
	  }
	}

	$cd = account_edit(array("loginid"   => $n_loginid,     "permissions"    => $new_permissions,
                            "firstname" => $n_firstname,   "lastname"       => $n_lastname,
                            "passwd"    => $n_passwd,      "account_status" => $n_account_status,
                            "old_loginid" => $old_loginid, "account_id"     => rawurldecode($account_id),
                            "groups"    => $phpgw->accounts->groups_array_to_string($n_groups)));

// The following sets any default preferences needed for new applications..
// This is smart enough to know if previous preferences were selected, use them.
	if (count($new_apps)) {
 	  if ($account_id <> $phpgw_info["user"]["account_id"]) {
  	    $phpgw->db->query("SELECT preference_value FROM preferences WHERE preference_owner=".$account_id,__FILE__,__LINE__);
  	    $phpgw->db->next_record();
  	    $phpgw_newuser["user"]["preferences"] = unserialize($phpgw->db->f("preference_value"));
 	  } else {
  	    $phpgw_newuser["user"]["preferences"] = $phpgw_info["user"]["preferences"];
 	  }
 	  $docommit = False;
 	  for ($j=0;$j<count($new_apps);$j++) {
  	    if (! @$phpgw_newuser["user"]["preferences"][$new_apps[$j]]) {
 	        $phpgw->common->hook_single("add_def_pref", $new_apps[$j]);
 	        $docommit = True;
  	    }
 	  }
 	  if ($docommit) {
  	    if ($account_id <> $phpgw_info["user"]["account_id"]) {
 	        $phpgw->preferences->commit_user($account_id);
  	    } else {
 	        $phpgw_info["user"]["preferences"] = $phpgw_newuser["user"]["preferences"];
 	        unset($phpgw_newuser);
 	        $phpgw->preferences->commit();
  	    }
 	  }
	}

	Header("Location: " . $phpgw->link("accounts.php", "cd=$cd"));
	$phpgw->common->phpgw_exit();
     }

  }                    // if $submit

  $phpgw->template->set_file(array("form" => "account_form.tpl"));

  $phpgw->common->phpgw_header();
  $phpgw->common->navbar();

  if ($totalerrors) {
     $phpgw->template->set_var("error_messages","<center>" . $phpgw->common->error_list($error) . "</center>");
  } else {
     $phpgw->template->set_var("error_messages","");
  }

  $userData = $phpgw->accounts->read_userData($account_id);
  $db_perms = $phpgw->accounts->read_apps($userData["account_lid"]);

  if (! $submit) {
     $n_loginid   = $userData["account_lid"];
     $n_firstname = $userData["firstname"];
     $n_lastname  = $userData["lastname"];
  }

  if ($phpgw_info["server"]["account_repository"] == "ldap") {
      $phpgw->template->set_var("form_action",$phpgw->link("editaccount.php","account_id=" . rawurlencode($userData["account_dn"]) . "&old_loginid=" . $userData["account_lid"]));
  } else {
      $phpgw->template->set_var("form_action",$phpgw->link("editaccount.php","account_id=" . $userData["account_id"] . "&old_loginid=" . $userData["account_lid"]));
  }

  $phpgw->template->set_var("tr_color1",$phpgw_info["theme"]["row_on"]);
  $phpgw->template->set_var("tr_color2",$phpgw_info["theme"]["row_off"]);

  $phpgw->template->set_var("lang_action",lang("Edit user account"));

  $phpgw->template->set_var("lang_loginid",lang("LoginID"));
  $phpgw->template->set_var("n_loginid_value",$n_loginid);

  $phpgw->template->set_var("lang_account_active",lang("Account active"));
  if ($userData["status"]) 
    $phpgw->template->set_var("account_checked","checked");
  else
    $phpgw->template->set_var("account_checked","");

  $phpgw->template->set_var("lang_password",lang("Password"));
  $phpgw->template->set_var("n_passwd_value",$n_passwd);

  $phpgw->template->set_var("lang_reenter_password",lang("Re-Enter Password"));
  $phpgw->template->set_var("n_passwd_2_value",$n_passwd_2);

  $phpgw->template->set_var("lang_firstname",lang("First Name"));
  $phpgw->template->set_var("n_firstname_value",$n_firstname);

  $phpgw->template->set_var("lang_lastname",lang("Last Name"));
  $phpgw->template->set_var("n_lastname_value",$n_lastname);

  $phpgw->template->set_var("lang_groups",lang("Groups"));
  $user_groups = $phpgw->accounts->read_group_names($userData["account_lid"]);

  $groups_select = '<select name="n_groups[]" multiple>';
  $phpgw->db->query("select * from groups");
  while ($phpgw->db->next_record()) {
     $groups_select .= '<option value="' . $phpgw->db->f("group_id") . '"';
     for ($i=0; $i<count($user_groups); $i++) {
        if ($user_groups[$i][0] == $phpgw->db->f("group_id")) {
           $groups_select .= " selected";
        }
     }
     $groups_select .= ">" . $phpgw->db->f("group_name") . "</option>\n";
  }
  $groups_select .= "</select>";
  $phpgw->template->set_var("groups_select",$groups_select);

  $i = 0;
  $sorted_apps = $phpgw_info["apps"];
//  @asort($sorted_apps);
//  @reset($sorted_apps);
  while ($permission = each($phpgw_info["apps"])) {
     if ($permission[1]["enabled"]) {
        $perm_display[$i][0] = $permission[0];
        $perm_display[$i][1] = $permission[1]["title"];
        $i++;
     }
  }

  for ($i=0;$i<200;) {     // The $i<200 is only used for a brake
     if (! $perm_display[$i][1]) break;
     $perm_html .= '<tr bgcolor="'.$phpgw_info["theme"]["row_on"].'"><td>' . lang($perm_display[$i][1]) . '</td>'
                 . '<td><input type="checkbox" name="new_permissions['
                 . $perm_display[$i][0] . ']" value="True"';
     if ($new_permissions[$perm_display[$i][0]] || $db_perms[$perm_display[$i][0]]) {
        $perm_html .= " checked";
     }
     $perm_html .= "></td>";
     $i++;

     if (! $perm_display[$i][1]) break;
     $perm_html .= '<td>' . lang($perm_display[$i][1]) . '</td>'
                 . '<td><input type="checkbox" name="new_permissions['
                 . $perm_display[$i][0] . ']" value="True"';
     if ($new_permissions[$perm_display[$i][0]] || $db_perms[$perm_display[$i][0]]) {
        $perm_html .= " checked";
     }
     $perm_html .= "></td></tr>";
     $i++;
  }

  $phpgw->template->set_var("permissions_list",$perm_html);


  $phpgw->template->set_var("lang_button",lang("Save"));
  $phpgw->template->pparse("out","form");

  account_close();
  $phpgw->common->phpgw_footer();
?>
