<?php
  /**************************************************************************\
  * phpGroupWare - Trouble Ticket System                                     *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/


  /* $Id $ */
  
	if ($submit)
	{
		$phpgw_info["flags"] = array("noheader" => True, "nonavbar" => True);
	}

	$phpgw_info["flags"]["currentapp"] = "tts";
	$phpgw_info["flags"]["enable_send_class"]       = True;
	include("../header.inc.php");
	if (! $submit)
	{

	$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	//  echo PHPGW_APP_TPL;
	$p->set_file(array(
		'newticket'   => 'newticket.tpl'
    	));
	
	$p->set_block('newticket', 'tts_new_lstassignto','tts_new_lstassignto');
	$p->set_block('newticket', 'tts_new_lstcategory','tts_new_lstcategory');
	
	$p->set_unknowns('remove');
	$p->set_var('tts_newticket_link', $phpgw->link("/tts/newticket.php"));
        $p->set_var('tts_bgcolor',$theme["th_bg"] );
        $p->set_var('tts_textcolor', $theme["th_text"] );
        $p->set_var('tts_lang_addnewticket', lang("Add new ticket"));
        $p->set_var('tts_lang_group', lang("Group"));
        $p->set_var('tts_lang_subject', lang("Subject") );
        $p->set_var('tts_lang_nosubject', lang("No subject"));
        $p->set_var('tts_lang_details', lang("Detail"));
        $p->set_var('tts_lang_priority', lang("Priority"));
        $p->set_var('tts_lang_lowest', lang("Lowest"));
        $p->set_var('tts_lang_medium', lang("Medium"));
        $p->set_var('tts_lang_highest', lang("Highest"));
        $p->set_var('tts_lang_addticket', lang("Add Ticket"));
        $p->set_var('tts_lang_clearform', lang("Clear Form"));

		$groups = CreateObject('phpgwapi.accounts');
		$group_list = $groups->get_list('groups');
		while (list($key,$entry) = each($group_list))
		{
    		    $p->set_var('tts_account_lid', $entry['account_lid']);
    		    $p->set_var('tts_account_name', $entry['account_lid']);
		    $p->parse('tts_new_lstcategories','tts_new_lstcategory',true);
		}
            
        $p->set_var('tts_lang_assignto', lang("assign to"));

		$accounts = CreateObject('phpgwapi.accounts',$group_id);
		$account_list = $accounts->get_list('accounts');

		$p->set_var('tts_account_lid', "0" );
		$p->set_var('tts_account_name', lang("none"));
		$p->set_var('tts_assignedtoselected', "selected");
		$p->parse('tts_new_lstassigntos','tts_new_lstassignto',false);
		
		while (list($key,$entry) = each($account_list))
		{
			if ($entry['account_lid'])
			{
    			$p->set_var('tts_account_lid', $entry['account_lid']);
    			$p->set_var('tts_account_name', $entry['account_lid']);
			}
			$p->parse('tts_new_lstassigntos','tts_new_lstassignto',true);
		}

	
	$p->pparse('out', 'newticket');
	
	$phpgw->common->phpgw_footer();
	}
	else
	{
		//$current_date = date("ymdHi");		//set timestamp

		$txtDetail .= $phpgw_info["user"]["userid"] . " - " . $phpgw->common->show_date($phpgw->db->f(6)) . "<BR>\n";
		$txtDetail .= $txtAdditional . "<br><hr>";
		$txtDetail = addslashes($txtDetail);

		$phpgw->db->query("INSERT INTO ticket (t_category,t_detail,t_priority,t_user,t_assignedto, "
			. " t_timestamp_opened,t_timestamp_closed,t_subject) VALUES ('$lstCategory','$txtDetail',"
			. "'$optPriority','" . $phpgw_info["user"]["userid"] . "','$assignto','"
			. time() . "',0,'$subject');");
		$phpgw->db->query("SELECT t_id FROM ticket WHERE t_subject='$subject' AND t_user='".$phpgw_info["user"]["userid"]."'");
		$phpgw->db->next_record();
		if($phpgw_info['server']['tts_mailticket'])
		{
			mail_ticket($phpgw->db->f("t_id"));
		}

		Header("Location: " . $phpgw->link("/tts/index.php"));
	}
?>
