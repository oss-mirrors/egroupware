<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail Message Processing Functions                             *
  * http://www.phpgroupware.org                                              *
  */
  /**************************************************************************\
  * phpGroupWare API - E-Mail Message Processing Functions                         *
  * This file written by Angelo Tony Puglisi (Angles) <angles@phpgroupware.org>      *
  * Handles specific operations in manipulating email messages                         *
  * Copyright (C) 2001 Angelo Tony Puglisi (Angles)                                           *
  * -------------------------------------------------------------------------*
  * This library is part of the phpGroupWare API                             *
  * http://www.phpgroupware.org/api                                          * 
  * ------------------------------------------------------------------------ *
  * This library is free software; you can redistribute it and/or modify it  *
  * under the terms of the GNU Lesser General Public License as published by *
  * the Free Software Foundation; either version 2.1 of the License,         *
  * or any later version.                                                    *
  * This library is distributed in the hope that it will be useful, but      *
  * WITHOUT ANY WARRANTY; without even the implied warranty of               *
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
  * See the GNU Lesser General Public License for more details.              *
  * You should have received a copy of the GNU Lesser General Public License *
  * along with this library; if not, write to the Free Software Foundation,  *
  * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
  \**************************************************************************/

  /* $Id$ */

// include this last, it extends mail_msg_wrappers which extends mail_msg_base
// so (1) include mail_msg_base, (2) incluse mail_msg_wrappers extending mail_msg_base
// then (3) include mail_msg which extends mail_msg_wrappers and, by inheritance, mail_msg_base
class mail_msg extends mail_msg_wrappers
{

	function all_folders_listbox($mailbox,$pre_select='',$skip='',$indicate_new=False)
	{
		if (!$mailbox)
		{
			$mailbox = $this->mailsvr_stream;
		}

		// DEBUG: force unseen display
		//$indicate_new = True;

		// init some important variables
		$outstr = '';
		//$unseen_prefix = ' &lt;';
		//$unseen_suffix = ' new&gt;';	
		//$unseen_prefix = ' &#091;';
		//$unseen_suffix = ' new&#093;';
		//$unseen_prefix = ' &#040;';
		//$unseen_suffix = ' new&#041;';
		//$unseen_prefix = ' &#045; ';
		//$unseen_suffix = ' new';
		//$unseen_prefix = ' &#045;';
		//$unseen_suffix = '&#045;';	
		//$unseen_prefix = '&nbsp;&nbsp;&#040;';
		//$unseen_suffix = ' new&#041;';
		//$unseen_prefix = '&nbsp;&nbsp;&#091;';
		//$unseen_suffix = ' new&#093;';
		$unseen_prefix = '&nbsp;&nbsp;&#060;';
		$unseen_suffix = ' new&#062;';

		if ($this->newsmode)
		{
			while($pref = each($GLOBALS['phpgw_info']['user']['preferences']['nntp']))
			{
				$GLOBALS['phpgw']->db->query('SELECT name FROM newsgroups WHERE con='.$pref[0]);
				while($GLOBALS['phpgw']->db->next_record())
				{
					$outstr = $outstr .'<option value="' . urlencode($GLOBALS['phpgw']->db->f('name')) . '">' . $GLOBALS['phpgw']->db->f('name')
					  . '</option>';
				}
			}
		}
		else
		{
			$folder_list = $this->get_folder_list('');

			for ($i=0; $i<count($folder_list);$i++)
			{
				$folder_long = $folder_list[$i]['folder_long'];
				$folder_short = $folder_list[$i]['folder_short'];
				if ($folder_short == $this->get_folder_short($pre_select))
				{
					$sel = ' selected';
				}
				else
				{
					$sel = '';
				}
				if ($folder_short != $this->get_folder_short($skip))
				{
					$outstr = $outstr .'<option value="' .$this->prep_folder_out($folder_long) .'"'.$sel.'>' .$folder_short;
					// do we show the number of new (unseen) messages for this folder
					if (($indicate_new)
					&& ($this->care_about_unseen($folder_short)))
					{
						$mailbox_status = $this->dcom->status($mailbox,$this->get_mailsvr_callstr().$folder_long,SA_ALL);
						if ($mailbox_status->unseen > 0)
						{
							$outstr = $outstr . $unseen_prefix . $mailbox_status->unseen . $unseen_suffix;
						}
					}
					$outstr = $outstr . "</option>\r\n";
				}
			}
		}
		return $outstr;
	}


	// ---- Messages Sort Order Start and Msgnum  -----
	function fill_sort_order_start_msgnum()
	{
		//$debug_sort = True;
		$debug_sort = False;
	
		// AND ensure $this->sort  $this->order  and  $this->start have usable values
		/*
		Sorting defs:
		SORTDATE:  0	//This is the Date that the senders email client stanp the message with
		SORTARRIVAL: 1	 //This is the date your email server's MTA stamps the message with
				// using SORTDATE cause some messages to be displayed in the wrong cronologicall order
		SORTFROM:  2
		SORTSUBJECT: 3
		SORTSIZE:  6

		// imap_sort(STREAM,  CRITERIA,  REVERSE,  OPTIONS)
		// Stream: is $this->mailsvr_stream
		// Criteria = $sort : is HOW to sort, we prefer SORTARRIVAL, or "1" as default (see note above)
		// Reverse = "order" : 0 = imap default = lowest to highest  ;;  1 = Reverse sorting  =  highest to lowest
		// Options: we do not use this (yet)
		*/

		// == SORT ==
		// if not set in the args, then assign some defaults
		// then store the determination in a class variable $this->sort
		if ((isset($this->args['sort']))
		&& ($this->args['sort'] != '')
		 && (($this->args['sort'] >= 0) && ($this->args['sort'] <= 6)) )
		{
			// this is a valid "sort" variable passed as an argument (in a URL, form, or cookie, or external request)
			$this->sort = $this->args['sort'];
		}
		elseif ((isset($this->args['sort']))
		&& ($this->args['sort'] != '')
		  && ($this->args['sort'] == 'ASC') && ($this->newsmode))
		{
			// I think this is needed for newsmode because it reads message list that has been
			// stored locally in a database, in this case it is NOT an arg ment for the NNTP server
			$this->sort = 'ASC';
		}
		else
		{
			// SORTARRIVAL as noted above, the preferred default for email
			$this->sort = 1;
		}

		// == ORDER ==
		// (reverse sorting or not)  if specified in the url, then use it, else use defaults
		if ((isset($this->args['order']))
		&& ($this->args['order'] != '')
		  && (($this->args['order'] >= 0) && ($this->args['order'] <= 1)) )
		{
			// this is a valid $this->args['order'] variable passed as an arg
			$this->order = $this->args['order'];
		}
		elseif ((isset($GLOBALS['phpgw_info']['user']['preferences']['email']['default_sorting']))
		  && ($GLOBALS['phpgw_info']['user']['preferences']['email']['default_sorting'] == "new_old"))
		{
			// user has a preference set to see new mail first
			// this is considered "reverse" order because it is "highest to lowest"
			// with "highest" being the more recent date values
			$this->order = 1;
		}
		else
		{
			// if no pref is set or the pref is old->new, then order should = 0
			// this is considered "NOT reverse" a.k.a. "normal" because it is "lowest to highest"
			// with "lowest" being the older date values
			$this->order = 0;
		}

		// == START ==
		// when requesting a subset of messages, start will get you there
		if ((isset($this->args['start']))
		&& ($this->args['start'] != ''))
		{
			// this is a valid $this->args['start'] variable passed as an arg
			// you are probably requesting a subset of the available messages
			$this->start = $this->args['start'];
		}
		else
		{
			// start at the beginning (relative to your "sort" and "order" of course)
			$this->start = 0;
		}

		// == MSGNUM ==
		// the current message number for the message we are concerned with here
		if ((isset($this->args['msgnum']))
		&& ($this->args['msgnum'] != ''))
		{
			$this->msgnum = $this->args['msgnum'];
		}
		// else it stays at default of empty string ('')

		if ($debug_sort)
		{
			echo 'sort: '.$this->sort.'<br>';
			echo 'order: '.$this->order.'<br>';
			echo 'start: '.$this->start.'<br>';
			echo 'msgnum: '.$this->msgnum.'<br>';
		}
	}

