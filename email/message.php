<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * Based on Aeromail by Mark Cushman <mark@cushman.net>                     *
  *          http://the.cushman.net/                                         *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	/* $Id$ */

	Header('Cache-Control: no-cache');
	Header('Pragma: no-cache');
	Header('Expires: Sat, Jan 01 2000 01:01:01 GMT');

	$phpgw_info["flags"] = array(
		'currentapp'			=>	'email',
		'enable_network_class'		=>	True,
		'enable_nextmatchs_class'	=>	True
	);

	include('../header.inc.php');

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array(		
		'T_message_main' => 'message_main.tpl',
		//'T_message_display' => 'message_display.tpl',
		'T_message_echo_dump' => 'message_echo_dump.tpl'
	));
	$t->set_block('T_message_main','B_x-phpgw-type','V_x-phpgw-type');
	$t->set_block('T_message_main','B_cc_data','V_cc_data');
	$t->set_block('T_message_main','B_attach_list','V_attach_list');
	$t->set_block('T_message_main','B_debug_parts','V_debug_parts');
	$t->set_block('T_message_main','B_display_part','V_display_part');
	//$t->set_block('T_message_blocks','B_output_bound','V_output_bound');
	//$t->set_block('T_message_display','B_message_intro','V_message_intro');
	//$t->set_block('T_message_display','B_message_part','V_message_part');
	$t->set_block('T_message_echo_dump','B_setup_echo_dump','V_setup_echo_dump');
	$t->set_block('T_message_echo_dump','B_done_echo_dump','V_done_echo_dump');


// ----  Are We In Newsmode Or Not  -----
	if (isset($newsmode) && $newsmode == "on")
	{
		$phpgw_info['flags']['newsmode'] = True;
	}
	else
	{
		$phpgw_info['flags']['newsmode'] = False;
	}

// ----  Fill Some Important Variables  -----
	$image_dir = $phpgw->common->get_image_path($phpgw_info['flags']['currentapp']);
	$svr_image_dir = $phpgw_info['server']['images_dir'];
	$sm_envelope_img = img_maketag($image_dir.'/sm_envelope.gif',"Add to address book","8","10","0");
	$session_folder = 'folder='.urlencode($folder).'&msgnum=';
	$default_sorting = $phpgw_info['user']['preferences']['email']['default_sorting'];

// ----  General Information about The Message  -----
	$msg = $phpgw->msg->header($mailbox, $msgnum);
	$struct = $phpgw->msg->fetchstructure($mailbox, $msgnum);
	$totalmessages = $phpgw->msg->num_msg($mailbox);

	$subject = $phpgw->msg->get_subject($msg,'');
	$message_date = $phpgw->common->show_date($msg->udate);

	#set_time_limit(0);

// ----  Special X-phpGW-Type Message Flag  -----
	// is this still a planned feature?
	$application = '';
	$msgtype = $phpgw->msg->get_flag($mailbox,$msgnum,'X-phpGW-Type');
	if (!empty($msgtype))
	{
		$msg_type = explode(';',$msgtype);
		$application = substr($msg_type[0],1,strlen($msg_type[0])-2);
		$t->set_var('application',$application);
		$t->parse('V_x-phpgw-type','B_x-phpgw-type');
	}
	else
	{
		$t->set_var('V_x-phpgw-type','');
	}

	if (!$folder)
	{
		$folder = 'INBOX';
	}

// ----  What Folder To Return To  -----
        $lnk_goback_folder = href_maketag($phpgw->link('/email/index.php','folder='.urlencode($folder)),$folder);

// ----  Images and Hrefs For Reply, ReplyAll, Forward, and Delete  -----
        $reply_img = img_maketag($image_dir.'/sm_reply.gif',lang('reply'),'19','26','0');
	$reply_url = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php','action=reply&folder='.urlencode($folder).'&msgnum='.$msgnum);
	$ilnk_reply = href_maketag($reply_url, $reply_img);

        $replyall_img = img_maketag($image_dir .'/sm_reply_all.gif',lang('reply all'),"19","26",'0');
	$replyall_url = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php','action=replyall&folder='.urlencode($folder).'&msgnum='.$msgnum);
	$ilnk_replyall = href_maketag($replyall_url, $replyall_img);

	$forward_img = img_maketag($image_dir .'/sm_forward.gif',lang('forward'),"19","26",'0');
	$forward_url =  $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php','action=forward&folder='.urlencode($folder).'&msgnum='.$msgnum);
	$ilnk_forward = href_maketag($forward_url, $forward_img);

	$delete_img = img_maketag($image_dir .'/sm_delete.gif',lang('delete'),"19","26",'0');
	$delete_url = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/action.php','what=delete&folder='.urlencode($folder).'&msgnum='.$msgnum);
	$ilnk_delete = href_maketag($delete_url, $delete_img);

	$t->set_var('theme_font',$phpgw_info['theme']['font']);
	$t->set_var('reply_btns_bkcolor',$phpgw_info['theme']['em_folder']);
	$t->set_var('reply_btns_text',$phpgw_info['theme']['em_folder_text']);
	$t->set_var('lnk_goback_folder',$lnk_goback_folder);
	$t->set_var('ilnk_reply',$ilnk_reply);
	$t->set_var('ilnk_replyall',$ilnk_replyall);
	$t->set_var('ilnk_forward',$ilnk_forward);
	$t->set_var('ilnk_delete',$ilnk_delete);

// ----  Go To Previous Message Handling  -----
	if ($msgnum != 1 || ($default_sorting == 'new_old' && $msgnum != $totalmeesages))
	{
		if ($default_sorting == 'new_old')
		{
			$pm = $msgnum + 1;
		}
		else
		{
			$pm = $msgnum - 1;
		}

		if ($default_sorting == 'new_old' && ($msgnum == $totalmessages && $msgnum != 1 || $totalmessages == 1))
		{
			$ilnk_prev_msg = img_maketag($svr_image_dir.'/left-grey.gif',"No Previous Message",'','','0');
		}
		else
		{
			$prev_msg_link = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/message.php',$session_folder.$pm);
			$prev_msg_img = img_maketag($svr_image_dir.'/left.gif',"Previous Message",'','','0');
			$ilnk_prev_msg = href_maketag($prev_msg_link,$prev_msg_img);
		}
	}
	else
	{
		$ilnk_prev_msg = img_maketag($svr_image_dir.'/left-grey.gif',"No Previous Message",'','','0');
	}

