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

// ----  Fill Some Important Variables  -----
	$svr_image_dir = PHPGW_IMAGES_DIR;
	$image_dir = PHPGW_IMAGES;
	$sm_envelope_img = $phpgw->msg->img_maketag($image_dir.'/sm_envelope.gif',"Add to address book","8","10","0");
	$default_sorting = $phpgw_info['user']['preferences']['email']['default_sorting'];
	$struct_not_set = $phpgw->msg->not_set;

// ----  General Information about The Message  -----
	$msg = $phpgw->msg->phpgw_header('');
	$struct = $phpgw->msg->phpgw_fetchstructure('');
	$folder_info = array();
	$folder_info = $phpgw->msg->folder_status_info();
	$totalmessages = $folder_info['number_all'];


	$subject = $phpgw->msg->get_subject($msg,'');
	$message_date = $phpgw->common->show_date($msg->udate);

	#set_time_limit(0);

// ----  Special X-phpGW-Type Message Flag  -----
	// is this still a planned feature?
	$application = '';
	$msgtype = $phpgw->msg->phpgw_get_flag('X-phpGW-Type');
	
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

// ----  What Folder To Return To  -----
        $lnk_goback_folder = $phpgw->msg->href_maketag(
		$phpgw->link('/email/index.php',
			'folder='.$phpgw->msg->prep_folder_out('')
			.'&sort='.$phpgw->msg->sort
			.'&order='.$phpgw->msg->order
			.'&start='.$phpgw->msg->start),
		$phpgw->msg->get_folder_short($phpgw->msg->folder));

// ----  Go To Previous Message Handling  -----
	// NOTE: THIS NEEDS FIXING
	if ($phpgw->msg->msgnum != 1 
	|| ($default_sorting == 'new_old' && $phpgw->msg->msgnum != $totalmeesages))
	{
		if ($default_sorting == 'new_old')
		{
			$pm = $phpgw->msg->msgnum + 1;
		}
		else
		{
			$pm = $phpgw->msg->msgnum - 1;
		}

		if ($default_sorting == 'new_old' 
		&& ($phpgw->msg->msgnum == $totalmessages && $phpgw->msg->msgnum != 1 || $totalmessages == 1))
		{
			$ilnk_prev_msg = $phpgw->msg->img_maketag($svr_image_dir.'/left-grey.gif',"No Previous Message",'','','0');
		}
		else
		{
			$prev_msg_link = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/message.php',
				'folder='.$phpgw->msg->prep_folder_out('')
				.'&msgnum='.$pm
				.'&sort='.$phpgw->msg->sort
				.'&order='.$phpgw->msg->order
				.'&start='.$phpgw->msg->start);
			$prev_msg_img = $phpgw->msg->img_maketag($svr_image_dir.'/left.gif',"Previous Message",'','','0');
			$ilnk_prev_msg = $phpgw->msg->href_maketag($prev_msg_link,$prev_msg_img);
		}
	}
	else
	{
		$ilnk_prev_msg = $phpgw->msg->img_maketag($svr_image_dir.'/left-grey.gif',"No Previous Message",'','','0');
	}

// ----  Go To Next Message Handling  -----
	if ($phpgw->msg->msgnum < $totalmessages
	|| ($default_sorting == 'new_old' && $phpgw->msg->msgnum != 1))
	{
		if ($default_sorting == 'new_old')
		{
			$nm = $phpgw->msg->msgnum - 1;
		}
		else
		{
			$nm = $phpgw->msg->msgnum + 1;
		}

		if (($default_sorting == 'new_old')
		&& ($phpgw->msg->msgnum == 1)
		&& ($totalmessages != $phpgw->msg->msgnum))
		{
			$ilnk_next_msg = $phpgw->msg->img_maketag($svr_image_dir.'/right-grey.gif',"No Next Message",'','','0');
		}
		else
		{
			$next_msg_link = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/message.php',
				'folder='.$phpgw->msg->prep_folder_out('')
				.'&msgnum='.$nm
				.'&sort='.$phpgw->msg->sort
				.'&order='.$phpgw->msg->order
				.'&start='.$phpgw->msg->start);
			$next_msg_img = $phpgw->msg->img_maketag($svr_image_dir.'/right.gif',"Next Message",'','','0');
			$ilnk_next_msg = $phpgw->msg->href_maketag($next_msg_link,$next_msg_img);
		}
	}
	else
	{
		$ilnk_next_msg = $phpgw->msg->img_maketag($svr_image_dir.'/right-grey.gif',"No Next Message",'','','0');
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
	if (!$msg->from)
	{
		// no header info about this sender is available
		$from_data_final = lang('Undisclosed Sender');
	}
	else
	{
		$from = $msg->from[0];
		//a typical email address have 2 properties: (1) rfc2822 addr_spec  (user@some.com)  and (2) maybe a descriptive string
		// get (1) - the from rfc2822 addr_spec
		$from_plain = $from->mailbox.'@'.$from->host;
		// get (2) the associated descriptive string. if supplied, the header usually looks like this: "personal name" <some@where.com>
		// that associasted string, called "personal" here, usally has the persons full name
		if (!isset($from->personal) || (!$from->personal))
		{
			// there is no "personal" info available, just fill this with the standard email addr
			$from_personal = $from_plain;
		}
		else
		{
			$from_personal = $phpgw->msg->decode_header_string($from->personal);
		}
		// display "From" according to user preferences
		if (isset($phpgw_info['user']['preferences']['email']['show_addresses'])
		&& ($phpgw_info['user']['preferences']['email']['show_addresses'] != 'none')
		&& ($from_personal != $from_plain))
		{
			// user wants to see "personal" info AND the plain address, and we have both available to us
			$from_extra_info = " ".'('.$from_plain.')'." ";
		}
		else
		{
			//user  want to see the "personal" ONLY (no plain address) OR we do not have any "personal" info to show
			$from_extra_info = ' ';
		}

		// first text in the "from" table data, AND click on it to compose a new, blank email to this email address
		$from_and_compose_link = 
			$phpgw->msg->href_maketag($phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php',
				'folder='.$phpgw->msg->prep_folder_out('').'&to='.urlencode($from_plain).'&personal='.urlencode($from_personal)),
			$from_personal);
		// click on the little envelope image to add this person/address to your address book
		$from_addybook_add = 
			$phpgw->msg->href_maketag($phpgw->link('/addressbook/add.php',
				'add_email='.urlencode($from_plain).'&name='.urlencode($from_personal)
				.'&referer='.urlencode($PHP_SELF.'?'.$QUERY_STRING)),
			$sm_envelope_img);
		
		// assemble the "From" data string  (note to_extra_info also handles the spacing)
		$from_data_final = $from_and_compose_link .$from_extra_info .$from_addybook_add;
	}

	$t->set_var('from_data_final',$from_data_final);