	function format_byte_size($feed_size)
	{
		if ($feed_size < 999999)
		{
			$nice_size = round(10*($feed_size/1024))/10;
			// kbytes is small enough that the 1/10 digit is irrelevent
			$nice_size = round($nice_size);
			// it looks stupid to report "0 k" as a size, make it "1 k"
			if ((int)$nice_size == 0)
			{
				$nice_size = 1;
			}
			$nice_size = round($nice_size).' k';
		}
		else
		{
			//  round to W.XYZ megs by rounding WX.YZ
			$nice_size = round($feed_size/(1024*100));
			// then bring it back one digit and add the MB string
			$nice_size = ($nice_size/10) .' MB';
		}
		return $nice_size;
	}

	// ----  High-Level Function To Get The Subject String  -----
	function get_subject($msg, $desired_prefix='Re: ')
	{
		if ( (! $msg->Subject) || ($msg->Subject == '') )
		{
			$subject = lang('no subject');
		}
		else
		{
			$subject = $this->decode_header_string($msg->Subject);
		}
		// non-us-ascii chars in headers MUST be specially encoded, so decode them (if any) now
		// $personal = $this->qprint_rfc_header($personal);
		$personal = $this->decode_header_string($personal);
		// do we add a prefix like Re: or Fw:
		if ($desired_prefix != '')
		{
			if (strtoupper(substr($subject, 0, 3)) != strtoupper(trim($desired_prefix)))
			{
				$subject = $desired_prefix . $subject;
			}
		}
		$subject = $this->htmlspecialchars_encode($subject);
		return $subject;
	}

	// ----  High-Level Function To Get The "so-and-so" wrote String   -----
	function get_who_wrote($msg)
	{
		if ( (!isset($msg->from)) && (!isset($msg->reply_to)) )
		{
			$lang_somebody = 'somebody';
			return $lang_somebody;
		}
		elseif ($msg->from[0])
		{
			$from = $msg->from[0];
		}
		else
		{
			$from = $msg->reply_to[0];
		}
		if ((!isset($from->personal)) || ($from->personal == ''))
		{
			$personal = $from->mailbox.'@'.$from->host;
			//$personal = 'not set or blank';
		}
		else
		{
			//$personal = $from->personal.' ('.$from->mailbox.'@'.$from->host.')';
			$personal = trim($from->personal);
			// non-us-ascii chars in headers MUST be specially encoded, so decode them (if any) now
			$personal = $this->decode_header_string($personal);
			//$personal = $this->qprint_rfc_header($personal);
			$personal = $personal .' ('.$from->mailbox.'@'.$from->host.')';
		}
		return $personal;
	}

	/*!
	@function has_real_attachment
	@abstract s quick test to see if a message has an attachment, (NOT 100% accurate, but fast and mostly accurate)
	@param $struct : PHP structure obtained from the "fetchstructure" command
	@result boolean
	@discussion for use when displaying a list of messages, a quick way to determine if visual information (paperclip) is necessary
	*/
	function has_real_attachment($struct)
	{
		$haystack = serialize($struct);

		if (stristr($haystack, 's:9:"attribute";s:4:"name"'))
		{
			// param attribute "name"
			// s:9:"attribute";s:4:"name"
			return True;
		}
		elseif (stristr($haystack, 's:8:"encoding";i:3'))
		{
			// encoding is base 64
			// s:8:"encoding";i:3
			return True;
		}
		elseif (stristr($haystack, 's:11:"disposition";s:10:"attachment"'))
		{
			// header disposition calls itself "attachment"
			// s:11:"disposition";s:10:"attachment"
			return True;
		}
		elseif (stristr($haystack, 's:9:"attribute";s:8:"filename"'))
		{
			// another mime filename indicator
			// s:9:"attribute";s:8:"filename"
			return True;
		}
		else
		{
			return False;
		}
	}