// ----  Go To Next Message Handling  -----
	if ($msgnum < $totalmessages || ($default_sorting == 'new_old' && $msgnum != 1))
	{
		if ($default_sorting == 'new_old')
		{
			$nm = $msgnum - 1;
		}
		else
		{
			$nm = $msgnum + 1;
		}

		if ($default_sorting == 'new_old' && $msgnum == 1 && $totalmessages != $msgnum)
		{
			$ilnk_next_msg = img_maketag($svr_image_dir.'/right-grey.gif',"No Next Message",'','','0');
		}
		else
		{
			$next_msg_link = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/message.php',$session_folder.$nm);
			$next_msg_img = img_maketag($svr_image_dir.'/right.gif',"Next Message",'','','0');
			$ilnk_next_msg = href_maketag($next_msg_link,$next_msg_img);
		}
	}
	else
	{
		$ilnk_next_msg = img_maketag($svr_image_dir.'/right-grey.gif',"No Next Message",'','','0');
	}

	$t->set_var('ilnk_prev_msg',$ilnk_prev_msg);
	$t->set_var('ilnk_next_msg',$ilnk_next_msg);

// ----  Labels and Colors for From, To, CC, Files, and Subject  -----
	$t->set_var('tofrom_labels_bkcolor', $phpgw_info['theme']['th_bg']);
	$t->set_var('tofrom_data_bkcolor', $phpgw_info['theme']['row_on']);

	$t->set_var('lang_from', lang('from'));
	$t->set_var('lang_to', lang('to'));
	$t->set_var('lang_cc', lang('cc'));
	$t->set_var('lang_date', lang('date'));
	$t->set_var('lang_files', lang('files'));
	$t->set_var('lang_subject', lang('subject'));

// ----  From: Message Data  -----
	// format "From" according to user preferences  ?
	// WHAT IS THIS PREF SUPPOSED TO DO ???
	$from = $msg->from[0];
	$personal = !isset($from->personal) || !$from->personal ? $from->mailbox.'@'.$from->host : $from->personal;
	if ($phpgw_info['user']['preferences']['email']['show_addresses'] != 'no' && ($personal != $from->mailbox.'@'.$from->host))
	{
		$display_address->from = '('.$from->mailbox.'@'.$from->host.')';
	}
	if ($msg->from)
	{

		$from_real_name = href_maketag($phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php','folder='.urlencode($folder).'&to='.urlencode($from->mailbox.'@'.$from->host)),
			decode_header_string($personal) );
		$from_raw_addy = trim($display_address->from);

		$from_addybook_add = href_maketag(
			$phpgw->link('/addressbook/add.php','add_email='.urlencode($from->mailbox.'@'.$from->host).'&name='.urlencode($personal).'&referer='.urlencode($PHP_SELF.'?'.$QUERY_STRING)),
			$sm_envelope_img
		);
	}
	else
	{
		$from_real_name = '';
		$from_raw_addy = lang('Undisclosed Sender');
		$from_addybook_add = '';
	}

	$t->set_var('from_real_name',$from_real_name);
	$t->set_var('from_raw_addy',$from_raw_addy);
	$t->set_var('from_addybook_add',$from_addybook_add);


// ----  To:  Message Data  -----
	if ($msg->to)
	{
		for ($i = 0; $i < count($msg->to); $i++)
		{
			$topeople = $msg->to[$i];
			$personal = !isset($topeople->personal) || !$topeople->personal ? $topeople->mailbox.'@'.$topeople->host : $topeople->personal;
			$personal = decode_header_string($personal);
			if ($phpgw_info['user']['preferences']['email']['show_addresses'] != 'no' && ($personal != $topeople->mailbox.'@'.$topeople->host))
			{
				$display_address->to = '('.$topeople->mailbox.'@'.$topeople->host.')';
			}

			$to_real_name = href_maketag(
				$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php','folder='.urlencode($folder).'&to='.$topeople->mailbox.'@'.$topeople->host),
				$personal
			);
			$to_raw_addy = trim($display_address->to);

			$to_addybook_add = href_maketag(
				$phpgw->link('/addressbook/add.php','add_email='.urlencode($topeople->mailbox.'@'.$topeople->host).'&name='.urlencode($personal).'&referer='.urlencode($PHP_SELF.'?'.$QUERY_STRING)),
				$sm_envelope_img
			);
			// assemble the string and store for later use
			$to_data_array[$i] = $to_real_name.' '.$to_raw_addy.' '.$to_addybook_add;
		}
		// throw a spacer comma in between addresses, if more than one
		$to_data_final = implode(', ',$to_data_array);
	}
	else
	{
		$to_data_final = lang('Undisclosed Recipients');
	}
	$t->set_var('to_data_final',$to_data_final);


// ----  Cc:  Message Data  -----
	if (isset($msg->cc) && count($msg->cc) > 0)
	{
		for ($i = 0; $i < count($msg->cc); $i++)
		{
			$ccpeople = $msg->cc[$i];
			$personal = !$ccpeople->personal ? $ccpeople->mailbox.'@'.$ccpeople->host : $ccpeople->personal;
			$personal = decode_header_string($personal);

			$cc_real_name = href_maketag($phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php','folder='.urlencode($folder)
				.'&to='.urlencode($ccpeople->mailbox.'@'.$ccpeople->host)),
				$personal
			);
			// we never show cc's raw address
			$cc_raw_addy = '';

			$cc_addybook_add = href_maketag(
				$phpgw->link('/addressbook/add.php','add_email='.urlencode($topeople->mailbox.'@'.$topeople->host).'&name='.urlencode($personal).'&referer='.urlencode($PHP_SELF.'?'.$QUERY_STRING)),
				$sm_envelope_img
			);
			// assemble the string and store for later use
			$cc_data_array[$i] = $cc_real_name.' '.$cc_raw_addy.' '.$cc_addybook_add;
		}
		// throw a spacer comma in between addresses, if more than one
		$cc_data_final = implode(', ',$cc_data_array);
		$t->set_var('cc_data_final',$cc_data_final);
		$t->parse('V_cc_data','B_cc_data');
	}
	else
	{
		$t->set_var('V_cc_data','');
	}

// ---- Message Date  (set above)  -----
	$t->set_var('message_date', $message_date);
// ---- Message Subject  (set above)  -----
	$t->set_var('message_subject',$subject);