// ----  To:  Message Data  -----
	if (!$msg->to)
	{
		$to_data_final = lang('Undisclosed Recipients');
	}
	else
	{
		for ($i = 0; $i < count($msg->to); $i++)
		{
			$topeople = $msg->to[$i];
			$to_plain = $topeople->mailbox.'@'.$topeople->host;
			if ((!isset($topeople->personal)) || (!$topeople->personal))
			{
				$to_personal = $to_plain;
			}
			else
			{
				$to_personal = $phpgw->msg->decode_header_string($topeople->personal);
			}
			if (($phpgw_info['user']['preferences']['email']['show_addresses'] != 'none')
			&& ($to_personal != $to_plain))
			{
				$to_extra_info = " ".'('.$to_plain.')'." ";
			}
			else
			{
				$to_extra_info = ' ';
			}

			$to_real_name = $phpgw->msg->href_maketag(
				$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php',
					'folder='.$phpgw->msg->prep_folder_out('').'&to='.urlencode($to_plain).'&personal='.urlencode($to_personal)),
				$to_personal);
			$to_addybook_add = $phpgw->msg->href_maketag(
				$phpgw->link('/addressbook/add.php',
					'add_email='.urlencode($to_plain).'&name='.urlencode($to_personal)
					.'&referer='.urlencode($PHP_SELF.'?'.$QUERY_STRING)),
				$sm_envelope_img);
			// assemble the string and store for later use (note to_extra_info also handles the spacing)
			$to_data_array[$i] = $to_real_name .$to_extra_info .$to_addybook_add;
		}
		// throw a spacer comma in between addresses, if more than one
		$to_data_final = implode(', ',$to_data_array);
	}

	$t->set_var('to_data_final',$to_data_final);


