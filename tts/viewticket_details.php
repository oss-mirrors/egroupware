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

  /* $Id$ */

	$phpgw_info["flags"] = array("currentapp" => "tts");

	if ($submit)
	{
		$phpgw_info["flags"]["noheader"] = True;
		$phpgw_info["flags"]["nonavbar"] = True;
		$phpgw_info["flags"]["enable_config_class"] = True;
	}

	include("../header.inc.php");

	if (! $submit)
	{

		// select the ticket that you selected
		$phpgw->db->query("select t_id,t_category,t_detail,t_priority,t_user,t_assignedto,"
			. "t_timestamp_opened, t_timestamp_closed, t_subject, t_watchers from ticket where t_id='$ticketid'");
		$phpgw->db->next_record();

		$lstAssignedto=$phpgw->db->f("t_assignedto");
		$lstCategory=$phpgw->db->f("t_category");

		// mark as read.
		$temp_watchers=explode(":",$phpgw->db->f("t_watchers"));
		if (!(in_array( $phpgw_info["user"]["userid"], $temp_watchers))) {
	    	    $temp_watchers[]=$phpgw_info["user"]["userid"];
	    	    $t_watchers=implode(":",$temp_watchers);
	    	    // var_dump($t_watchers);
	    	    $phpgw->db->query("UPDATE ticket set t_watchers='".$t_watchers."' where t_id=$ticketid");
		} 


		// Print the table
		$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
		//  echo PHPGW_APP_TPL;
		$p->set_file(array(
		    'viewticket'   => 'viewticket_details.tpl'
    		));
	
		$p->set_block('viewticket', 'tts_select_options','tts_select_options');
	
		$p->set_unknowns('remove');


                if ($phpgw->db->f("t_timestamp_closed") > 0) {
		    $p->set_var('tts_t_status', $phpgw->common->show_date($phpgw->db->f("t_timestamp_closed")));
                } else {
		    $p->set_var('tts_t_status', lang("in progress"));
                }
	
		// Choose the correct priority to display
		$priority_selected[$phpgw->db->f("t_priority")] = " selected";
		$priority_comment[1]=" - ".lang("Lowest"); 
		$priority_comment[5]=" - ".lang("Medium"); 
		$priority_comment[10]=" - ".lang("Highest"); 

        	for ($i=1; $i<=10; $i++) {
		    $p->set_var('tts_optionname', $i.$priority_comment[$i]);
		    $p->set_var('tts_optionvalue', $i);
		    $p->set_var('tts_optionselected', $priority_selected[$i]);
		    $p->parse('tts_priority_options','tts_select_options',true);
		}
		
		// assigned to
		$accounts = CreateObject('phpgwapi.accounts',$group_id);
		$account_list = $accounts->get_list('accounts');
		$p->set_var('tts_optionname', lang("none"));
		$p->set_var('tts_optionvalue', "none" );
		$p->set_var('tts_optionselected', "");
		$p->parse('tts_assignedto_options','tts_select_options',true);
		while (list($key,$entry) = each($account_list))
		{
		    $tag="";
		    if ($entry['account_lid'] == "$lstAssignedto") { $tag = "selected"; }
		    $p->set_var('tts_optionname', $entry['account_lid']);
		    $p->set_var('tts_optionvalue', $entry['account_lid']);
		    $p->set_var('tts_optionselected', $tag);
		    $p->parse('tts_assignedto_options','tts_select_options',true);
		}
		
		// group
		$groups = CreateObject('phpgwapi.accounts');
		$group_list = $groups->get_list('groups');
		while (list($key,$entry) = each($group_list))
		{
		    $tag="";
		    if ($entry['account_lid'] == "$lstCategory") { $tag = "selected"; }
		    $p->set_var('tts_optionname', $entry['account_lid']);
		    $p->set_var('tts_optionvalue', $entry['account_lid']);
		    $p->set_var('tts_optionselected', $tag);
		    $p->parse('tts_group_options','tts_select_options',true);
		}
	    
	        $details_string = stripslashes($phpgw->db->f("t_detail"));

		$p->set_var('tts_viewticketdetails_link', $phpgw->link("/tts/viewticket_details.php"));
		$p->set_var('tts_t_id', $phpgw->db->f("t_id"));
		$p->set_var('tts_t_user', $phpgw->db->f("t_user"));
		$p->set_var('tts_th_bg', $phpgw_info["theme"][th_bg]);
		$p->set_var('tts_lang_viewjobdetails', lang("View Job Detail"));
		$p->set_var('tts_lang_assignedfrom', lang("Assigned from"));
		$p->set_var('tts_lang_opendate', lang("Open Date"));
		$p->set_var('tts_t_opendate', $phpgw->common->show_date($phpgw->db->f("t_timestamp_opened")));
		$p->set_var('tts_t_status', $phpgw->db->f("t_timestamp_closed")?$phpgw->common->show_date($phpgw->db->f("t_timestamp_closed")):lang("In progress"));
		$p->set_var('tts_lang_closedate', lang("Close Date"));
		$p->set_var('tts_lang_priority', lang("Priority"));
		$p->set_var('tts_lang_group', lang("Group"));
		$p->set_var('tts_lang_assignedto', lang("Assigned to"));
		$p->set_var('tts_hidden_detailstring', $phpgw->strip_html($details_string));
		$p->set_var('tts_lang_subject', lang("Subject"));
		$p->set_var('tts_lang_details', lang("Details"));
		$p->set_var('tts_t_subject', stripslashes($phpgw->db->f("t_subject")));
		$p->set_var('tts_detailstring', stripslashes($details_string));
		$p->set_var('tts_lang_additionalnotes', lang("Additional notes"));
	        $p->set_var('tts_lang_ok', lang("OK"));

		// change buttons from update/close to close/reopen if ticket is already closed
		if ($phpgw->db->f(7) > 0)
		{
		        $p->set_var('tts_leftradio', lang("Closed"));
		        $p->set_var('tts_rightradio', lang("ReOpen"));
		        $p->set_var('tts_leftradiovalue', "letclosed");
		        $p->set_var('tts_rightradiovalue', "reopen");
		}
		else
		{
		        $p->set_var('tts_leftradio', lang("Update"));
		        $p->set_var('tts_rightradio', lang("Close"));
		        $p->set_var('tts_leftradiovalue', "update");
		        $p->set_var('tts_rightradiovalue', "close");
		}
		
		$p->set_var('tts_select_options',"");
	
		$p->pfp('out','viewticket');

		$phpgw->common->phpgw_footer();
	}
	else
	{

		// DB Content is fresher than http posted value.
		$phpgw->db->query("select t_detail, t_assignedto, t_category, t_priority from ticket where t_id='".$t_id."'");
		$phpgw->db->next_record();
		$txtDetail = $phpgw->db->f("t_detail");
		$oldassigned = $phpgw->db->f("t_assignedto");
		$oldpriority = $phpgw->db->f("t_priority");
		$oldcategory = $phpgw->db->f("t_category");
		
		if ($optUpdateclose == "letclosed" )
		{
			# let ticket be closed
			# don't do any changes, ppl will have to reopen tickets to
			# submit additional infos
		} else {
			if ($optUpdateclose == "reopen")
			{
				# reopen the ticket
				$phpgw->db->query("UPDATE ticket set t_timestamp_closed='0' WHERE t_id=$t_id");
				$txtReopen = "<b>".lang("Ticket reopened")."</b><br>";
			}

			if ( $optUpdateclose == "close" )
			{
				$txtClose = "<br /><b>".lang("Ticket closed")."</b>";
				$phpgw->db->query("UPDATE ticket set t_timestamp_closed='" . time() . "' WHERE t_id=$t_id");
			}
	
			if ($oldassigned != $lstAssignedto)
			{
				$txtAssignTo = "<br /><b>".lang("Ticket assigned to x",$lstAssignedto)."</b>";
			}

			if ($oldpriority != $optPriority)
			{
				$txtPriority = "<br /><b>".lang("Priority changed to x",$optPriority)."</b>";
			}

			if ($oldcategory != $lstCategory)
			{
				$txtCategory = "<br /><b>".lang("Category changed to x",$lstCategory)."</b>";
			}

			$txtAdditional = $txtReopen.$txtAdditional.$txtAssignTo.$txtCategory.$txtPriority.$txtClose;

			if (! empty($txtAdditional))
			{
		    	
				$UserInfo = "<BR><i>\n" . $phpgw_info["user"]["userid"] . " - "
		    				. $phpgw->common->show_date(time()) . "</i><BR>\n";

				$txtDetail .= $UserInfo;
				$txtDetail .= nl2br($txtAdditional);
				$txtDetail .= "<hr>";
	
				# update the database if ticket content changed
				$phpgw->db->query("UPDATE ticket set t_category='$lstCategory',t_detail='".addslashes($txtDetail)."',t_priority='$optPriority',t_user='$lstAssignedfrom',t_assignedto='$lstAssignedto',t_watchers='".$phpgw_info["user"]["userid"]."' WHERE t_id=$t_id");
		
				if ($phpgw_info['server']['tts_mailticket']) {
				    mail_ticket($t_id);
				}
			}

		}
		Header("Location: " . $phpgw->link("/tts/index.php"));
	}

?>