/*
// ---- Flatten Message Structure Array   -----
	// prepare for recursive traversal of this multi-dimentional array structure
	global $struct_not_set, $index_flat_array, $part_nice, $control_array;
	$struct_not_set = '-1';
	//$index_flat_array = -1; // it will be advanced to 0 before its used
	// need this unset so we can call this function recursively
	unset($part_nice);

	// this function will be called recursively
	function traverse_part_struct($part, $loops, $parent_part)
	{
		global $struct_not_set, $index_flat_array, $part_nice, $control_array;
		//echo '=*=*=*=*=*' .serialize($part) .'=*=*=*=*=*' ." <br> \r\n";
		for ($i = 0; $i < $loops; $i++)
		{

			// NEW CONTROL TEST
			// has this parent been seen by the control_array?
			$z = count($control_array);
			$seen_before = $struct_not_set;
			if ($z != 0)
				for ($y = 0; $y < $z; $y++)
				{
					if ($control_array[$y] == serialize($part))
					{
						$seen_before = $y;
						$debth = $y + 1;
					}
				}
			// if not seen, add it, using $z as the the "next blank" index num in control_array 
			if ($seen_before == $struct_not_set)
			{
				$control_array[$z] = serialize($part);
				$debth = $z + 1;
			}

			$index_flat_array++;
			$part_nice[$index_flat_array] = pgw_msg_struct($part[$i], ($i+1), $loops, $debth, $folder, $msgnum);

			if ($part_nice[$index_flat_array]['ex_num_subparts'] != $struct_not_set)
			{
				traverse_part_struct($part_nice[$index_flat_array]['subpart'], $part_nice[$index_flat_array]['ex_num_subparts'], $part_nice[$index_flat_array]);
			}
		}
	}

	// start the process, call traverse_part_struct recursively until done
	set_time_limit(15);
	$array_filled = (isset($part_nice));
	if (!$array_filled)
	{
		// get INITIAL part structure / array from the fetchstructure  variable
		if ((!isset($struct->parts[0]) || (!$struct->parts[0])))
		{
			//$part = $struct;
			$part[0] = $struct;
		}
		else
		{
			$part = $struct->parts;
		}
		// track recursion
		$index_flat_array = -1; // it will be advanced  its used (we added 3 sudo parts to zero level before traverse_part_struct)
		$start_analysis_pos = 0;
		$control_array = Array();
		traverse_part_struct($part, count($part), $struct_not_set);
	}
	set_time_limit(0);
*/