	/* * * * * * * * * * *
	  *
	  *   = = = = = = MIME ANALYSIS = = = = = 
	  *
	  * * * * * * *  * * * */
	// ---- Message Structure Analysis   -----
	function get_flat_pgw_struct($struct)
	{
		if (isset($this->not_set))
		{
			$not_set = $this->not_set;
		}
		else
		{
			$not_set = '-1';
		}
		
		// get INITIAL part structure / array from the fetchstructure  variable
		if ((!isset($struct->parts[0]) || (!$struct->parts[0])))
		{
			$part[0] = $struct;
		}
		else
		{
			$part = $struct->parts;
		}
	
		//$part = Array();
		//$part[0] = $struct;

		//echo '<br>INITIAL var part serialized:<br>' .serialize($part) .'<br><br>';	

		$d1_num_parts = count($part);	
		$part_nice = Array();

		// get PRIMARY level part information
		$deepest_level=0;
		$array_position = -1;  // it will be advanced to 0 before its used
		// ---- Flatten Message Structure Array   -----
		for ($d1 = 0; $d1 < $d1_num_parts; $d1++)
		{
			$array_position++;
			$d1_mime_num = (string)($d1+1);
			$part_nice[$array_position] = $this->pgw_msg_struct($part[$d1], $not_set, $d1_mime_num, ($d1+1), $d1_num_parts, 1);
			if ($deepest_level < 1) { $deepest_level=1; }
			
			// get SECONDARY/EMBEDDED level part information
			$d1_array_pos = $array_position;
			if ($part_nice[$d1_array_pos]['ex_num_subparts'] != $not_set)
			{
				$d2_num_parts = $part_nice[$d1_array_pos]['ex_num_subparts'];
				for ($d2 = 0; $d2 < $d2_num_parts; $d2++)
				{
					$d2_part = $part_nice[$d1_array_pos]['subpart'][$d2];
					$d2_mime_num = (string)($d1+1) .'.' .(string)($d2+1);
					$array_position++;
					$part_nice[$array_position] = $this->pgw_msg_struct($d2_part, $d1_array_pos, $d2_mime_num, ($d2+1), $d2_num_parts, 2);
					if ($deepest_level < 2) { $deepest_level=2; }
					
					// get THIRD/EMBEDDED level part information
					$d2_array_pos = $array_position;
					if ($d2_part['ex_num_subparts'] != $not_set)
					{
						$d3_num_parts = $part_nice[$d2_array_pos]['ex_num_subparts'];
						for ($d3 = 0; $d3 < $d3_num_parts; $d3++)
						{
							$d3_part = $part_nice[$d2_array_pos]['subpart'][$d3];
							$d3_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1);
							$array_position++;
							$part_nice[$array_position] = $this->pgw_msg_struct($d3_part, $d2_array_pos, $d3_mime_num, ($d3+1), $d3_num_parts, 3);
							if ($deepest_level < 3) { $deepest_level=3; }
							
							// get FOURTH/EMBEDDED level part information
							$d3_array_pos = $array_position;
							if ($d3_part['ex_num_subparts'] != $not_set)
							{
								$d4_num_parts = $part_nice[$d3_array_pos]['ex_num_subparts'];
								for ($d4 = 0; $d4 < $d4_num_parts; $d4++)
								{
									$d4_part = $part_nice[$d3_array_pos]['subpart'][$d4];
									$d4_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1);
									$array_position++;
									$part_nice[$array_position] = $this->pgw_msg_struct($d4_part, $d3_array_pos, $d4_mime_num, ($d4+1), $d4_num_parts, 4);
									if ($deepest_level < 4) { $deepest_level=4; }
									
									// get FIFTH LEVEL EMBEDDED level part information
									$d4_array_pos = $array_position;
									if ($d4_part['ex_num_subparts'] != $not_set)
									{
										$d5_num_parts = $part_nice[$d4_array_pos]['ex_num_subparts'];
										for ($d5 = 0; $d5 < $d5_num_parts; $d5++)
										{
											$d5_part = $part_nice[$d4_array_pos]['subpart'][$d5];
											$d5_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1) .'.' .(string)($d5+1);
											$array_position++;
											$part_nice[$array_position] = $this->pgw_msg_struct($d5_part, $d4_array_pos, $d5_mime_num, ($d5+1), $d5_num_parts, 5);
											if ($deepest_level < 5) { $deepest_level=5; }
											
											// get SISTH LEVEL EMBEDDED level part information
											$d5_array_pos = $array_position;
											if ($d5_part['ex_num_subparts'] != $not_set)
											{
												$d6_num_parts = $part_nice[$d5_array_pos]['ex_num_subparts'];
												for ($d6 = 0; $d6 < $d6_num_parts; $d6++)
												{
													$d6_part = $part_nice[$d5_array_pos]['subpart'][$d6];
													$d6_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1) .'.' .(string)($d5+1) .'.' .(string)($d6+1);
													$array_position++;
													$part_nice[$array_position] = $this->pgw_msg_struct($d6_part, $d5_array_pos, $d6_mime_num, ($d6+1), $d6_num_parts, 6);
													if ($deepest_level < 6) { $deepest_level=6; }
													
													// get SEVENTH LEVEL EMBEDDED level part information
													$d6_array_pos = $array_position;
													if ($d6_part['ex_num_subparts'] != $not_set)
													{
														$d7_num_parts = $part_nice[$d6_array_pos]['ex_num_subparts'];
														for ($d7 = 0; $d7 < $d7_num_parts; $d7++)
														{
															$d7_part = $part_nice[$d6_array_pos]['subpart'][$d7];
															$d7_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1) .'.' .(string)($d5+1) .'.' .(string)($d6+1) .'.' .(string)($d7+1);
															$array_position++;
															$part_nice[$array_position] = $this->pgw_msg_struct($d7_part, $d6_array_pos, $d7_mime_num, ($d7+1), $d7_num_parts, 7);
															if ($deepest_level < 7) { $deepest_level=7; }
															
															// get EIGTH LEVEL EMBEDDED level part information
															$d7_array_pos = $array_position;
															if ($d7_part['ex_num_subparts'] != $not_set)
															{
																$d8_num_parts = $part_nice[$d7_array_pos]['ex_num_subparts'];
																for ($d8 = 0; $d8 < $d8_num_parts; $d8++)
																{
																	$d8_part = $part_nice[$d7_array_pos]['subpart'][$d8];
																	$d8_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1) .'.' .(string)($d5+1) .'.' .(string)($d6+1) .'.' .(string)($d7+1) .'.' .(string)($d8+1);
																	$array_position++;
																	$part_nice[$array_position] = $this->pgw_msg_struct($d8_part, $d7_array_pos, $d8_mime_num, ($d8+1), $d8_num_parts, 8);
																	if ($deepest_level < 8) { $deepest_level=8; }
																	
																	// get NINTH LEVEL EMBEDDED level part information
																	$d8_array_pos = $array_position;
																	if ($d8_part['ex_num_subparts'] != $not_set)
																	{
																		$d9_num_parts = $part_nice[$d8_array_pos]['ex_num_subparts'];
																		for ($d9 = 0; $d9 < $d9_num_parts; $d9++)
																		{
																			$d9_part = $part_nice[$d8_array_pos]['subpart'][$d9];
																			$d9_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1) .'.' .(string)($d5+1) .'.' .(string)($d6+1) .'.' .(string)($d7+1) .'.' .(string)($d8+1) .'.' .(string)($d9+1);
																			$array_position++;
																			$part_nice[$array_position] = $this->pgw_msg_struct($d9_part, $d8_array_pos, $d9_mime_num, ($d9+1), $d9_num_parts, 9);
																			if ($deepest_level < 9) { $deepest_level=9; }
																			
																			// get 10th LEVEL EMBEDDED level part information
																			$d9_array_pos = $array_position;
																			if ($d9_part['ex_num_subparts'] != $not_set)
																			{
																				$d10_num_parts = $part_nice[$d9_array_pos]['ex_num_subparts'];
																				for ($d10 = 0; $d10 < $d10_num_parts; $d10++)
																				{
																					$d10_part = $part_nice[$d9_array_pos]['subpart'][$d10];
																					$d10_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1) .'.' .(string)($d5+1) .'.' .(string)($d6+1) .'.' .(string)($d7+1) .'.' .(string)($d8+1) .'.' .(string)($d9+1) .'.' .(string)($d10+1);
																					$array_position++;
																					$part_nice[$array_position] = $this->pgw_msg_struct($d10_part, $d9_array_pos, $d10_mime_num, ($d10+1), $d10_num_parts, 10);
																					if ($deepest_level < 10) { $deepest_level=10; }
																					
																					// get 11th LEVEL EMBEDDED level part information
																					$d10_array_pos = $array_position;
																					if ($d10_part['ex_num_subparts'] != $not_set)
																					{
																						$d11_num_parts = $part_nice[$d10_array_pos]['ex_num_subparts'];
																						for ($d11 = 0; $d11 < $d11_num_parts; $d11++)
																						{
																							$d11_part = $part_nice[$d10_array_pos]['subpart'][$d11];
																							$d11_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1) .'.' .(string)($d5+1) .'.' .(string)($d6+1) .'.' .(string)($d7+1) .'.' .(string)($d8+1) .'.' .(string)($d9+1) .'.' .(string)($d10+1) .'.' .(string)($d11+1);
																							$array_position++;
																							$part_nice[$array_position] = $this->pgw_msg_struct($d11_part, $d10_array_pos, $d11_mime_num, ($d11+1), $d11_num_parts, 11);
																							if ($deepest_level < 11) { $deepest_level=11; }
																							
																							// get 12th LEVEL EMBEDDED level part information
																							$d11_array_pos = $array_position;
																							if ($d11_part['ex_num_subparts'] != $not_set)
																							{
																								$d12_num_parts = $part_nice[$d11_array_pos]['ex_num_subparts'];
																								for ($d12 = 0; $d12 < $d12_num_parts; $d12++)
																								{
																									$d12_part = $part_nice[$d11_array_pos]['subpart'][$d12];
																									$d12_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1) .'.' .(string)($d5+1) .'.' .(string)($d6+1) .'.' .(string)($d7+1) .'.' .(string)($d8+1) .'.' .(string)($d9+1) .'.' .(string)($d10+1) .'.' .(string)($d11+1) .'.' .(string)($d12+1);
																									$array_position++;
																									$part_nice[$array_position] = $this->pgw_msg_struct($d12_part, $d11_array_pos, $d12_mime_num, ($d12+1), $d12_num_parts, 12);
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

		// CONTINUE WITH THE ANALYSIS

		// ---- Mime Characteristics Analysis  and more Attachments Detection  -----	
		// ANALYSIS LOOP Part 1
		for ($i = 0; $i < count($part_nice); $i++)
		{
			// ------  ATTACHMENT DETECTION  -------
			
			// NOTE: initially I wanted to treat base64 attachments with more "respect", but many other attachments are NOT
			// base64 encoded and are still attachments - if param_value NAME has a value, pretend it's an attachment
			// however, a base64 part IS an attachment even if it has no name, just make one up
			// also, if "disposition" header = "attachment", same thing, it's an attachment, and if no name is in the params, make one up
			
			// Fallback / Default: assume No Attachment here
			//$part_nice['ex_part_name'] = 'unknown.html';
			$part_nice[$i]['ex_part_name'] = 'attachment.txt';
			$part_nice[$i]['ex_attachment'] = False;
			
			// Attachment Detection PART1 = if a part has a NAME=FOO in the param pairs, then treat as an attachment
			if (($part_nice[$i]['ex_num_param_pairs'] > 0)
			&& ($part_nice[$i]['ex_attachment'] == False))
			{
				for ($p = 0; $p < $part_nice[$i]['ex_num_param_pairs']; $p++)
				{
					if (($part_nice[$i]['params'][$p]['attribute'] == 'name') 
					  && ($part_nice[$i]['params'][$p]['value'] != $not_set))
					{
						$part_nice[$i]['ex_part_name'] = $part_nice[$i]['params'][$p]['value'];
						$part_nice[$i]['ex_attachment'] = True;
						break;
					}
				}
			}
			// Attachment Detection PART2 = if a part has encoding=base64 , then treat as an attachment
			if (($part_nice[$i]['encoding'] == 'base64')
			&& ($part_nice[$i]['ex_attachment'] == False)
			// some idiots encode text/plain parts in base64 - that's not an attachment
			&& ($part_nice[$i]['subtype'] != 'plain'))
			{
				// NOTE: if a part has a name in the params, the above code would have found it, so to get here means
				// we MUST have a base64 part with NO NAME - but it still should be treated as an attachment
				$part_nice[$i]['ex_attachment'] = True;
				// BUT we have no idea of it's name, and *maybe* no idea of it's content type (eg. name.gif = image/gif)
				// sometimes the name's extention is the only info we have, i.e. ".doc" implies a WORD file
				//$part_nice['ex_part_name'] = 'no_name.att';
			}
			// Attachment Detection PART3 = if "disposition" header has a value of "attachment" , then treat as an attachment
			// PROVIDED it is not type "message" - in that case the attachment is *inside* the message, not the message itself
			if (($part_nice[$i]['disposition'] == 'attachment')
			&& ($part_nice[$i]['type'] != 'message')
			&& ($part_nice[$i]['ex_attachment'] == False))
			{
				// NOTE: if a part has a name in the params, the above code would have found it, so to get here means
				// we MUST have a attachment with NO NAME - but it still should be treated as an attachment
				$part_nice[$i]['ex_attachment'] = True;
				// BUT we have no idea of it's name, and *maybe* no idea of it's content type (eg. name.gif = image/gif)
				// sometimes the name's extention is the only info we have, i.e. ".doc" implies a WORD file
				//$part_nice['ex_part_name'] = 'no_name.att';
			}
			
			// ------  MIME PART CATAGORIZATION  -------
			
			// POSSIBLE VALUES FOR ['m_description'] ARE:
			//	container
			//	packagelist
			//	presentable/image
			//	attachment
			//	presentable
		
			// RULES:
			// a) if no subpart(s) then we have either "presentable" or "attachment"
			// b) if subpart(s) and a boundary param, then we have a "packagelist" (HeadersOnly)
			// c) else we have a container
			if ((int)$part_nice[$i]['ex_num_subparts'] < 1)
			{
				// a) if no subparts then we have either "presentable" or "attachment"
				if ($part_nice[$i]['ex_attachment'])
				{
					// fallback value pending the following test
					$part_nice[$i]['m_description'] = 'attachment';
					// does the "attachment" have a name with "image"type extension
					for ($p = 0; $p < count($part_nice[$i]['params']); $p++)
					{
						if ( (stristr($part_nice[$i]['params'][$p]['attribute'], 'name'))
						&& ((stristr($part_nice[$i]['params'][$p]['value'], '.JPG'))
						  || (stristr($part_nice[$i]['params'][$p]['value'], '.GIF'))
						  || (stristr($part_nice[$i]['params'][$p]['value'], '.PNG')) ) )
						{
							// we should attempt to inline display images
							$part_nice[$i]['m_description'] = 'presentable/image';
							break;
						}
					}
				}
				else
				{
					// not an attachment, nor an attachment that's an image for inline display
					$part_nice[$i]['m_description'] = 'presentable';
				}
			}
			elseif ($this->has_this_param($part_nice[$i]['params'], 'boundary'))
			{
				// b) if subpart(s) and a boundary param, then we have a "packagelist" (HeadersOnly)
				$part_nice[$i]['m_description'] = 'packagelist';
			}
			else
			{
				// c) else we have a container
				$part_nice[$i]['m_description'] = 'container';
			}
			
			// ------  KEYWORD LIST  -------
			
			// probably will be depreciated
			// at least for now, keywords "plain" and "html" are needed below
			$part_nice[$i]['m_keywords'] = '';
			if ((stristr($part_nice[$i]['subtype'], 'plain'))
			|| (stristr($part_nice[$i]['subtype'], 'html')))
			{
				$part_nice[$i]['m_keywords'] .= $part_nice[$i]['subtype'] .' ';
			}
			// encoding keyword is used below as well
			if ($part_nice[$i]['encoding'] != $not_set)
			{
				$part_nice[$i]['m_keywords'] .= $part_nice[$i]['encoding'] .' ';
			}
			
			// ------  MS "RELATED" FLAGGING  -------
			
			// Outl00k Stationary handling - where an HTML part has references to other parts (images) in it
			// initialize and prepare for the following mime exceptions code
			$part_nice[$i]['m_html_related_kids'] = False;
			$parent_idx = $part_nice[$i]['ex_parent_flat_idx'];
			if (($part_nice[$i]['ex_level_debth'] > 1)  // does not apply to level1, b/c level1 has no parent
			&& ($part_nice[$i]['type'] == 'multipart')
			&& ($part_nice[$i]['subtype'] == 'alternative')
			&& ($part_nice[$parent_idx]['type'] == 'multipart')
			&& ($part_nice[$parent_idx]['subtype'] == 'related'))
			{
				// SET THIS FLAG: then, in presentation loop, see if a HTML part 
				// has a parent with this flag - if so, replace "id" reference(s) with 
				// http... mime reference(s). Example: MS Stationary mail's image background
				$part_nice[$i]['m_html_related_kids'] = True;
				$part_nice[$i]['m_keywords'] .= 'id_swap' .' ';
			}
			
			// ------  EXCEPTIONS TO THE RULES  -------

			// = = = = =  Exceptions for Less-Standart Subtypes = = = = =
			//"m_description" set above will work *most all* the time. However newer standards
			// are encouraged to make use of the "subtype" param, not create new "type"s 
			// the following "multipart/SUBTYPES" should be treated as
			// "container" instead of "packagelist"
			
			// (1) Exception: multipart/APPLEDOUBLE  (ex. mac thru X.400 gateway)
			// treat as "container", not as "packagelist"
			if (($part_nice[$i]['type'] == 'multipart')
			&& ($part_nice[$i]['subtype'] == 'appledouble'))
			{
				$part_nice[$i]['m_description'] = 'container';
				$part_nice[$i]['m_keywords'] .= 'Force Container' .' ';
			}
			
			// ------  MAKE "SMART" MIME PART NUMBER  -------
			
			// ---Use Mime Number Dumb To Make ex_mime_number_smart
			$new_mime_dumb = $part_nice[$i]['ex_mime_number_dumb'];
			$part_nice[$i]['ex_mime_number_smart'] = $this->mime_number_smart($part_nice, $i, $new_mime_dumb);
			
			// -----   Make Smart Mime Number THE PRIMARY MIME NUMBER we will use
			//$part_nice[$i]['m_part_num_mime'] = $part_nice[$i]['ex_mime_number_smart'];

			// TEMPORARY HACK FOR SOCKET POP3 CLASS - feed it DUMB mime part numbers
			if ((isset($this->dcom->imap_builtin))
			&& ($this->dcom->imap_builtin == False)
			&& (stristr($GLOBALS['phpgw_info']['user']['preferences']['email']['mail_server_type'], 'pop3')))
			{
				// Make ***DUMB*** Mime Number THE PRIMARY MIME NUMBER we will use
				$part_nice[$i]['m_part_num_mime'] = $part_nice[$i]['ex_mime_number_dumb'];
			}
			else
			{
				// Make Smart Mime Number THE PRIMARY MIME NUMBER we will use
				$part_nice[$i]['m_part_num_mime'] = $part_nice[$i]['ex_mime_number_smart'];
			}
			
			// ------  MAKE CLICKABLE HREF TO THIS PART  -------
			
			// make an URL and a Clickable Link to directly acces this part
			$click_info = $this->make_part_clickable($part_nice[$i], $this->folder, $this->msgnum);
			$part_nice[$i]['ex_part_href'] = $click_info['part_href'];
			$part_nice[$i]['ex_part_clickable'] = $click_info['part_clickable'];
		}		
		
		// finally, return the customized flat phpgw msg structure array
		return $part_nice;
	}

	function pgw_msg_struct($part, $parent_flat_idx, $feed_dumb_mime, $feed_i, $feed_loops, $feed_debth)
	{
		if (isset($this->not_set))
		{
			$not_set = $this->not_set;
		}
		else
		{
			$not_set = '-1';
		}
		
		//echo 'BEGIN pgw_msg_struct<br>';
		//echo var_dump($part);
		//echo '<br>';
		
		// TRANSLATE PART STRUCTURE CONSTANTS INTO STRINGS OR TRUE/FALSE
		// see php manual page function.imap-fetchstructure.php
		
		// 1: TYPE
		$part_nice['type'] = $not_set; // Default value if not filled
		if (isset($part->type) && $part->type)
		{
			switch ($part->type)
			{
				case TYPETEXT		: $part_type = 'text'; break;
				case TYPEMULTIPART	: $part_type = 'multipart'; break;
				case TYPEMESSAGE		: $part_type = 'message'; break;
				case TYPEAPPLICATION	: $part_type = 'application'; break;
				case TYPEAUDIO		: $part_type = 'audio'; break;
				case TYPEIMAGE		: $part_type = 'image'; break;
				case TYPEVIDEO		: $part_type = 'video'; break;
				//case TYPEMODEL:		$part_type = "model"; break;
				// TYPEMODEL is not supported as of php v 4
				case TYPEOTHER		: $part_type = 'other'; break;
				default			: $part_type = 'unknown';
			}
			$part_nice['type'] = $part_type;
		}
		
		// 2: ENCODING
		$part_nice['encoding'] = $not_set; // Default value if not filled
		if (isset($part->encoding) && $part->encoding)
		{
			switch ($part->encoding)
			{
				case ENC7BIT		: $part_encoding = '7bit'; break;
				case ENC8BIT		: $part_encoding = '8bit'; break;
				case ENCBINARY		: $part_encoding = 'binary';  break;
				case ENCBASE64		: $part_encoding = 'base64'; break;
				//case ENCQUOTEDPRINTABLE : $part_encoding = 'quoted-printable'; break;
				case ENCQUOTEDPRINTABLE 	: $part_encoding = 'qprint'; break;
				case ENCOTHER		: $part_encoding = 'other';  break;
				case ENCUU		: $part_encoding = 'uu';  break;
				default			: $part_encoding = 'other';
			}
			$part_nice['encoding'] = $part_encoding;
		}
		// 3: IFSUBTYPE : true if there is a subtype string (SKIP)
		// 4: MIME subtype if the above is true, already in string form
		$part_nice['subtype'] = $not_set; // Default value if not filled
		if ((isset($part->ifsubtype)) && ($part->ifsubtype)
		&& (isset($part->subtype)) && ($part->subtype) )
		{
			$part_nice['subtype'] = $part->subtype;
			// this header item is not case sensitive
			$part_nice['subtype'] = trim(strtolower($part_nice['subtype']));
		}
		//5: IFDESCRIPTION : true if there is a description string (SKIP)
		// 6: Content Description String, if the above is true
		$part_nice['description'] = $not_set; // Default value if not filled
		if ((isset($part->ifdescription)) && ($part->ifdescription)
		&& (isset($part->description)) && ($part->description) )
		{
			$part_nice['description'] = $part->description;
		}
		// 7:  ifid : True if there is an identification string (SKIP)
		// 8: id : Identification string  , if the above is true
		$part_nice['id'] = $not_set; // Default value if not filled
		if ( (isset($part->ifid)) && ($part->ifid)
		&& (isset($part->id)) && ($part->id) )
		{
			$part_nice['id'] = trim($part->id);
		}
		// 9: lines : Number of lines
		$part_nice['lines'] = $not_set; // Default value if not filled
		if ((isset($part->lines)) && ($part->lines))
		{
			$part_nice['lines'] = $part->lines;
		}
		// 10:  bytes : Number of bytes
		$part_nice['bytes'] = $not_set; // Default value if not filled
		if ((isset($part->bytes)) && ($part->bytes))
		{
			$part_nice['bytes'] = $part->bytes;
		}
		// 11:  ifdisposition : True if there is a disposition string (SKIP)
		// 12:  disposition : Disposition string  ,  if the above is true
		$part_nice['disposition'] = $not_set; // Default value if not filled
		if ( (isset($part->ifdisposition)) && ($part->ifdisposition)
		&& (isset($part->disposition)) && ($part->disposition) )
		{
			$part_nice['disposition'] = $part->disposition;
			// this header item is not case sensitive
			$part_nice['disposition'] = trim(strtolower($part_nice['disposition']));
		}
		//13:  ifdparameters : True if the dparameters array exists SKIPPED -  ifparameters is more useful (I think)
		//14:  dparameters : Disposition parameter array SKIPPED -  parameters is more useful (I think)
		// 15:  ifparameters : True if the parameters array exists (SKIP)
		// 16:  parameters : MIME parameters array  - this *may* have more than a single attribute / value pair  but I'm not sure
		// ex_num_param_pairs defaults to 0 (no params)
		$part_nice['ex_num_param_pairs'] = 0;
		if ( (isset($part->ifparameters)) && ($part->ifparameters)
		&& (isset($part->parameters)) && ($part->parameters) )
		{
			// Custom/Extra Information (ex_):  ex_num_param_pairs
			$part_nice['ex_num_param_pairs'] = count($part->parameters);
			// capture data from all param attribute=value pairs
			for ($pairs = 0; $pairs < $part_nice['ex_num_param_pairs']; $pairs++)
			{
				$part_params = $part->parameters[$pairs];
				$part_nice['params'][$pairs]['attribute'] = $not_set; // default / fallback
				if ((isset($part_params->attribute) && ($part_params->attribute)))
				{
					$part_nice['params'][$pairs]['attribute'] = $part_params->attribute;
					$part_nice['params'][$pairs]['attribute'] = trim(strtolower($part_nice['params'][$pairs]['attribute']));
				}
				$part_nice['params'][$pairs]['value'] = $not_set; // default / fallback
				if ((isset($part_params->value) && ($part_params->value)))
				{
					$part_nice['params'][$pairs]['value'] = $part_params->value;
					// stuff like file names should retain their case
					//$part_nice['params'][$pairs]['value'] = strtolower($part_nice['params'][$pairs]['value']);
				}
			}
		}
		// 17:  parts : Array of objects describing each message part to this part
		// (i.e. embedded MIME part(s) within a wrapper MIME part)
		// key 'ex_' = CUSTOM/EXTRA information
		$part_nice['ex_num_subparts'] = $not_set;
		$part_nice['subpart'] = Array();
		if (isset($part->parts) && $part->parts)
		{
			$num_subparts = count($part->parts);
			$part_nice['ex_num_subparts'] = $num_subparts;
			for ($p = 0; $p < $num_subparts; $p++)
			{
				$part_subpart = $part->parts[$p];
				$part_nice['subpart'][$p] = $part_subpart;
			}
		}
		// ADDITIONAL INFORMATION (often uses array key "ex_" )
		
		// "dumb" mime part number based only on array position, will be made "smart" later
		$part_nice['ex_mime_number_dumb'] = $feed_dumb_mime;
		$part_nice['ex_parent_flat_idx'] = $parent_flat_idx;
		// Iteration Tracking
		$part_nice['ex_level_iteration'] = $feed_i;
		$part_nice['ex_level_max_loops'] = $feed_loops;
		$part_nice['ex_level_debth'] = $feed_debth;
		
		//echo 'BEGIN DUMP<br>';
		//echo var_dump($part_nice);
		//echo '<br>END DUMP<br>';
		
		return $part_nice;
	}


	function mime_number_smart($part_nice, $flat_idx, $new_mime_dumb)
	{
		if (isset($this->not_set))
		{
			$not_set = $this->not_set;
		}
		else
		{
			$not_set = '-1';
		}
		
		// ---- Construct a "Smart" mime number
		
		//$debug = True;
		$debug = False;
		//if (($flat_idx >= 25) && ($flat_idx <= 100))
		//{
		//	$debug = True;
		//}
		
		if ($debug) { echo 'ENTER mime_number_smart<br>'; }
		if ($debug) { echo 'fed var flat_idx: '. $flat_idx.'<br>'; }
		if ($debug) { echo 'fed var new_mime_dumb: '. $new_mime_dumb.'<br>'; }
		//error check
		if ($new_mime_dumb == $not_set)
		{
			$smart_mime_number = 'error 1 in mime_number_smart';
			break;
		}
		
		// explode new_mime_dumb into an array
		$exploded_mime_dumb = Array();
		if (strlen($new_mime_dumb) == 1)
		{
			if ($debug) { echo 'strlen(new_mime_dumb) = 1 :: TRUE ; FIRST debth level<br>'; }
			$exploded_mime_dumb[0] = (int)$new_mime_dumb;
		}
		else
		{
			if ($debug) { echo 'strlen(new_mime_dumb) = 1 :: FALSE<br>'; }
			$exploded_mime_dumb = explode('.', $new_mime_dumb);
		}
		
		// cast all values in exploded_mime_dumb as integers
		for ($i = 0; $i < count($exploded_mime_dumb); $i++)
		{
			$exploded_mime_dumb[$i] = (int)$exploded_mime_dumb[$i];
		}
		if ($debug) { echo 'exploded_mime_dumb '.serialize($exploded_mime_dumb).'<br>'; }
		
		// make an array of all parts of this family tree,  from the current part (the outermost) to innermost (closest to debth level 1)
		$dumbs_part_nice = Array();
		//loop BACKWARDS
		for ($i = count($exploded_mime_dumb) - 1; $i > -1; $i--)
		{
			if ($debug) { echo 'exploded_mime_dumb reverse loop i=['.$i.']<br>'; }
			// is this the outermost (current) part ?
			if ($i == (count($exploded_mime_dumb) - 1))
			{
				$dumbs_part_nice[$i] = $part_nice[$flat_idx];
				if ($debug) { echo '(outermost/current part) dumbs_part_nice[i('.$i.')] = part_nice[flat_idx('.$flat_idx.')]<br>'; }
				//if ($debug) { echo ' - prev_parent_flat_idx: '.$prev_parent_flat_idx.'<br>'; }
			}
			else
			{
				$this_dumbs_idx = $dumbs_part_nice[$i+1]['ex_parent_flat_idx'];
				$dumbs_part_nice[$i] = $part_nice[$this_dumbs_idx];
				if ($debug) { echo 'dumbs_part_nice[i('.$i.')] = part_nice[this_dumbs_idx('.$this_dumbs_idx.')]<br>'; }
			}
		}
		//if ($debug) { echo 'dumbs_part_nice serialized: '.serialize($dumbs_part_nice) .'<br>'; }
		//if ($debug) { echo 'serialize exploded_mime_dumb: '.serialize($exploded_mime_dumb).'<br>'; }
		
		// NOTE:  Packagelist -> Container EXCEPTION Conversions
		// a.k.a "Exceptions for Less-Standart Subtypes"
		// are located in the analysis loop done that BEFORE you enter this function
		
		// Reconstruct the Dumb Mime Number string into a "SMART" Mime Number string
		// RULE:  Dumb Mime parts that have "m_description" = "packagelist" (i.e. it's a header part)
		//	should be ommitted when constructing the Smart Mime Number
		// WITH 2 EXCEPTIONS:
		//	(a) debth 1 parts that are "packagelist" *never* get altered in any way
		//	(b) outermost debth parts that are "packagelist" get a value of "0", not ommitted
		//	(c) for 2 "packagelist"s in sucession, the first one gets a "1", not ommitted
		
		// apply the rules
		$smart_mime_number_array = Array();
		for ($i = 0; $i < count($dumbs_part_nice); $i++)
		{
			if (((int)$dumbs_part_nice[$i]['ex_level_debth'] == 1)
			|| ($i == 0))
			{
				// debth 1 part numbers are never altered
				$smart_mime_number_array[$i] = $exploded_mime_dumb[$i];
			}
			// is this the outermost level (i.e. the last dumb mime number)
			elseif ($i == (count($exploded_mime_dumb) - 1))
			{
				// see outermost rule above
				if ($dumbs_part_nice[$i]['m_description'] == 'packagelist')
				{
					// it gets a value of zero
					$smart_mime_number_array[$i] = 0;
				}
				else
				{
					// no need to change
					$smart_mime_number_array[$i] = $exploded_mime_dumb[$i];
				}
			}
			// we covered the exceptions, now apply the ommiting rule
			else
			{
				if ($dumbs_part_nice[$i]['m_description'] == 'packagelist')
				{
					// mark this for later removal (ommition)
					$smart_mime_number_array[$i] = $not_set;
				}
				else
				{
					// no need to change
					$smart_mime_number_array[$i] = $exploded_mime_dumb[$i];
				}
			}
		}
		
		// for 2 "packagelist"s in sucession, the first one gets a "1", not ommitted
		for ($i = 0; $i < count($dumbs_part_nice); $i++)
		{
			if (($i > 0) // not innermost
			&& ($dumbs_part_nice[$i]['m_description'] == 'packagelist')
			&& ($dumbs_part_nice[$i-1]['m_description'] == 'packagelist'))
			{
				$smart_mime_number_array[$i-1] = 1;
			}
		}
		
		// make the "smart mime number" based on the info gathered and the above rules
		// as applied to the smart_mime_number_array
		$smart_mime_number = '';
		for ($i = 0; $i < count($smart_mime_number_array); $i++)
		{
			if ($smart_mime_number_array[$i] != $not_set)
			{
				$smart_mime_number = $smart_mime_number . (string)$smart_mime_number_array[$i];
				// we  add a dot "." if this is not the outermost debth level
				if ($i != (count($smart_mime_number_array) - 1))
				{
					$smart_mime_number = $smart_mime_number . '.';
				}
			}
		}
		if ($debug) { echo 'FINAL smart_mime_number: '.$smart_mime_number.'<br><br>'; }
		return $smart_mime_number;
	}

	function make_part_clickable($part_nice, $folder, $msgnum)
	{
		if (isset($this->not_set))
		{
			$not_set = $this->not_set;
		}
		else
		{
			$not_set = '-1';
		}
		
		// Part Number used to request parts from the server
		$m_part_num_mime = $part_nice['m_part_num_mime'];
		
		$part_name = $part_nice['ex_part_name'];
		
		// make a URL to directly access this part
		if ($part_nice['type'] != $not_set)
		{
			$url_part_type = $part_nice['type'];
		}
		else
		{
			$url_part_type = 'unknown';
		}
		if ($part_nice['subtype'] != $not_set)
		{
			$url_part_subtype = $part_nice['subtype'];
		}
		else
		{
			$url_part_subtype = 'unknown';
		}
		if ($part_nice['encoding'] != $not_set)
		{
			$url_part_encoding = $part_nice['encoding'];
		}
		else
		{
			$url_part_encoding = 'other';
		}
		// make a URL to directly access this part
		$url_part_name = urlencode($part_name);
		// ex_part_href
		$ex_part_href = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/get_attach.php',
			 'folder='.$this->prep_folder_out($folder)
			.'&msgnum=' .$msgnum
			.'&part_no=' .$m_part_num_mime
			.'&type=' .$url_part_type
			.'&subtype=' .$url_part_subtype
			.'&name=' .$url_part_name
			.'&encoding=' .$url_part_encoding); 
		// Make CLICKABLE link directly to this attachment or part
		$href_part_name = $this->decode_header_string($part_name);
		// ex_part_clickable
		$ex_part_clickable = '<a href="'.$ex_part_href.'">'.$href_part_name.'</a>';
		// put these two vars in an array, and pass it back to the calling process
		$click_info = Array();
		$click_info['part_href'] = $ex_part_href;
		$click_info['part_clickable'] = $ex_part_clickable;
		return $click_info;
	}

	// function make_clickable taken from text_to_links() in the SourceForge Snipplet Library
	// http://sourceforge.net/snippet/detail.php?type=snippet&id=100004
	// modified to make mailto: addresses compose in phpGW
	function make_clickable($data, $folder)
	{
		if(empty($data))
		{
			return $data;
		}

		$lines = split("\n",$data);

		while ( list ($key,$line) = each ($lines))
		{
			$line = eregi_replace("([ \t]|^)www\."," http://www.",$line);
			$line = eregi_replace("([ \t]|^)ftp\."," ftp://ftp.",$line);
			$line = eregi_replace("(http://[^ )\r\n]+)","<A href=\"\\1\" target=\"_new\">\\1</A>",$line);
			$line = eregi_replace("(https://[^ )\r\n]+)","<A href=\"\\1\" target=\"_new\">\\1</A>",$line);
			$line = eregi_replace("(ftp://[^ )\r\n]+)","<A href=\"\\1\" target=\"_new\">\\1</A>",$line);
			$line = eregi_replace("([-a-z0-9_]+(\.[_a-z0-9-]+)*@([a-z0-9-]+(\.[a-z0-9-]+)+))",
				"<a href=\"".$GLOBALS['phpgw']->link("/".$GLOBALS['phpgw_info']['flags']['currentapp']."/compose.php","folder=".$GLOBALS['phpgw']->msg->prep_folder_out($folder))
				."&to=\\1\">\\1</a>", $line);

			$newText .= $line . "\n";
		}
		return $newText;
	}

	function has_this_param($param_array, $needle='')
	{
		if ((!isset($param_array))
		|| (count($param_array) < 1)
		|| ($needle == ''))
		{
			return False;
		}
		elseif (isset($param_array[0]['attribute']))
		{
			// we have a phpgw flat part array input
			for ($p = 0; $p < count($param_array); $p++)
			{
				if (stristr($param_array[$p]['attribute'], $needle))
				{
					return True;
					// implicit break with that return
				}
			}
		}
		elseif (isset($param_array[0]->attribute))
		{
			// we have a PHP fetchstructure input
			for ($p = 0; $p < count($param_array); $p++)
			{
				if (stristr($param_array[$p]->attribute, $needle))
				{
					return True;
					// implicit break with that return
				}
			}
		}
		else
		{
			return False;
		}
	}

	function array_keys_str($my_array)
	{
		$all_keys = Array();
		$all_keys = array_keys($my_array);
		return implode(', ',$all_keys);
	}
} // end class mail_msg
?>