// ----  Cc:  Message Data  -----
	if (isset($msg->cc) && count($msg->cc) > 0)
	{
		for ($i = 0; $i < count($msg->cc); $i++)
		{
			$ccpeople = $msg->cc[$i];
			$cc_plain = $ccpeople->mailbox.'@'.$ccpeople->host;
			if ((!isset($ccpeople->personal)) || (!$ccpeople->personal))
			{
				$cc_personal = $cc_plain;
			}
			else
			{
				$cc_personal = $phpgw->msg->decode_header_string($ccpeople->personal);
			}
			if (($phpgw_info['user']['preferences']['email']['show_addresses'] != 'none')
			&& ($cc_personal != $cc_plain))
			{
				$cc_extra_info = " ".'('.$cc_plain.')'." ";
			}
			else
			{
				$cc_extra_info = ' ';
			}
			$cc_real_name = $phpgw->msg->href_maketag($phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php',
					'folder='.$phpgw->msg->prep_folder_out('')
					.'&to='.urlencode($cc_plain).'&personal='.urlencode($cc_personal)),
				$cc_personal);

			$cc_addybook_add = $phpgw->msg->href_maketag(
				$phpgw->link('/addressbook/add.php',
					'add_email='.urlencode($cc_plain).'&name='.urlencode($cc_personal).'&referer='.urlencode($PHP_SELF.'?'.$QUERY_STRING)),
				$sm_envelope_img);

			// assemble the string and store for later use
			$cc_data_array[$i] = $cc_real_name .$cc_extra_info .$cc_addybook_add;
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
			$part_nice[$index_flat_array] = pgw_msg_struct($part[$i], ($i+1), $loops, $debth, $folder, $phpgw->msg->msgnum);

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

	//echo '<br>var struct serialized:<br>' .serialize($struct) .'<br><br>';
	//echo '<br>var struct->parts serialized:<br>' .serialize($struct->parts) .'<br><br>';
	//echo '<br>var count(struct->parts): [' .count($struct->parts) .']<br><br>';


	// get INITIAL part structure / array from the fetchstructure  variable
	if ((!isset($struct->parts[0]) || (!$struct->parts[0])))
	{
		$part[0] = $struct;
	}
	else
	{
		$part = $struct->parts;
	}

	//echo '<br>INITIAL var part serialized:<br>' .serialize($part) .'<br><br>';
	

	$d1_num_parts = count($part);
	
	$part_nice = Array();

	// get PRIMARY level part information
	$deepest_level=0;
	$array_position = -1;  // it will be advanced to 0 before its used
	for ($d1 = 0; $d1 < $d1_num_parts; $d1++)
	{
		$array_position++;
		$d1_mime_num = (string)($d1+1);
		$part_nice[$array_position] = pgw_msg_struct($part[$d1], $struct_not_set, $d1_mime_num, ($d1+1), $d1_num_parts, 1, $phpgw->msg->folder, $phpgw->msg->msgnum);
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
				$part_nice[$array_position] = pgw_msg_struct($d2_part, $d1_array_pos, $d2_mime_num, ($d2+1), $d2_num_parts, 2, $phpgw->msg->folder, $phpgw->msg->msgnum);
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
						$part_nice[$array_position] = pgw_msg_struct($d3_part, $d2_array_pos, $d3_mime_num, ($d3+1), $d3_num_parts, 3, $phpgw->msg->folder, $phpgw->msg->msgnum);
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
								$part_nice[$array_position] = pgw_msg_struct($d4_part, $d3_array_pos, $d4_mime_num, ($d4+1), $d4_num_parts, 4, $phpgw->msg->folder, $phpgw->msg->msgnum);
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
										$part_nice[$array_position] = pgw_msg_struct($d5_part, $d4_array_pos, $d5_mime_num, ($d5+1), $d5_num_parts, 5, $phpgw->msg->folder, $phpgw->msg->msgnum);
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
												$part_nice[$array_position] = pgw_msg_struct($d6_part, $d5_array_pos, $d6_mime_num, ($d6+1), $d6_num_parts, 6, $phpgw->msg->folder, $phpgw->msg->msgnum);
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
														$part_nice[$array_position] = pgw_msg_struct($d7_part, $d6_array_pos, $d7_mime_num, ($d7+1), $d7_num_parts, 7, $phpgw->msg->folder, $phpgw->msg->msgnum);
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
																$part_nice[$array_position] = pgw_msg_struct($d8_part, $d7_array_pos, $d8_mime_num, ($d8+1), $d8_num_parts, 8, $phpgw->msg->folder, $phpgw->msg->msgnum);
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
																		$part_nice[$array_position] = pgw_msg_struct($d9_part, $d8_array_pos, $d9_mime_num, ($d9+1), $d9_num_parts, 9, $phpgw->msg->folder, $phpgw->msg->msgnum);
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
																				$part_nice[$array_position] = pgw_msg_struct($d10_part, $d9_array_pos, $d10_mime_num, ($d10+1), $d10_num_parts, 10, $phpgw->msg->folder, $phpgw->msg->msgnum);
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
																						$part_nice[$array_position] = pgw_msg_struct($d11_part, $d10_array_pos, $d11_mime_num, ($d11+1), $d11_num_parts, 11, $phpgw->msg->folder, $phpgw->msg->msgnum);
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
																								$part_nice[$array_position] = pgw_msg_struct($d12_part, $d11_array_pos, $d12_mime_num, ($d12+1), $d12_num_parts, 12, $phpgw->msg->folder, $phpgw->msg->msgnum);
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
	//$other_attach_types = array(
	//	'X-VCARD'
	//);
	
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
		//if ($part_nice[$i]['description'] != $struct_not_set)
		//{
		//	$part_nice[$i]['m_keywords'] .= $part_nice[$i]['description'] .' ';
		//}
		//if ($part_nice[$i]['ex_num_param_pairs'] > 0)
		//{
		//	for ($p = 0; $p < $part_nice[$i]['ex_num_param_pairs']; $p++)
		//	{
		//		$part_nice[$i]['m_keywords'] .= $part_nice[$i]['params'][$p]['attribute'].'='.$part_nice[$i]['params'][$p]['value'].' ';
		//	}
		//}
		if ($part_nice[$i]['ex_attachment'])
		{
			$part_nice[$i]['m_keywords'] .= 'ex_attachment' .' ';
		}
		//$part_nice[$i]['m_keywords'] = trim($part_nice[$i]['m_keywords']);

		/* B0RKED --(any part with a name is considered an attachment, so this is not needed)
		// ------  Test For Non-File Attachments like X-VCARD  ------
		// add any others that I missed to the $other_attach_types array above
		for ($oa = 0; $oa < count($other_attach_types); $oa++)
		{
			if (stristr($part_nice[$i]['m_keywords'], $other_attach_types[$oa]))
			{
				$part_nice[$i]['ex_attachment'] = True;
				$part_nice[$i]['ex_part_name'] = $other_attach_types[$oa];
				// add "ex_attachment" to keywords
				$prev_keywords = $part_nice[$i]['m_keywords'];
				if (!stristr($prev_keywords, 'ex_attachment'))
				{
					$part_nice[$i]['m_keywords'] .= 'ex_attachment' .' ';
				}
			}
		}
		*/

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
		elseif (stristr($part_nice[$i]['m_keywords'], 'ex_attachment')) 
		{
			$part_nice[$i]['m_description'] = 'attachment';
		}
		else
		{
			$part_nice[$i]['m_description'] = 'presentable';
		}
		
		// initialize and prepare for the following mime exceptions code
		$part_nice[$i]['m_html_related_kids'] = False;
		$parent_idx = $part_nice[$i]['ex_parent_flat_idx'];
		
		// ------  Exceptions for Less-Standart Subtypes  ------
		//"m_description" set above will work *most all* the time. However newer standards
		// are encouraged to make use of the "subtype" param, not create new "type"s 
		// the following "multipart/SUBTYPES" should be treated as
		// "container" instead of "packagelist"
		if ($part_nice[$i]['m_description'] == 'packagelist')
		{
			// Exception: multipart/APPLEDOUBLE  (ex. mac thru X.400 gateway)
			// treat as "container", not as "packagelist"
			if (($part_nice[$i]['type'] == 'multipart')
			&& ($part_nice[$i]['subtype'] == 'appledouble'))
			{
				$part_nice[$i]['m_description'] = 'container';
				$part_nice[$i]['m_keywords'] .= 'Force Container' .' ';
			}
			// Exception: multipart/RELATED (ex. Outl00k "stationary" email)
			// treat it's *child* multipart/alternative as "container", not as "packagelist"
			elseif (($part_nice[$i]['ex_level_debth'] > 1)  // does not apply to level1, b/c level1 has no parent
			&& ($part_nice[$i]['type'] == 'multipart')
			&& ($part_nice[$i]['subtype'] == 'alternative')
			&& ($part_nice[$parent_idx]['type'] == 'multipart')
			&& ($part_nice[$parent_idx]['subtype'] == 'related'))
			{
				$part_nice[$i]['m_description'] = 'container';
				$part_nice[$i]['m_keywords'] .= 'Force Container' .' ';
				// SET THIS FLAG: then, in presentation loop, see if a HTML part 
				// has a parent with this flag - if so, replace "id" reference(s) with 
				// http... mime reference(s). Example: MS Stationary mail's image background
				$part_nice[$i]['m_html_related_kids'] = True;
			}
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
		$click_info = make_part_clickable($part_nice[$j], $phpgw->msg->folder, $phpgw->msg->msgnum);
		$part_nice[$j]['ex_part_href'] = $click_info['part_href'];
		$part_nice[$j]['ex_part_clickable'] = $click_info['part_clickable'];

		// ---- list_of_files is diaplayed in the summary at the top of the message page
		if ($part_nice[$j]['ex_attachment'])
		{
			if ((int)$part_nice[$j]['bytes'] > 100)
			{
				$att_size = ' ('. $phpgw->msg->format_byte_size($part_nice[$j]['bytes']).')';
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

// ----  Reply to First Presentable Part  (needed for Reply, ReplyAll, and Forward below)  -----
	$first_presentable = '';
	// what's the first presentable part?
	for ($i = 0; $i < count($part_nice); $i++)
	{
		if (($part_nice[$i]['m_description'] == 'presentable')
		&& ($first_presentable == '')
		&& ($part_nice[$i]['bytes'] > 5))
		{
			$first_presentable = '&part_no='.$part_nice[$i]['m_part_num_mime'];
			// and if it is qprint then we must decode in the reply process
			if (stristr($part_nice[$i]['m_keywords'], 'qprint'))
			{
				$first_presentable = $first_presentable .'&encoding=qprint';
			}
			break;
		}
	}
	/*
	// FUTURE: Forward needs entirely different handling
	if ($deepest_level == 1)
	{
		$fwd_proc = 'pushdown';
	}
	else
	{
		$fwd_proc = 'encapsulate';
	}
	*/
	$fwd_proc = 'encapsulate';
	
// ----  Images and Hrefs For Reply, ReplyAll, Forward, and Delete  -----
        $reply_img = $phpgw->msg->img_maketag($image_dir.'/sm_reply.gif',lang('reply'),'19','26','0');
	$reply_url = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php',
				'action=reply&folder='.$phpgw->msg->prep_folder_out('')
				.'&msgnum='.$phpgw->msg->msgnum .$first_presentable);
	$ilnk_reply = $phpgw->msg->href_maketag($reply_url, $reply_img);

        $replyall_img = $phpgw->msg->img_maketag($image_dir .'/sm_reply_all.gif',lang('reply all'),"19","26",'0');
	$replyall_url = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php','action=replyall&folder='.$phpgw->msg->prep_folder_out('').'&msgnum='.$phpgw->msg->msgnum .$first_presentable);
	$ilnk_replyall = $phpgw->msg->href_maketag($replyall_url, $replyall_img);

	$forward_img = $phpgw->msg->img_maketag($image_dir .'/sm_forward.gif',lang('forward'),"19","26",'0');
	$forward_url =  $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php','action=forward&folder='.$phpgw->msg->prep_folder_out('').'&msgnum='.$phpgw->msg->msgnum .'&fwd_proc='.$fwd_proc .$first_presentable);
	$ilnk_forward = $phpgw->msg->href_maketag($forward_url, $forward_img);

	$delete_img = $phpgw->msg->img_maketag($image_dir .'/sm_delete.gif',lang('delete'),"19","26",'0');
	$delete_url = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/action.php','what=delete&folder='.$phpgw->msg->prep_folder_out('').'&msgnum='.$phpgw->msg->msgnum);
	$ilnk_delete = $phpgw->msg->href_maketag($delete_url, $delete_img);

	$t->set_var('theme_font',$phpgw_info['theme']['font']);
	$t->set_var('reply_btns_bkcolor',$phpgw_info['theme']['em_folder']);
	$t->set_var('reply_btns_text',$phpgw_info['theme']['em_folder_text']);
	$t->set_var('lnk_goback_folder',$lnk_goback_folder);
	$t->set_var('ilnk_reply',$ilnk_reply);
	$t->set_var('ilnk_replyall',$ilnk_replyall);
	$t->set_var('ilnk_forward',$ilnk_forward);
	$t->set_var('ilnk_delete',$ilnk_delete);


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

		//$msg_raw_headers = $phpgw->dcom->fetchheader($phpgw->msg->mailsvr_stream, $phpgw->msg->msgnum);
		$msg_raw_headers = $phpgw->msg->phpgw_fetchheader('');
		$msg_raw_headers = $phpgw->msg->htmlspecialchars_encode($msg_raw_headers);
		
		$crlf = "\r\n";
		$msg_body_info = '<pre>' .$crlf;
		$msg_body_info .= 'Top Level Headers:' .$crlf;
		$msg_body_info .= $msg_raw_headers .$crlf;
		$msg_body_info .= $crlf;
		
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
			
			//$keystr = array_keys_str($part_nice[$i]);
			//$msg_body_info .= 'Array Keys (len='.strlen($keystr).'): '.$keystr .$crlf;
			
			if ((isset($part_nice[$i]['m_level_total_parts']))
			&& ($part_nice[$i]['m_level_total_parts'] != $struct_not_set))
			{
				$msg_body_info .= 'm_level_total_parts: '. $part_nice[$i]['m_level_total_parts'] .$crlf;
			}
			if ($part_nice[$i]['type'] != $struct_not_set)
			{
				$msg_body_info .= 'type: '. $part_nice[$i]['type'] .$crlf;
			}
			if ($part_nice[$i]['subtype'] != $struct_not_set)
			{
				$msg_body_info .= 'subtype: '. $part_nice[$i]['subtype'] .$crlf;
			}
			if ($part_nice[$i]['m_html_related_kids'])
			{
				$msg_body_info .= '*m_html_related_kids: True*' .$crlf;
			}
			if ($part_nice[$i]['encoding'] != $struct_not_set)
			{
				$msg_body_info .= 'encoding: '. $part_nice[$i]['encoding'] .$crlf;
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
			if ($part_nice[$i]['ex_num_param_pairs'] > 0)
			{
				for ($p = 0; $p < $part_nice[$i]['ex_num_param_pairs']; $p++)
				{
					$msg_body_info .= 'params['.$p.']: '.$part_nice[$i]['params'][$p]['attribute'].'='.$part_nice[$i]['params'][$p]['value'] .$crlf;
				}

				/*
				//$msg_body_info .= 'ex_num_param_pairs: '. $part_nice[$i]['ex_num_param_pairs'] .$crlf;
				$msg_body_info .= 'param_attribute: '. $part_nice[$i]['param_attribute'] .$crlf;
				$msg_body_info .= 'param_value: '. $part_nice[$i]['param_value']  .$crlf;
				$msg_body_info .= 'param_2_attribute: '. $part_nice[$i]['param_2_attribute'] .$crlf;
				$msg_body_info .= 'param_2_value: '. $part_nice[$i]['param_2_value']  .$crlf;
				*/
			}
			if ($part_nice[$i]['ex_num_subparts'] != $struct_not_set)
			{
				$msg_body_info .= 'ex_num_subparts: '. $part_nice[$i]['ex_num_subparts'] .$crlf;
				if (strlen($part_nice[$i]['m_part_num_mime']) > 2)
				{
					$msg_body_info .= 'subpart: '. serialize($part_nice[$i]['subpart']) .$crlf;
				}
			}
			if ($part_nice[$i]['ex_attachment'])
			{
				$msg_body_info .= '**ex_attachment**' .$crlf;
				$msg_body_info .= 'ex_part_name: '. $part_nice[$i]['ex_part_name'] .$crlf;
				//$msg_body_info .= 'ex_attachment: '. $part_nice[$i]['ex_attachment'] .$crlf;
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

// -----  Pass part_nice into $phpgw_info['flags']  for Temporary Storage  --------
	/*
	$phpgw_info["user"]["preferences"]
	$phpgw_info['flags']['part_nice_serial'] = serialize($part_nice);
	$phpgw_info['flags']['part_nice_msgnum'] = $phpgw->msg->msgnum;
	$phpgw_info['flags']['part_nice_folder'] = $folder;
	*/
	

// -----  Message_Display Template Handles it from here  -------
	$t->set_var('theme_font',$phpgw_info['theme']['font']);
	$t->set_var('theme_th_bg',$phpgw_info['theme']['th_bg']);
	$t->set_var('theme_row_on',$phpgw_info['theme']['row_on']);

	// Force Echo Out Unformatted Text for email with 1 part which is a large text messages (in bytes) , such as a system replrt from cron
	// php (4.0.4pl1 last tested) and some imap servers (courier and uw-imap are confirmed) will time out retrieving this type of message
	$force_echo_size = 20000;
	//$too_many_crlf = 18;

// -----  Unknown and/or Unrecognized MIME subtypes get flagged HERE  -------
	// ### TEMP HACK FIX ###
	// RFC1892 "The  Multipart/Report Content Type"  (1996)
	//	requires a MIME sub-part "Message/delivery-status" which is INCONSISTANT
	//	with later MIME docs rfc2045-2049 in its use of the type "Message" in this case
	//	(this is my opinion - Angles)
	// flag "multipart/report" as unsupported
	
	// initialize
	$unsupported['finding'] = False;
	$unsupported['user_info'] = '';

	//$support_test_struct = $phpgw->dcom->fetchstructure($phpgw->msg->mailsvr_stream, $phpgw->msg->msgnum);
	$support_test_struct = $phpgw->msg->phpgw_fetchstructure('');
	$support_test_struct_nice = pgw_msg_struct($support_test_struct, $struct_not_set, '1', 1, 1, 1, $phpgw->msg->folder, $phpgw->msg->msgnum);

	if (($support_test_struct_nice['type'] == 'multipart')
	&& ($support_test_struct_nice['subtype'] == 'report'))
	{
		$unsupported['finding'] = True;
		$unsupported['user_info'] = 'unsupported Content-Type "'
			.$support_test_struct_nice['type'].'/'.$support_test_struct_nice['subtype']
			.'" forced echo dump';
	}
	

// -----  GET BODY AND SHOW MESSAGE  -------
	set_time_limit(120);
	for ($i = 0; $i < count($part_nice); $i++)
	{
		// TEMPORARY: some lame servers do not give any mime data out
		if ((count($part_nice) == 1) 
		&&  (($part_nice[$i]['m_description'] == 'container') 
		    || ($part_nice[$i]['m_description'] == 'packagelist')) )
		{
			// ====  POP 3 SERVER -OR- MIME IGNORANT SERVER  ====
			$title_text = '&nbsp;Mime-Ignorant Email: ';
			$t->set_var('title_text',$title_text);
			$display_str = 'keywords: '.$part_nice[$i]['m_keywords'].' - '.$phpgw->msg->format_byte_size(strlen($dsp));
			$t->set_var('display_str',$display_str);

			//$msg_raw_headers = $phpgw->dcom->fetchheader($mailbox, $phpgw->msg->msgnum);
			//$msg_headers = $phpgw->dcom->header($mailbox, $phpgw->msg->msgnum); // returns a structure w/o boundry info
			//$struct_pop3 = $phpgw->dcom->get_structure($msg_headers, 1);
			//$msg_boundry = $phpgw->dcom->get_boundary($msg_headers);
			//$msg_body = $phpgw->dcom->fetchbody($mailbox, $phpgw->msg->msgnum, '1');
			//$msg_body = $phpgw->dcom->get_body($mailbox, $phpgw->msg->msgnum);
			//$msg_body = $phpgw->dcom->get_body($phpgw->msg->mailsvr_stream, $phpgw->msg->msgnum);
			$msg_body = $phpgw->msg->phpgw_body();

			// GET THE BOUNDRY
			for ($bs=0;$bs<count($struct->parameters);$bs++)
			{
				$pop3_temp = $struct->parameters[$bs];
				if ($pop3_temp->attribute == "boundary")
				{
					$boundary = $pop3_temp->value;
				}
			}
			$boundary = trim($boundary);

			/*
			// GET THE PARTS
			$this->boundary = $boundary;
			for ($i=1;$i<=$body[0];$i++)
			{
				$pos1 = strpos($body[$i],"--$boundary");
				$pos2 = strpos($body[$i],"--$boundary--");
				if (is_int($pos2) && !$pos2)
				{
					break;
				}
				if (is_int($pos1) && !$pos1)
				{
					$info->parts[] = $this->get_structure($body,&$i,true);
				}
			}
			*/

			/*
			$dsp = '<br><br> === API STRUCT ==== <br><br>'
				.'<pre>'.serialize($struct).'</pre>'
				//.'<br><br> === HEADERS ==== <br><br>'
				//.'<pre>'.$msg_raw_headers.'</pre>'
				.'<br><br> === struct->parameters ==== <br><br>'
				.'<pre>'.serialize($struct->parameters).'</pre>'
				.'<br><br> === BOUNDRY ==== <br><br>'
				.'<pre>'.serialize($boundary).'</pre>'
				.'<br><br> === BODY ==== <br><br>';
				.'<pre>'.serialize($msg_body).'</pre>';
			*/

			$dsp = '<br> === BOUNDRY ==== <br>'
				.'<pre>'.$boundary.'</pre> <br>'
				.'<br> === BODY ==== <br><br>';
			//$dsp = $dsp .$phpgw->dcom->fetchbody($phpgw->msg->mailsvr_stream, $phpgw->msg->msgnum, $part_nice[$i]['m_part_num_mime']);
			$dsp = $dsp .$phpgw->msg->phpgw_fetchbody($part_nice[$i]['m_part_num_mime']);
			
			$t->set_var('message_body',$dsp);
			$t->parse('V_display_part','B_display_part');
		}
		// do we Force Echo Out Unformatted Text ?
		elseif (($part_nice[$i]['m_description'] == 'presentable')
		&& (stristr($part_nice[$i]['m_keywords'], 'PLAIN'))
		&& ($d1_num_parts <= 2)
		&& (($part_nice[$i]['m_part_num_mime'] == 1) || ((string)$part_nice[$i]['m_part_num_mime'] == '1.1'))
		&& ((int)$part_nice[$i]['bytes'] > $force_echo_size))
		{
			// output a blank message body, we'll use an alternate method below
			$t->set_var('V_display_part','');
			// -----  Finished With Message_Mail Template, Output It
			$t->pparse('out','T_message_main');
			
			// -----  Prepare a Table for this Echo Dump
			$title_text = '&nbsp;message: ';
			$t->set_var('title_text',$title_text);
			$display_str = 'keywords: '.$part_nice[$i]['m_keywords'].' - '.$phpgw->msg->format_byte_size($part_nice[$i]['bytes'])
				.'; meets force_echo ('.$phpgw->msg->format_byte_size($force_echo_size).') criteria';
			$t->set_var('display_str',$display_str);
			$t->parse('V_setup_echo_dump','B_setup_echo_dump');
			$t->set_var('V_done_echo_dump','');
			$t->pparse('out','T_message_echo_dump');
			// -----  Echo This Data Directly to the Client
			echo '<pre>';
			//echo $phpgw->dcom->fetchbody($phpgw->msg->mailsvr_stream, $phpgw->msg->msgnum, $part_nice[$i]['m_part_num_mime']);
			echo $phpgw->msg->phpgw_fetchbody($part_nice[$i]['m_part_num_mime']);
			echo '</pre>';
			// -----  Close Table
			$t->set_var('V_setup_echo_dump','');
			$t->parse('V_done_echo_dump','B_done_echo_dump');
			$t->pparse('out','T_message_echo_dump');

			//  = = = =  = =======  CLEANUP AND EXIT PAGE ======= = = = = = =
			unset($part_nice);
			$phpgw->msg->end_request();
			$phpgw->common->phpgw_footer();
			exit;
		}
		// TEMP HACK FOR  FOR NON-SUPPORTED MIME EMAILS
		elseif ($unsupported['finding'] == True)
		{
			// output a blank message body, we'll use an alternate method below
			$t->set_var('V_display_part','');
			// -----  Finished With Message_Mail Template, Output It
			$t->pparse('out','T_message_main');
			
			// -----  Prepare a Table for this Echo Dump
			$title_text = '&nbsp;message: ';
			$t->set_var('title_text',$title_text);
			$display_str = $unsupported['user_info'];
			$t->set_var('display_str',$display_str);
			$t->parse('V_setup_echo_dump','B_setup_echo_dump');
			$t->set_var('V_done_echo_dump','');
			$t->pparse('out','T_message_echo_dump');
			// -----  Echo This Data Directly to the Client
			echo '<pre>';
			echo $phpgw->msg->htmlspecialchars_encode(
				$phpgw->msg->normalize_crlf(
					//trim($phpgw->dcom->get_body($phpgw->msg->mailsvr_stream, $phpgw->msg->msgnum))
					trim($phpgw->msg->phpgw_body())
				)
			    );
			echo '</pre>';
			// -----  Close Table
			$t->set_var('V_setup_echo_dump','');
			$t->parse('V_done_echo_dump','B_done_echo_dump');
			$t->pparse('out','T_message_echo_dump');

			//  = = = =  = =======  CLEANUP AND EXIT PAGE ======= = = = = = =
			unset($part_nice);
			$phpgw->msg->end_request();
			$phpgw->common->phpgw_footer();
			exit;
		}
		elseif (($part_nice[$i]['m_description'] == 'presentable')
		&& (stristr($part_nice[$i]['m_keywords'], 'HTML')))
		{

			// get the body
			//$dsp = $phpgw->dcom->fetchbody($phpgw->msg->mailsvr_stream, $phpgw->msg->msgnum, $part_nice[$i]['m_part_num_mime'], FT_INTERNAL);
			$dsp = $phpgw->msg->phpgw_fetchbody($part_nice[$i]['m_part_num_mime'], FT_INTERNAL);
			// is a blank part test necessary for html ???

			$title_text = lang("section").': '.$part_nice[$i]['m_part_num_mime'];
			//$display_str = $part_nice[$i]['type'].'/'.strtolower($part_nice[$i]['subtype']);
			$display_str = 'keywords: '.$part_nice[$i]['m_keywords']
				.' - '.$phpgw->msg->format_byte_size(strlen($dsp));
			$t->set_var('title_text',$title_text);
			$t->set_var('display_str',$display_str);

			if (stristr($part_nice[$i]['m_keywords'], 'qprint'))
			{
				$dsp = $phpgw->msg->qprint($dsp);
			}

			$parent_idx = $part_nice[$i]['ex_parent_flat_idx'];
			//$msg_raw_headers = $phpgw->dcom->fetchheader($phpgw->msg->mailsvr_stream, $phpgw->msg->msgnum);
			$msg_raw_headers = $phpgw->msg->phpgw_fetchheader('');
			$ms_related_str = 'X-MimeOLE: Produced By Microsoft MimeOLE';

			// ---- Replace "Related" part's ID with a mime reference link
			// this for the less-standard multipart/RELATED subtype ex. Outl00k's Stationary email
			if (($part_nice[$parent_idx]['m_html_related_kids'])
			|| (stristr($msg_raw_headers, $ms_related_str)))
			{
				// typically it's the NEXT mime part that should be inserted into this one
				for ($rel = $i+1; $rel < count($part_nice)+1; $rel++)
				{
					if ((isset($part_nice[$rel]))
					&& ($part_nice[$rel]['id'] != $struct_not_set))
					{
						// Set this Flag for Later Use
						$probable_replace = True;
						// prepare the reference ID for search and replace
						$replace_id = $part_nice[$rel]['id'];
						// prepare the replacement href, add the quotes that the html expects
						$part_href = $part_nice[$rel]['ex_part_href'];
						//$part_href = '"'.$part_nice[$rel]['ex_part_href'].'"';
					
						//echo '<br> **replace_id (pre-processing): ' .$replace_id .'<br>';
						//echo 'part_href (processed): ' .$part_href .'<br>';
					
						// strip <  and  >  from this ID
						$replace_id = ereg_replace( '^<','',$replace_id);
						$replace_id = ereg_replace( '>$','',$replace_id);
						// id references are typically preceeded with "cid:"
						$replace_id = 'cid:' .$replace_id;
					
						//echo '**replace_id (post-processing): ' .$replace_id .'<br>';
					
						// Attempt the Search and Replace
						$dsp = str_replace($replace_id, $part_href, $dsp);
					}
				}
				// ELSE - Forget About It - Unsupported
			}

			// ---- strip html - FUTURE: only strip "bad" html
			
			// PARTIALLY HOSED
			// strip source html email from <!DOCTYPE  ...  to the begining of the body tag
			//$dsp = preg_replace("/<!DOC.*<body/ismx", "BLA",$dsp);
			// strip to the end of the <body .... > tag
			//$dsp = preg_replace("/^.*>/i","",$dsp);
			// strip </body>  tag
			//$dsp = preg_replace("/<\/body>/i","",$dsp);
			// strip </html>  tag
			//$dsp = preg_replace("/<\/html>/i","",$dsp);

			// FULLY HOSED
			// strip source html email from <!DOCTYPE  ...  to the begining of the style tag
			//$dsp = preg_replace("/<!DOC.*<style/ismx", "<style",$dsp);
			//$dsp = preg_replace("/<\/style>.*<body/ismx", "</style>\r\n<body",$dsp);
			// strip to the end of the <body .... > tag
			//$dsp = preg_replace("/^<body.{0,}>/im","",$dsp);
			// strip </body>  tag
			//$dsp = preg_replace("/<\/body>/i","",$dsp);
			// strip </html>  tag
			//$dsp = preg_replace("/<\/html>/i","",$dsp);

			//if (strtoupper(lang("charset")) <> "BIG5")
			//{
			//	$dsp = $phpgw->strip_html($dsp);
			//}
			
			//  TEST: only strip "bad" html
			// eliminate JS code
			//$dsp = preg_replace("'<script[^>]*?\>.*?</script>'", "",$dsp);
			
			//$dsp = ereg_replace( "^","<p>",$dsp);
			//$dsp = ereg_replace( "\n","<br>",$dsp);
			//$dsp = ereg_replace( "$","</p>", $dsp);


			//$t->set_var('message_body',"<tt>$dsp</tt>");

			/*
			// if there are headers <!DOCTYPE or <STYLE> in the html body, then seeing is optional
			if ((stristr($dsp, '<!DOCTYPE'))
			|| (stristr($dsp, '<style'))
			|| (stristr($dsp, '<script'))
			|| (stristr($dsp, '</script>'))
			|| (stristr($dsp, '</head>')))
			*/
			
			// Viewing HTML part is Optional (NOT automatic) if:
			// (1) if there are CSS Body formattings, or
			// (2) any <script> in the html body
			if ((preg_match("/<style.*body.*[{].*[}]/ismx", $dsp))
			|| (preg_match("/<script.*>.*<\/script>/ismx", $dsp)))
			{
				// if we replaced id(s) with href'(s) above (RELATED) then
				// stuff the modified html in a hidden var, submit it then echo it back
				if (($part_nice[$parent_idx]['m_html_related_kids'])
				|| (stristr($msg_raw_headers, $ms_related_str)))
				{
					// this means we *may* have replaced, a guess, but better security 
					// than setting a variable that could be fed to the server from a URI
					// replacement is done, and hard to reproduce easily, do just use the work
					// we already did above
					// make a submit button with this html part as a hidden var
					$dsp =
					//'<pre>'.$msg_raw_headers .'</pre>'
					'<p>'
					.'<form action="'.$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/view_html.php').'" method="post">'."\r\n"
					.'<input type="hidden" name="html_part" value="'.base64_encode($dsp).'">'."\r\n"
					.'&nbsp;&nbsp;<input type="submit" value="View as HTML">'."\r\n"
					.'</p>'
					.'<br>';
				}
				else
				{
					// in this case, we need only refer to the part number in an href, then redirect
					// make a submit button with this html part as a hidden var
					if ($part_nice[$i]['encoding'] != $struct_not_set)
					{
						$part_encoding = $part_nice[$i]['encoding'];
					}
					else
					{
						$part_encoding = '';
					}
					$part_href = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/get_attach.php',
						 'folder='.$phpgw->msg->prep_folder_out('') .'&msgnum=' .$phpgw->msg->msgnum .'&part_no=' .$part_nice[$i]['m_part_num_mime'] .'&encoding=' .$part_encoding);
					$dsp =
					//'<pre>'.$msg_raw_headers .'</pre>'
					'<p>'
					.'<form action="'.$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/view_html.php').'" method="post">'."\r\n"
					.'<input type="hidden" name="html_reference" value="'.$part_href.'">'."\r\n"
					.'&nbsp;&nbsp;<input type="submit" value="View as HTML">'."\r\n"
					.'</p>'
					.'<br>';
				}
			}
			else
			{
				// it can't be that bad, just show it
			}

			$t->set_var('message_body',"$dsp");
			$t->parse('V_display_part','B_display_part', True);

			/*
			// get the body
			//$dsp = $phpgw->dcom->fetchbody($mailbox, $phpgw->msg->msgnum, $part_nice[$i]['m_part_num_mime'], FT_INTERNAL);
			$dsp = $phpgw->dcom->fetchbody($phpgw->msg->mailsvr_stream, $phpgw->msg->msgnum, $part_nice[$i]['m_part_num_mime'], FT_INTERNAL);

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
			$dsp = make_clickable($dsp, $phpgw->msg->folder);

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
		elseif ($part_nice[$i]['m_description'] == 'presentable')
		{
			// ----- get the part from the server
			//$dsp = $phpgw->dcom->fetchbody($phpgw->msg->mailsvr_stream, $phpgw->msg->msgnum, $part_nice[$i]['m_part_num_mime']);
			$dsp = $phpgw->msg->phpgw_fetchbody($part_nice[$i]['m_part_num_mime']);
			$dsp = trim($dsp);

			/*
			$dsp = str_replace("{", " BLA ", $dsp);
			$dsp = str_replace("}", " ALB ", $dsp);

			$b_slash = chr(92);
			$f_slash = chr(47);
			$dsp = str_replace($b_slash, " B_SLASH ", $dsp);
			$dsp = str_replace($f_slash, " F_SLASH ", $dsp);

			$dbl_quo = chr(34);
			$single_quo = chr(39);
			$dsp = str_replace($dbl_quo, " dbl_quo ", $dsp);
			$dsp = str_replace($single_quo, " single_quo ", $dsp);

			$colon = chr(58);
			$dsp = str_replace($colon, " colon ", $dsp);
			
			echo '<br>'.$part_nice[$i]['m_part_num_mime'].'<br>';
			var_dump($dsp);
			*/

			// ----- when to skip showing a part (i.e. blank part - no alpha chars)
			$skip_this_part = False;
			if (strlen($dsp) < 3)
			{
				$skip_this_part = True;
				$t->set_var('V_display_part','');
			}
			
			// ===DEBUG===
			//$skip_this_part = True;

			// ----- show the part 
			if ($skip_this_part == False)
			{
				if (stristr($part_nice[$i]['m_keywords'], 'qprint'))
				{
					$dsp = $phpgw->msg->qprint($dsp);
					$tag = "tt";
				}

				//    normalize line breaks to rfc2822 CRLF
				$dsp = $phpgw->msg->normalize_crlf($dsp);


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

				// the "view unformatted" or "view formatted" option base url
				$view_option_url = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/message.php','&folder='.$phpgw->msg->prep_folder_out('').'&msgnum='.$phpgw->msg->msgnum);

				if ((isset($phpgw->msg->args['no_fmt']))
				&& ($phpgw->msg->args['no_fmt'] != ''))
				{
					$dsp = $phpgw->msg->htmlspecialchars_decode($dsp);
					// (OPT 1) THIS WILL DISPLAY UNFORMATTED TEXT (faster)
					// enforce HARD WRAP - X chars per line
					$dsp = $phpgw->msg->body_hard_wrap($dsp, 85);
					$dsp = $phpgw->msg->htmlspecialchars_encode($dsp);
					$dsp = '<pre>'.$dsp.'</pre>';
					// alternate to view formatted
					$view_option = $phpgw->msg->href_maketag($view_option_url, 'view formatted');
				}
				else
				{
					if (strtoupper(lang("charset")) <> "BIG5")
					{
						// befor we can encode some chars into html entities (ex. change > to &gt;)
						// we need to make sure there are no html entities already there
						// else we'll end up encoding the & (ampersand) when it should not be
						// ex. &gt; becoming &amp;gt; is NOT what we want
						$dsp = $phpgw->msg->htmlspecialchars_decode($dsp);
						// now we can make browser friendly html entities out of $ < > ' " chars
						$dsp = $phpgw->msg->htmlspecialchars_encode($dsp);
						// now lets preserve the spaces, else html squashes multiple spaces into 1 space
						// NOT WORTH IT: give view unformatted option instead
						//$dsp = $phpgw->msg->space_to_nbsp($dsp);
					}
					$dsp = make_clickable($dsp, $phpgw->msg->folder);
					// (OPT 2) THIS CONVERTS UNFORMATTED TEXT TO *VERY* SIMPLE HTML - adds only <br>
					$dsp = ereg_replace("\r\n","<br>",$dsp);
					// add a line after the last line of the message
					$dsp = $dsp .'<br><br>';
					// choice to view unformatted
					$view_option = $phpgw->msg->href_maketag($view_option_url."&no_fmt=1", 'view unformatted');
				}

				// one last thing with "view option" - only show it with PLAIN email parts
				if (!stristr($part_nice[$i]['m_keywords'], 'plain'))
				{
					$view_option = '';
				}
				
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
					.' - '.$phpgw->msg->format_byte_size(strlen($dsp));
				
				$display_str = $display_str 
					.' &nbsp; '.$view_option;
				$t->set_var('display_str',$display_str);
				$t->set_var('message_body',$dsp);
				$t->parse('V_display_part','B_display_part', True);

				/*
				// ------- Previous Method
				// get the part
				//$dsp = $phpgw->dcom->fetchbody($mailbox, $phpgw->msg->msgnum, $part_nice[$i]['m_part_num_mime']);
				$dsp = $phpgw->dcom->fetchbody($phpgw->msg->mailsvr_stream, $phpgw->msg->msgnum, $part_nice[$i]['m_part_num_mime']);

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
				$dsp = make_clickable($dsp, $phpgw->msg->folder);
				*/
			}
		}
		elseif ($part_nice[$i]['m_description'] == 'presentable/image')
		{
			$title_text = lang("section").': '.$part_nice[$i]['m_part_num_mime'];
			$display_str = $phpgw->msg->decode_header_string($part_nice[$i]['ex_part_name'])
				.' - ' .$phpgw->msg->format_byte_size((int)$part_nice[$i]['bytes']) 
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
				//$dsp = $phpgw->dcom->fetchbody($mailbox, $phpgw->msg->msgnum, $part_nice[$i]['m_part_num_mime'], FT_INTERNAL);
				$dsp = $phpgw->dcom->fetchbody($phpgw->msg->mailsvr_stream, $phpgw->msg->msgnum, $part_nice[$i]['m_part_num_mime'], FT_INTERNAL);
					//$dsp = $phpgw->dcom->fetchbody($mailbox, $phpgw->msg->msgnum, $part_nice[$i]['m_part_num_mime']);
					//$processed_msg_body = $processed_msg_body . base64_decode($dsp) .'<br>' ."\r\n";
				$att_size =  $phpgw->msg->format_byte_size(strlen($dsp));
				$msg_text = $msg_text .'&nbsp;&nbsp; size: '.$att_size;
			}
			*/
			$msg_text = '&nbsp;&nbsp; <strong>Attachment:</strong>'
				.'&nbsp;&nbsp; '.$part_nice[$i]['ex_part_clickable']
				.'&nbsp;&nbsp; size: '.$phpgw->msg->format_byte_size((int)$part_nice[$i]['bytes'])
				.'<br><br>';
			
			$t->set_var('message_body',$msg_text);
			$t->parse('V_display_part','B_display_part', True);
		}
		elseif (($part_nice[$i]['m_description'] != 'container')
		&& ($part_nice[$i]['m_description'] != 'packagelist'))
		{
			$title_text = lang("section").': '.$part_nice[$i]['m_part_num_mime'];
			$display_str = $phpgw->msg->decode_header_string($part_nice[$i]['ex_part_name'])
				.' - keywords: ' .$part_nice[$i]['m_keywords'];
			$t->set_var('title_text',$title_text);
			$t->set_var('display_str',$display_str);
			
			$msg_text = '';
			// UNKNOWN DATA
			$msg_text = $msg_text .'<br><strong>ERROR: Unknown Message Data</strong><br>';
			if ($part_nice[$i]['encoding'] == 'base64')
			{
				//$dsp = $phpgw->dcom->fetchbody($phpgw->msg->mailsvr_stream, $phpgw->msg->msgnum, $part_nice[$i]['m_part_num_mime'], FT_INTERNAL);
				$dsp = $phpgw->msg->phpgw_fetchbody($part_nice[$i]['m_part_num_mime'], FT_INTERNAL);
					//$dsp = $phpgw->dcom->fetchbody($mailbox, $phpgw->msg->msgnum, $part_nice[$i]['m_part_num_mime']);
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

	$phpgw->msg->end_request();
	$phpgw->common->phpgw_footer();
?>