// ---- Message Structure Analysis   -----
	global $struct_not_set;
	$struct_not_set = '-1';
	
	// get INITIAL part structure / array from the fetchstructure  variable
	if ((!isset($struct->parts[0]) || (!$struct->parts[0])))
	{
		$part[0] = $struct;
	}
	else
	{
		$part = $struct->parts;
	}
	$d1_num_parts = count($part);
	
	/*
	// how many parts does this message have
	if ((!isset($struct->parts)) || (!$struct->parts))
	{
		// either (a) a text only message or (b) email with NO body text, but has 1 attachment
		$d1_num_parts = 1;
	}
	elseif (count($struct->parts) == 0)
	{
		// either (a) a text only message or (b) email with NO body text, but has 1 attachment
		$d1_num_parts = 1;
	}
	else
	{
		$d1_num_parts = count($struct->parts);
	}
	*/

	$part_nice = Array();

	// get PRIMARY level part information
	$deepest_level=0;
	$array_position = -1;  // it will be advanced to 0 before its used
	for ($d1 = 0; $d1 < $d1_num_parts; $d1++)
	{
		$array_position++;
		$d1_mime_num = (string)($d1+1);
		$part_nice[$array_position] = pgw_msg_struct($part[$d1], $struct_not_set, $d1_mime_num, ($d1+1), $d1_num_parts, 1, $folder, $msgnum);
		if ($deepest_level < 1) { $deepest_level=1; }
		
		// get SECONDARY/EMBEDDED level part information
		$d1_array_pos = $array_position;
		if ($part_nice[$d1_array_pos]['ex_num_subparts'] != $struct_not_set)
		{
			$d2_num_parts = $part_nice[$d1_array_pos]['ex_num_subparts'];
			for ($d2 = 0; $d2 < $d2_num_parts; $d2++)
			{
				$d2_part = $part_nice[$d1_array_pos]['subpart'][$d2];
				$d2_mime_num = (string)($d1+1) .'.' .(string)($d2+1);
				$array_position++;
				$part_nice[$array_position] = pgw_msg_struct($d2_part, $d1_array_pos, $d2_mime_num, ($d2+1), $d2_num_parts, 2, $folder, $msgnum);
				if ($deepest_level < 2) { $deepest_level=2; }
				
				// get THIRD/EMBEDDED level part information
				$d2_array_pos = $array_position;
				if ($d2_part['ex_num_subparts'] != $struct_not_set)
				{
					$d3_num_parts = $part_nice[$d2_array_pos]['ex_num_subparts'];
					for ($d3 = 0; $d3 < $d3_num_parts; $d3++)
					{
						$d3_part = $part_nice[$d2_array_pos]['subpart'][$d3];
						$d3_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1);
						$array_position++;
						$part_nice[$array_position] = pgw_msg_struct($d3_part, $d2_array_pos, $d3_mime_num, ($d3+1), $d3_num_parts, 3, $folder, $msgnum);
						if ($deepest_level < 3) { $deepest_level=3; }

						// get FOURTH/EMBEDDED level part information
						$d3_array_pos = $array_position;
						if ($d3_part['ex_num_subparts'] != $struct_not_set)
						{
							$d4_num_parts = $part_nice[$d3_array_pos]['ex_num_subparts'];
							for ($d4 = 0; $d4 < $d4_num_parts; $d4++)
							{
								$d4_part = $part_nice[$d3_array_pos]['subpart'][$d4];
								$d4_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1);
								$array_position++;
								$part_nice[$array_position] = pgw_msg_struct($d4_part, $d3_array_pos, $d4_mime_num, ($d4+1), $d4_num_parts, 4, $folder, $msgnum);
								if ($deepest_level < 4) { $deepest_level=4; }

								// get FIFTH LEVEL EMBEDDED level part information
								$d4_array_pos = $array_position;
								if ($d4_part['ex_num_subparts'] != $struct_not_set)
								{
									$d5_num_parts = $part_nice[$d4_array_pos]['ex_num_subparts'];
									for ($d5 = 0; $d5 < $d5_num_parts; $d5++)
									{
										$d5_part = $part_nice[$d4_array_pos]['subpart'][$d5];
										$d5_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1) .'.' .(string)($d5+1);
										$array_position++;
										$part_nice[$array_position] = pgw_msg_struct($d5_part, $d4_array_pos, $d5_mime_num, ($d5+1), $d5_num_parts, 5, $folder, $msgnum);
										if ($deepest_level < 5) { $deepest_level=5; }

										// get SISTH LEVEL EMBEDDED level part information
										$d5_array_pos = $array_position;
										if ($d5_part['ex_num_subparts'] != $struct_not_set)
										{
											$d6_num_parts = $part_nice[$d5_array_pos]['ex_num_subparts'];
											for ($d6 = 0; $d6 < $d6_num_parts; $d6++)
											{
												$d6_part = $part_nice[$d5_array_pos]['subpart'][$d6];
												$d6_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1) .'.' .(string)($d5+1) .'.' .(string)($d6+1);
												$array_position++;
												$part_nice[$array_position] = pgw_msg_struct($d6_part, $d5_array_pos, $d6_mime_num, ($d6+1), $d6_num_parts, 6, $folder, $msgnum);
												if ($deepest_level < 6) { $deepest_level=6; }

												// get SEVENTH LEVEL EMBEDDED level part information
												$d6_array_pos = $array_position;
												if ($d6_part['ex_num_subparts'] != $struct_not_set)
												{
													$d7_num_parts = $part_nice[$d6_array_pos]['ex_num_subparts'];
													for ($d7 = 0; $d7 < $d7_num_parts; $d7++)
													{
														$d7_part = $part_nice[$d6_array_pos]['subpart'][$d7];
														$d7_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1) .'.' .(string)($d5+1) .'.' .(string)($d6+1) .'.' .(string)($d7+1);
														$array_position++;
														$part_nice[$array_position] = pgw_msg_struct($d7_part, $d6_array_pos, $d7_mime_num, ($d7+1), $d7_num_parts, 7, $folder, $msgnum);
														if ($deepest_level < 7) { $deepest_level=7; }

														// get EIGTH LEVEL EMBEDDED level part information
														$d7_array_pos = $array_position;
														if ($d7_part['ex_num_subparts'] != $struct_not_set)
														{
															$d8_num_parts = $part_nice[$d7_array_pos]['ex_num_subparts'];
															for ($d8 = 0; $d8 < $d8_num_parts; $d8++)
															{
																$d8_part = $part_nice[$d7_array_pos]['subpart'][$d8];
																$d8_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1) .'.' .(string)($d5+1) .'.' .(string)($d6+1) .'.' .(string)($d7+1) .'.' .(string)($d8+1);
																$array_position++;
																$part_nice[$array_position] = pgw_msg_struct($d8_part, $d7_array_pos, $d8_mime_num, ($d8+1), $d8_num_parts, 8, $folder, $msgnum);
																if ($deepest_level < 8) { $deepest_level=8; }

																// get NINTH LEVEL EMBEDDED level part information
																$d8_array_pos = $array_position;
																if ($d8_part['ex_num_subparts'] != $struct_not_set)
																{
																	$d9_num_parts = $part_nice[$d8_array_pos]['ex_num_subparts'];
																	for ($d9 = 0; $d9 < $d9_num_parts; $d9++)
																	{
																		$d9_part = $part_nice[$d8_array_pos]['subpart'][$d9];
																		$d9_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1) .'.' .(string)($d5+1) .'.' .(string)($d6+1) .'.' .(string)($d7+1) .'.' .(string)($d8+1) .'.' .(string)($d9+1);
																		$array_position++;
																		$part_nice[$array_position] = pgw_msg_struct($d9_part, $d8_array_pos, $d9_mime_num, ($d9+1), $d9_num_parts, 9, $folder, $msgnum);
																		if ($deepest_level < 9) { $deepest_level=9; }

																		// get 10th LEVEL EMBEDDED level part information
																		$d9_array_pos = $array_position;
																		if ($d9_part['ex_num_subparts'] != $struct_not_set)
																		{
																			$d10_num_parts = $part_nice[$d9_array_pos]['ex_num_subparts'];
																			for ($d10 = 0; $d10 < $d10_num_parts; $d10++)
																			{
																				$d10_part = $part_nice[$d9_array_pos]['subpart'][$d10];
																				$d10_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1) .'.' .(string)($d5+1) .'.' .(string)($d6+1) .'.' .(string)($d7+1) .'.' .(string)($d8+1) .'.' .(string)($d9+1) .'.' .(string)($d10+1);
																				$array_position++;
																				$part_nice[$array_position] = pgw_msg_struct($d10_part, $d9_array_pos, $d10_mime_num, ($d10+1), $d10_num_parts, 10, $folder, $msgnum);
																				if ($deepest_level < 10) { $deepest_level=10; }

																				// get 11th LEVEL EMBEDDED level part information
																				$d10_array_pos = $array_position;
																				if ($d10_part['ex_num_subparts'] != $struct_not_set)
																				{
																					$d11_num_parts = $part_nice[$d10_array_pos]['ex_num_subparts'];
																					for ($d11 = 0; $d11 < $d11_num_parts; $d11++)
																					{
																						$d11_part = $part_nice[$d10_array_pos]['subpart'][$d11];
																						$d11_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1) .'.' .(string)($d5+1) .'.' .(string)($d6+1) .'.' .(string)($d7+1) .'.' .(string)($d8+1) .'.' .(string)($d9+1) .'.' .(string)($d10+1) .'.' .(string)($d11+1);
																						$array_position++;
																						$part_nice[$array_position] = pgw_msg_struct($d11_part, $d10_array_pos, $d11_mime_num, ($d11+1), $d11_num_parts, 11, $folder, $msgnum);
																						if ($deepest_level < 11) { $deepest_level=11; }


																						// get 12th LEVEL EMBEDDED level part information
																						$d11_array_pos = $array_position;
																						if ($d11_part['ex_num_subparts'] != $struct_not_set)
																						{
																							$d12_num_parts = $part_nice[$d11_array_pos]['ex_num_subparts'];
																							for ($d12 = 0; $d12 < $d12_num_parts; $d12++)
																							{
																								$d12_part = $part_nice[$d11_array_pos]['subpart'][$d12];
																								$d12_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1) .'.' .(string)($d5+1) .'.' .(string)($d6+1) .'.' .(string)($d7+1) .'.' .(string)($d8+1) .'.' .(string)($d9+1) .'.' .(string)($d10+1) .'.' .(string)($d11+1) .'.' .(string)($d12+1);
																								$array_position++;
																								$part_nice[$array_position] = pgw_msg_struct($d12_part, $d11_array_pos, $d12_mime_num, ($d12+1), $d12_num_parts, 12, $folder, $msgnum);
																								if ($deepest_level < 12) { $deepest_level=12; }
																							}
																						}
																					}
																				}
																			}
																		}
																	}
																}
															}
														}

													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}
	

// ---- Mime Characteristics Analysis  and more Attachments Detection  -----

	// Make an Array of Non-File Attachments like X-VCARD for use in the following loop
	$other_attach_types = array(
		'X-VCARD'
	);
	
	// ANALYSIS LOOP Part 1
	for ($i = 0; $i < count($part_nice); $i++)
	{
		// ------  Make a Keyword List for this Part  ------
		$part_nice[$i]['m_keywords'] = '';
		if ($part_nice[$i]['type'] != $struct_not_set)
		{
			$part_nice[$i]['m_keywords'] .= $part_nice[$i]['type'] .' ';
		}
		if ($part_nice[$i]['subtype'] != $struct_not_set)
		{
			$part_nice[$i]['m_keywords'] .= $part_nice[$i]['subtype'] .' ';
		}
		if ($part_nice[$i]['encoding'] != $struct_not_set)
		{
			$part_nice[$i]['m_keywords'] .= $part_nice[$i]['encoding'] .' ';
		}
		if ($part_nice[$i]['encoding'] != $struct_not_set)
		{
			$part_nice[$i]['m_keywords'] .= $part_nice[$i]['encoding'] .' ';
		}
		if ($part_nice[$i]['param_attribute'] != $struct_not_set)
		{
			$part_nice[$i]['m_keywords'] .= $part_nice[$i]['param_attribute'] .' ';
			if ($part_nice[$i]['param_attribute'] == 'charset')
			{
				$part_nice[$i]['m_keywords'] .= $part_nice[$i]['param_value'] .' ';
			}
		}
		if ($part_nice[$i]['ex_has_attachment'])
		{
			$part_nice[$i]['m_keywords'] .= 'ex_has_attachment' .' ';
		}
		$part_nice[$i]['m_keywords'] = trim($part_nice[$i]['m_keywords']);

		// ------  Test For Non-File Attachments like X-VCARD  ------
		// add any others that I missed to the $other_attach_types array above
		for ($oa = 0; $oa < count($oa); $oa++)
		{
			if (stristr($part_nice[$i]['m_keywords'], $other_attach_types[$oa]))
			{
				$part_nice[$i]['ex_has_attachment'] = True;
				$part_nice[$i]['ex_part_name'] = $other_attach_types[$oa];
				// add "ex_has_attachment" to keywords
				$prev_keywords = $part_nice[$i]['m_keywords'];
				if (!stristr($prev_keywords, 'ex_has_attachment'))
				{
					$part_nice[$i]['m_keywords'] .= 'ex_has_attachment' .' ';
				}
			}
		}

		// POSSIBLE VALUES FOR ['m_description'] ARE:
		//	container
		//	packagelist
		//	presentable/image
		//	attachment
		//	presentable

		// ------  Use That Keyword List To Make a "m_description"  ------
		if ((stristr($part_nice[$i]['m_keywords'], 'RFC822')) 
		|| (stristr($part_nice[$i]['m_keywords'], 'message')))
		{
			$part_nice[$i]['m_description'] = 'container';
		}
		elseif ((stristr($part_nice[$i]['m_keywords'], 'MIXED')) 
		|| (stristr($part_nice[$i]['m_keywords'], 'multipart'))
		|| (stristr($part_nice[$i]['m_keywords'], 'boundry')))
		{
			$part_nice[$i]['m_description'] = 'packagelist';
		}
		elseif ((stristr($part_nice[$i]['m_keywords'], 'base64')) 
		&& ((stristr($part_nice[$i]['m_keywords'], 'JPEG'))
		|| (stristr($part_nice[$i]['m_keywords'], 'GIF'))
		|| (stristr($part_nice[$i]['m_keywords'], 'PJPEG')) ) )
		{
			$part_nice[$i]['m_description'] = 'presentable/image';
		}
		elseif (stristr($part_nice[$i]['m_keywords'], 'ex_has_attachment')) 
		{
			$part_nice[$i]['m_description'] = 'attachment';
		}
		else
		{
			$part_nice[$i]['m_description'] = 'presentable';
		}
	}


// ---- Generate Mime Part Number and Attachments List Creation  -----
	$list_of_files = '';
	// ANALYSIS LOOP Part 2
	for ($j = 0; $j < count($part_nice); $j++)
	{
		// ---Mime Number Dumb
		//$new_mime_dumb = mime_number_dumb($part_nice, $j);
		//$part_nice[$j]['ex_mime_number_dumb'] = $new_mime_dumb;
		
		// ---Use Mime Number Dumb To Make ex_mime_number_smart
		$new_mime_dumb = $part_nice[$j]['ex_mime_number_dumb'];
		$part_nice[$j]['ex_mime_number_smart'] = mime_number_smart($part_nice, $j, $new_mime_dumb);

		// -----   Make Smart Mime Number THE PRIMARY MIME NUMBER we will use
		$part_nice[$j]['m_part_num_mime'] = $part_nice[$j]['ex_mime_number_smart'];

		// ---- make an URL and a Clickable Link to directly acces this part
		$click_info_serial = make_part_clickable($part_nice[$j], $folder, $msgnum);
		//$click_info = Array();
		$click_info = unserialize($click_info_serial);
		$part_nice[$j]['ex_part_href'] = $click_info[0];
		$part_nice[$j]['ex_part_clickable'] = $click_info[1];
		
		// ---- list_of_files is diaplayed in the summary at the top of the message page
		if ($part_nice[$j]['ex_has_attachment'])
		{
			if ((int)$part_nice[$j]['bytes'] > 100)
			{
				$att_size = ' ('. format_byte_size($part_nice[$j]['bytes']).')';
			}
			else
			{
				$att_size = '';
			}
			$list_of_files = $list_of_files . $part_nice[$j]['ex_part_clickable'] .$att_size .', ';
		}
	}
	// set up for use in the template
	if ($list_of_files != '')
	{
		// get rid of the last ", "
		$list_of_files = ereg_replace(",.$", "", $list_of_files);
		$t->set_var('list_of_files',$list_of_files);
		$t->parse('V_attach_list','B_attach_list');
	}
	else
	{
		$t->set_var('V_attach_list','');
	}

// ---- DEBUG: Show Information About Each Part  -----
	$show_debug_parts = False;
	//$show_debug_parts = True;
	
	if ($show_debug_parts)
	{
		// what's the count in the array?
		$max_parts = count($part_nice);
		
		$all_keys = Array();
		$all_keys = array_keys($part_nice);
		$str_keys = implode(', ',$all_keys);
		
		$crlf = "\r\n";
		$msg_body_info = '<pre>' .$crlf;
		$msg_body_info .= 'This message has '.$max_parts.' part(s)' .$crlf;
		$msg_body_info .= 'deepest_level: '.$deepest_level .$crlf;
		$msg_body_info .= 'Array Keys: '.array_keys_str($part_nice) .$crlf;
		$msg_body_info .= $crlf;
		for ($i = 0; $i < count($part_nice); $i++)
		{
			//$msg_body_info .= 'Information for primary part number '.$i .$crlf;
			$msg_body_info .= 'Part Number '. $part_nice[$i]['m_part_num_mime'] .$crlf;
			$msg_body_info .= 'Mime Number Dumb '. $part_nice[$i]['ex_mime_number_dumb'] .$crlf;
			$msg_body_info .= 'Mime Number Smart '. $part_nice[$i]['ex_mime_number_smart'] .$crlf;
			$msg_body_info .= 'Level iteration '. $part_nice[$i]['ex_level_iteration'] .'/'. $part_nice[$i]['ex_level_max_loops'] .$crlf;
			$msg_body_info .= 'Level Debth '. $part_nice[$i]['ex_level_debth'] .$crlf;
			$msg_body_info .= 'Flat Idx ['. $i .']' .$crlf;
			$msg_body_info .= 'ex_parent_flat_idx ['. $part_nice[$i]['ex_parent_flat_idx'] .']' .$crlf;
			$msg_body_info .= 'm_description: '. $part_nice[$i]['m_description'] .$crlf;
			$msg_body_info .= 'm_keywords: '. $part_nice[$i]['m_keywords'] .$crlf;
			$keystr = array_keys_str($part_nice[$i]);
			$msg_body_info .= 'Array Keys (len='.strlen($keystr).'): '.$keystr .$crlf;
			if ((isset($part_nice[$i]['m_level_total_parts']))
			&& ($part_nice[$i]['m_level_total_parts'] != $struct_not_set))
			{
				$msg_body_info .= 'm_level_total_parts: '. $part_nice[$i]['m_level_total_parts'] .$crlf;
			}
			$msg_body_info .= 'm_last_kid: '. $part_nice[$i]['m_last_kid'] .$crlf;
			if ($part_nice[$i]['type'] != $struct_not_set)
			{
				$msg_body_info .= 'type: '. $part_nice[$i]['type'] .$crlf;
			}
			if ($part_nice[$i]['encoding'] != $struct_not_set)
			{
				$msg_body_info .= 'encoding: '. $part_nice[$i]['encoding'] .$crlf;
			}
			if ($part_nice[$i]['subtype'] != $struct_not_set)
			{
				$msg_body_info .= 'subtype: '. $part_nice[$i]['subtype'] .$crlf;
			}
			if ($part_nice[$i]['description'] != $struct_not_set)
			{
				$msg_body_info .= 'description: '. $part_nice[$i]['description']  .$crlf;
			}
			if ($part_nice[$i]['id'] != $struct_not_set)
			{
				$msg_body_info .= 'id: '. $part_nice[$i]['id'] .$crlf;
			}
			if ($part_nice[$i]['lines'] != $struct_not_set)
			{
				$msg_body_info .= 'lines: '. $part_nice[$i]['lines'] .$crlf;
			}
			if ($part_nice[$i]['bytes'] != $struct_not_set)
			{
				$msg_body_info .= 'bytes: '. $part_nice[$i]['bytes'] .$crlf;
			}
			if ($part_nice[$i]['disposition'] != $struct_not_set)
			{
				$msg_body_info .= 'disposition: '. $part_nice[$i]['disposition'] .$crlf;
			}
			if ($part_nice[$i]['ex_num_param_pairs'] != $struct_not_set)
			{
				//$msg_body_info .= 'ex_num_param_pairs: '. $part_nice[$i]['ex_num_param_pairs'] .$crlf;
				$msg_body_info .= 'param_attribute: '. $part_nice[$i]['param_attribute'] .$crlf;
				$msg_body_info .= 'param_value: '. $part_nice[$i]['param_value']  .$crlf;
			}
			if ($part_nice[$i]['ex_num_subparts'] != $struct_not_set)
			{
				$msg_body_info .= 'ex_num_subparts: '. $part_nice[$i]['ex_num_subparts'] .$crlf;
				if (strlen($part_nice[$i]['m_part_num_mime']) > 2)
				{
					$msg_body_info .= 'subpart: '. serialize($part_nice[$i]['subpart']) .$crlf;
				}
			}
			if ($part_nice[$i]['ex_has_attachment'])
			{
				$msg_body_info .= '**ex_has_attachment**' .$crlf;
				$msg_body_info .= 'ex_part_name: '. $part_nice[$i]['ex_part_name'] .$crlf;
				//$msg_body_info .= 'ex_has_attachment: '. $part_nice[$i]['ex_has_attachment'] .$crlf;
			}
			$msg_body_info .= 'ex_part_href: '. $part_nice[$i]['ex_part_href'] .$crlf;
			$msg_body_info .= 'ex_part_clickable: '. $part_nice[$i]['ex_part_clickable'] .$crlf;
			$msg_body_info .= $crlf;
		}

		$msg_body_info .= '</pre>' .$crlf;
		$t->set_var('msg_body_info',$msg_body_info);
		$t->parse('V_debug_parts','B_debug_parts');
	}
	else
	{
		$t->set_var('V_debug_parts','');
	}

// -----  Message_Display Template Handles it from here  -------
	$t->set_var('theme_font',$phpgw_info['theme']['font']);
	$t->set_var('theme_th_bg',$phpgw_info['theme']['th_bg']);
	$t->set_var('theme_row_on',$phpgw_info['theme']['row_on']);

	// Force Echo Out Unformatted Text for email with 1 part which is a large text messages (in bytes) , such as a system replrt from cron
	// php (4.0.4pl1 last tested) and some imap servers (courier and uw-imap are confirmed) will time out retrieving this type of message
	$force_echo_size = 80000;
	$too_many_crlf = 18;

// -----  GET BODY AND SHOW MESSAGE  -------
	set_time_limit(120);
	for ($i = 0; $i < count($part_nice); $i++)
	{
		if (($part_nice[$i]['m_description'] == 'presentable')
		&& (stristr($part_nice[$i]['m_keywords'], 'HTML')))
		{

			// get the body
			$dsp = $phpgw->msg->fetchbody($mailbox, $msgnum, $part_nice[$i]['m_part_num_mime'], FT_INTERNAL);
			// is a blank part test necessary for html ???

			$title_text = lang("section").': '.$part_nice[$i]['m_part_num_mime'];
			//$display_str = $part_nice[$i]['type'].'/'.strtolower($part_nice[$i]['subtype']);
			$display_str = 'keywords: '.$part_nice[$i]['m_keywords']
				.' - '.format_byte_size(strlen($dsp));
			$t->set_var('title_text',$title_text);
			$t->set_var('display_str',$display_str);

			if (stristr($part_nice[$i]['m_keywords'], 'qprint'))
			{
				$dsp = $phpgw->msg->qprint($dsp);
			}


			// ---- strip html - FUTURE: only strip "bad" html
			if (strtoupper(lang("charset")) <> "BIG5")
			{
				$dsp = $phpgw->strip_html($dsp);
			}
			$dsp = ereg_replace( "^","<p>",$dsp);
			$dsp = ereg_replace( "\n","<br>",$dsp);
			$dsp = ereg_replace( "$","</p>", $dsp);


			//$t->set_var('message_body',"<tt>$dsp</tt>");
			$t->set_var('message_body',"$dsp");
			$t->parse('V_display_part','B_display_part', True);

			/*
			// get the body
			$dsp = $phpgw->msg->fetchbody($mailbox, $msgnum, $part_nice[$i]['m_part_num_mime'], FT_INTERNAL);

			if (stristr($part_nice[$i]['m_keywords'], 'qprint'))
			{
				$dsp = $phpgw->msg->qprint($dsp);
			}

			if (strtoupper(lang("charset")) <> "BIG5")
			{
				$dsp = $phpgw->strip_html($dsp);
			}
			$dsp = ereg_replace( "^","<p>",$dsp);
			$dsp = ereg_replace( "\n","<br>",$dsp);
			$dsp = ereg_replace( "$","</p>", $dsp);
			$dsp = make_clickable($dsp);

			$title_text = lang("section").': '.$part_nice[$i]['m_part_num_mime'];
			//$display_str = $part_nice[$i]['type'].'/'.strtolower($part_nice[$i]['subtype']);
			$display_str = $part_nice[$i]['m_keywords'];
			$t->set_var('title_text',$title_text);
			$t->set_var('display_str',$display_str);
			$t->parse('V_output_bound','B_output_bound');
			$v_msg_body = $v_msg_body . $t->get_var('V_output_bound');

			if (stristr($part_nice[$i]['m_keywords'], 'qprint'))
			{
				$dsp = '<tt>'.$dsp.'</tt>';
			}

			$t->set_var('message_body',$dsp);
			$t->parse('V_message_part','B_message_part');
			$v_msg_body = $v_msg_body . $t->get_var('V_message_part');
			*/

		}
		// do we Force Echo Out Unformatted Text ?
		elseif (($part_nice[$i]['m_description'] == 'presentable')
		&& (stristr($part_nice[$i]['m_keywords'], 'PLAIN'))
		&& ($d1_num_parts == 1)
		&& ($part_nice[$i]['m_part_num_mime'] === '1')
		&& ((int)$part_nice[$i]['bytes'] > $force_echo_size))
		{
			// output a blank message body, we'll use an alternate method below
			$t->set_var('V_display_part','');
			// -----  Finished With Message_Mail Template, Output It
			$t->pparse('out','T_message_main');
			
			// -----  Prepare a Table for this Echo Dump
			$title_text = '&nbsp;message: ';
			$t->set_var('title_text',$title_text);
			$display_str = 'keywords: '.$part_nice[$i]['m_keywords'].' - '.format_byte_size($part_nice[$i]['bytes'])
				.'; meets force_echo ('.format_byte_size($force_echo_size).') criteria';
			$t->set_var('display_str',$display_str);
			$t->parse('V_setup_echo_dump','B_setup_echo_dump');
			$t->set_var('V_done_echo_dump','');
			$t->pparse('out','T_message_echo_dump');
			// -----  Echo This Data Directly to the Client
			echo '<pre>';
			echo $phpgw->msg->fetchbody($mailbox, $msgnum, $part_nice[$i]['m_part_num_mime']);
			echo '</pre>';
			// -----  Close Table
			$t->set_var('V_setup_echo_dump','');
			$t->parse('V_done_echo_dump','B_done_echo_dump');
			$t->pparse('out','T_message_echo_dump');

			//  = = = =  = =======  CLEANUP AND EXIT PAGE ======= = = = = = =
			unset($part_nice);
			$phpgw->msg->close($mailbox); 
			$phpgw->common->phpgw_footer();
			exit;
		}
		elseif ($part_nice[$i]['m_description'] == 'presentable')
		{
			// ----- get the part from the server
			$dsp = $phpgw->msg->fetchbody($mailbox, $msgnum, $part_nice[$i]['m_part_num_mime']);
			$dsp = trim($dsp);
			
			// ----- when to skip showing a part (i.e. blank part - no alpha chars)
			$skip_this_part = False;
			//if (((int)$part_nice[$i]['bytes'] == 3)  && ((int)$part_nice[$i]['lines'] == 1))
			if (strlen($dsp) < 3)
			{
				$skip_this_part = True;
			}
			//if (strlen($dsp) < 3)  //if (strlen(trim($dsp)) > 2)  //if (strlen($dsp) > 2)

			// ----- show the part 
			if ($skip_this_part == False)
			{		
				if (stristr($part_nice[$i]['m_keywords'], 'qprint'))
				{
					$dsp = $phpgw->msg->qprint($dsp);
					$tag = "tt";
				}

				//    normalize line breaks to rfc822 CRLF
				//   $dsp = str_replace("=\r\n","\r\n",$dsp);
				$dsp = ereg_replace("\r\n", "\n", $dsp);
				$dsp = ereg_replace("\r", "\n", $dsp);
				$dsp = ereg_replace("\n", "\r\n", $dsp);
				if (strtoupper(lang("charset")) <> "BIG5")
				{
					$dsp = $phpgw->strip_html($dsp);
				}
				$dsp = make_clickable($dsp);

				/*// THIS NEEDS TO BE SMARTER
				// how many "\r\n\r\n" do we have? too_many was set above
				$crlf_report = '';
				$excessive_crlf = explode("\r\n\r\n", $dsp);
				if ((is_array($excessive_crlf))
				&& (count($excessive_crlf) > $too_many_crlf))
				{
					$dsp = ereg_replace("\r\n\r\n", "\r\n", $dsp);
					$crlf_report = '; CRLF > ' .$too_many_crlf. ' so compressed';
				}
				*/

				// (OPT 1) THIS WILL DISPLAY UNFORMATTED TEXT (faster)
				// enforce HARD WRAP
				//$max_line_chars = 100;
				//$dsp = '<pre>'.$dsp.'</pre>';

				// (OPT 2) THIS CONVERTS UNFORMATTED TEXT TO *VERY* SIMPLE HTML - adds only <br>
				$dsp = ereg_replace("\r\n","<br>",$dsp);
				// add a line after the last line of the message
				$dsp = $dsp .'<br><br>';

				// prepare the message sep
				if ($d1_num_parts > 1)
				{
					$title_text = lang("section").': '.$part_nice[$i]['m_part_num_mime'];
				}
				else
				{
					$title_text = '&nbsp;message: ';
				}
				$t->set_var('title_text',$title_text);
				$display_str = 'keywords: '.$part_nice[$i]['m_keywords']
					.' - '.format_byte_size(strlen($dsp));
					//.$crlf_report;
				$t->set_var('display_str',$display_str);

				$t->set_var('message_body',$dsp);
				$t->parse('V_display_part','B_display_part', True);

				/*
				// ------- Previous Method
				// get the part
				$dsp = $phpgw->msg->fetchbody($mailbox, $msgnum, $part_nice[$i]['m_part_num_mime']);

				// prepare the part
				if (strtoupper(lang("charset")) <> "BIG5")
				{
					$dsp = $phpgw->strip_html($dsp);
				}
				
				// Thanks to Omer Uner Guclu <oquclu@superonline.com> for figuring out
				// a better way to do message wrapping
				$dsp = ereg_replace( "^","<p>",$dsp);
				$dsp = ereg_replace( "\n","<br>",$dsp);
				$dsp = ereg_replace( "$","</p>", $dsp);
				$dsp = make_clickable($dsp);
				*/
			}
		}
		elseif ($part_nice[$i]['m_description'] == 'presentable/image')
		{
			$title_text = lang("section").': '.$part_nice[$i]['m_part_num_mime'];
			$display_str = decode_header_string($part_nice[$i]['ex_part_name'])
				.' - ' .format_byte_size((int)$part_nice[$i]['bytes']) 
				.' - keywords: ' .$part_nice[$i]['m_keywords'];
			$t->set_var('title_text',$title_text);
			$t->set_var('display_str',$display_str);
			
			$img_inline = '<img src="'.$part_nice[$i]['ex_part_href'].'">';
			$t->set_var('message_body',$img_inline);
			$t->parse('V_display_part','B_display_part', True);
		}
		elseif ($part_nice[$i]['m_description'] == 'attachment')
		{
			$title_text = lang("section").': '.$part_nice[$i]['m_part_num_mime'];
			$display_str = 'keywords: ' .$part_nice[$i]['m_keywords'];
			$t->set_var('title_text',$title_text);
			$t->set_var('display_str',$display_str);
			
			/*
			if (($part_nice[$i]['encoding'] == 'base64')
			|| ($part_nice[$i]['encoding'] == '8bit'))
			{
				$dsp = $phpgw->msg->fetchbody($mailbox, $msgnum, $part_nice[$i]['m_part_num_mime'], FT_INTERNAL);
					//$dsp = $phpgw->msg->fetchbody($mailbox, $msgnum, $part_nice[$i]['m_part_num_mime']);
					//$processed_msg_body = $processed_msg_body . base64_decode($dsp) .'<br>' ."\r\n";
				$att_size =  format_byte_size(strlen($dsp));
				$msg_text = $msg_text .'&nbsp;&nbsp; size: '.$att_size;
			}
			*/
			$msg_text = '&nbsp;&nbsp; <strong>ATTACHENT:</strong>'
				.'&nbsp;&nbsp; '.$part_nice[$i]['ex_part_clickable']
				.'&nbsp;&nbsp; size: '.format_byte_size((int)$part_nice[$i]['bytes'])
				.'<br><br>';
			
			$t->set_var('message_body',$msg_text);
			$t->parse('V_display_part','B_display_part', True);
		}
		elseif (($part_nice[$i]['m_description'] != 'container')
		&& ($part_nice[$i]['m_description'] != 'packagelist'))
		{
			$title_text = lang("section").': '.$part_nice[$i]['m_part_num_mime'];
			$display_str = decode_header_string($part_nice[$i]['ex_part_name'])
				.' - keywords: ' .$part_nice[$i]['m_keywords'];
			$t->set_var('title_text',$title_text);
			$t->set_var('display_str',$display_str);
			
			$msg_text = '';
			// UNKNOWN DATA
			$msg_text = $msg_text .'<br><strong>ERROR: Unknown Message Data</strong><br>';
			if ($part_nice[$i]['encoding'] == 'base64')
			{
				$dsp = $phpgw->msg->fetchbody($mailbox, $msgnum, $part_nice[$i]['m_part_num_mime'], FT_INTERNAL);
					//$dsp = $phpgw->msg->fetchbody($mailbox, $msgnum, $part_nice[$i]['m_part_num_mime']);
					//$processed_msg_body = $processed_msg_body . base64_decode($dsp) .'<br>' ."\r\n";
				$msg_text = $msg_text . 'actual part size: ' .strlen($dsp);
			}
			$t->set_var('message_body',$msg_text);
			$t->parse('V_display_part','B_display_part', True);
		}
	}
	set_time_limit(0);

	/* // IS THIS STILL USED ???????
	if($application)
	{
		if(strstr($msgtype,'"; Id="'))
		{
			$msg_type = explode(';',$msgtype);
			$id_array = explode('=',$msg_type[2]);
			$calendar_id = intval(substr($id_array[1],1,strlen($id_array[1])-2));

			echo '<tr><td align="center">';
			$phpgw->common->hook_single('email',$application);
			echo '</td></tr>';
		}
	} */

	$t->pparse('out','T_message_main');

	// CLEANUP
	unset($part_nice);

	$phpgw->msg->close($mailbox); 
	$phpgw->common->phpgw_footer();
?>
