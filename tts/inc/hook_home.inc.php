<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	/* $Id$ */

	$d1 = strtolower(substr($phpgw_info['server']['app_inc'],0,3));
	if($d1 == 'htt' || $d1 == 'ftp' )
	{
		echo "Failed attempt to break in via an old Security Hole!<br>\n";
		$phpgw->common->phpgw_exit();
	}
	unset($d1);

	$tmp_app_tpl = $phpgw->common->get_tpl_dir('tts');

	if ($phpgw_info["user"]["apps"]["tts"]
		&& $phpgw_info["user"]["preferences"]["tts"]["mainscreen_show_new_updated"])
	{
		echo "\n<!-- Begin TTS New/Updated -->\n";

		// this will be an user option
		$filtermethod="where t_timestamp_closed='0' and t_assignedto='".$phpgw_info["user"]["userid"]."'";
		$sortmethod="order by t_priority desc";

		$phpgw->db->query("select t_id,t_category,t_priority,t_assignedto,t_timestamp_opened,t_user,t_timestamp_closed,t_subject,t_watchers "
		. "from ticket $filtermethod $sortmethod");
		$phpgw->db->next_record();
		$phpgw->db->f('0') ;

		$p = CreateObject('phpgwapi.Template',$tmp_app_tpl);
		// echo PHPGW_APP_TPL;
		$p->set_file(array(
			'index'   => 'hook_home.tpl'
	   	));

		$p->set_unknowns('remove');
	    
		$p->set_block('index', 'tts_list', 'tts_list');
		$p->set_block('index', 'tts_row', 'tts_row');
		$p->set_block('index', 'tts_ticket_id_read', 'tts_ticket_id_read');
		$p->set_block('index', 'tts_ticket_id_unread', 'tts_ticket_id_unread');


		while ($phpgw->db->next_record())
		{
			$p->set_var('tts_col_status',"");
			$priority=$phpgw->db->f("t_priority");
			switch ($priority)
			{
				case 1:  $tr_color = $phpgw_info["theme"]["bg01"]; break;
				case 2:  $tr_color = $phpgw_info["theme"]["bg02"]; break;
				case 3:  $tr_color = $phpgw_info["theme"]["bg03"]; break;
				case 4:  $tr_color = $phpgw_info["theme"]["bg04"]; break;
				case 5:  $tr_color = $phpgw_info["theme"]["bg05"]; break;
				case 6:  $tr_color = $phpgw_info["theme"]["bg06"]; break;
				case 7:  $tr_color = $phpgw_info["theme"]["bg07"]; break;
				case 8:  $tr_color = $phpgw_info["theme"]["bg08"]; break;
				case 9:  $tr_color = $phpgw_info["theme"]["bg09"]; break;
				case 10: $tr_color = $phpgw_info["theme"]["bg10"]; break;
				default: $tr_color = $phpgw_info["theme"]["bg_color"];
		    	}

    			$t_watchers=explode(":",$phpgw->db->f("t_watchers"));
			if (in_array( $phpgw_info["user"]["userid"], $t_watchers)) {$t_read=1;} else { $t_read=0;}
					
			$p->set_var('tts_row_color', $tr_color );
			$p->set_var('tts_ticketdetails_link', $phpgw->link("/tts/viewticket_details.php","ticketid=" . $phpgw->db->f("t_id")));
	
			$p->set_var('tts_t_id',$phpgw->db->f("t_id") );
    
			if (!$t_read==1) { 
			    $p->parse('tts_ticket_id', 'tts_ticket_id_unread', false );
			 } else {
			    $p->parse('tts_ticket_id', 'tts_ticket_id_read', false );
			 }

			$p->set_var('tts_t_user', $phpgw->db->f("t_user"));
			$p->set_var('tts_t_timestampopened', $phpgw->common->show_date($phpgw->db->f("t_timestamp_opened")));
			$p->set_var('tts_t_subject', $phpgw->db->f("t_subject"));
    
			$p->parse('rows','tts_row',true);
		
		}

		echo "\n<!-- End TTS New/Updated -->\n";
	}
?>
