<?php
  /**
   ** read_body.php
   **
   **  Copyright (c) 1999-2000 The SquirrelMail development team
   **  Licensed under the GNU GPL. For full terms see the file COPYING.
   **
   **  This file is used for reading the msgs array and displaying
   **  the resulting emails in the right frame.
   **
   **  $Id$
   **/

	$enablePHPGW = 1;

	// store the value of $mailbox, because it will overwriten
	$MAILBOX = $mailbox;
	$phpgw_info['flags'] = array('currentapp' => 'felamimail');
	include('../header.inc.php');
	$mailbox = $MAILBOX;

	$phpgw->session->restore();

	if (!isset($strings_php))
	{
		include(PHPGW_APP_ROOT . '/inc/strings.php');
	}
	
	if (!isset($config_php))
	{
		include(PHPGW_APP_ROOT . '/config/config.php');
	}

	if (!isset($page_header_php))
	{
		include(PHPGW_APP_ROOT . '/inc/page_header.php');
	}

	if (!isset($imap_php))
	{
		include(PHPGW_APP_ROOT . '/inc/imap.php');
	}

	if (!isset($mime_php))
	{
		include(PHPGW_APP_ROOT . '/inc/mime.php');
	}

	if (!isset($date_php))
	{
		include(PHPGW_APP_ROOT . '/inc/date.php');
	}

	if (!isset($url_parser_php)) 
	{
		include(PHPGW_APP_ROOT . '/inc/url_parser.php');
	}

	include(PHPGW_APP_ROOT . '/src/load_prefs.php');
	

	function createAddressbookButton($_addressString)
	{
   		global $phpgw, $phpgw_info, $PHP_SELF, $QUERY_STRING;
   		
   		// if last char == , => remove it
   		if (strrpos ($_addressString, ",")+1 == strlen($_addressString))
   		{
   			$_addressString = substr($_addressString,0, strlen($_addressString)-1);
   		}
   		
   		#print "$_addressString<br>";
   		
		// "Lars Kneschke" <lars@kneschke.de>
		if (preg_match("/^\"(.*)\".*<(.*)>/i",$_addressString , $matches))
		{
			$linkData = array
			(
				'menuaction'	=> 'addressbook.uiaddressbook.add_email',
				'add_email'	=> urlencode($matches[2]),
				'name'		=> urlencode($matches[1]),
				'referer'	=> urlencode($PHP_SELF.'?'.$QUERY_STRING)
			);
			$link = $phpgw->link('/index.php',$linkData);
			$image = '<img src="'.PHPGW_IMAGES.
				 	'/sm_envelope.gif" width="10" height="8" 
				 	alt="'.lang("Add to address book").'" border="0" align="absmiddle">';
			return sprintf('&nbsp;<a href="%s">%s</a>',
					$link, $image);
		}
		// Lars Kneschke <lars@kneschke.de>
		elseif (preg_match("/^(.*).*<(.*)>/i",$_addressString , $matches))
		{
			$linkData = array
			(
				'menuaction'	=> 'addressbook.uiaddressbook.add_email',
				'add_email'	=> urlencode($matches[2]),
				'name'		=> urlencode($matches[1]),
				'referer'	=> urlencode($PHP_SELF.'?'.$QUERY_STRING)
			);
			$link = $phpgw->link('/index.php',$linkData);
			$image = '<img src="'.PHPGW_IMAGES.
				 	'/sm_envelope.gif" width="10" height="8" 
				 	alt="'.lang("Add to address book").'" border="0" align="absmiddle">';
			return sprintf('&nbsp;<a href="%s">%s</a>',
					$link, $image);
		}
		// lars@kneschke.de (Lars Kneschke)
		elseif (preg_match("/^(.*@.*).*\((.*)\)/i",$_addressString , $matches))
		{
			$linkData = array
			(
				'menuaction'	=> 'addressbook.uiaddressbook.add_email',
				'add_email'	=> urlencode($matches[2]),
				'name'		=> urlencode($matches[1]),
				'referer'	=> urlencode($PHP_SELF.'?'.$QUERY_STRING)
			);
			$link = $phpgw->link('/index.php',$linkData);
			$image = '<img src="'.PHPGW_IMAGES.
				 	'/sm_envelope.gif" width="10" height="8" 
				 	alt="'.lang("Add to address book").'" border="0" align="absmiddle">';
			return sprintf('&nbsp;<a href="%s">%s</a>',
					$link, $image);
		}
		// lars@kneschke.de
		elseif (preg_match("/^(.*@.*)/i",$_addressString , $matches))
		{
			$linkData = array
			(
				'menuaction'	=> 'addressbook.uiaddressbook.add_email',
				'add_email'	=> urlencode($matches[1]),
				'referer'	=> urlencode($PHP_SELF.'?'.$QUERY_STRING)
			);
			$link = $phpgw->link('/index.php',$linkData);
			$image = '<img src="'.PHPGW_IMAGES.
				 	'/sm_envelope.gif" width="10" height="8" 
				 	alt="'.lang("Add to address book").'" border="0" align="absmiddle">';
			return sprintf('&nbsp;<a href="%s">%s</a>',
					$link, $image);
		}
	}

	function fillDataArray($_imapConnection, $_passed_id, $_mailbox)
	{
		global $phpgw, $where, $what, $show_more, $sort, $startMessage, $show_more_cc;
	
		// $message contains all information about the message
		// including header and body
		$message = sqimap_get_message($_imapConnection, $_passed_id, $_mailbox);
		$data["passed_id"] = $passed_id = $_passed_id;
		$data["mailbox"] = $_mailbox;
		
		/** translate the subject and mailbox into url-able text **/
		$data["subject"] = $message->header->subject;
		$data["url_subj"] = urlencode(trim(sqStripSlashes($message->header->subject)));
		$data["urlMailbox"] = $urlMailbox = urlencode($_mailbox);
		$data["url_replyto"] = urlencode($message->header->replyto);
		$data["url_replytoall"] = urlencode($message->header->replyto);
		
		// If we are replying to all, then find all other addresses and
		// add them to the list.  Remove duplicates.
		// This is somewhat messy, so I'll explain:
		// 1) Take all addresses (from, to, cc) (avoid nasty join errors here)
		$url_replytoall_extra_addrs = array_merge(array($message->header->from),
						$message->header->to, $message->header->cc);
							
		// 2) Make one big string out of them
		$url_replytoall_extra_addrs = join(';', $url_replytoall_extra_addrs);
		
		// 3) Parse that into an array of addresses
		$url_replytoall_extra_addrs = parseAddrs($url_replytoall_extra_addrs);
		
		// 4) Make them unique -- weed out duplicates
		// (Coded for PHP 4.0.0)
		$url_replytoall_extra_addrs = array_keys(array_flip($url_replytoall_extra_addrs));
		$data["url_replytoall_extra_addrs"] = array_keys(array_flip($url_replytoall_extra_addrs));
		
		// 5) Remove the addresses we'll be sending the message 'to'
		$url_replytoall_avoid_addrs = parseAddrs($message->header->replyto);
		foreach ($url_replytoall_avoid_addrs as $addr)
		{
			foreach (array_keys($url_replytoall_extra_addrs, $addr) as $key_to_delete)
			{
				unset($url_replytoall_extra_addrs[$key_to_delete]);
			}
		}
		$data["url_replytoall_avoid_addrs"] = $url_replytoall_extra_addrs;
		
		// 6) Smoosh back into one nice line
		$url_replytoallcc = getLineOfAddrs($url_replytoall_extra_addrs);
		
		// 7) urlencode() it
		$data["url_replytoallcc"] = urlencode($url_replytoallcc);
		
		#$data["dateString"] = getLongDateString($message->header->date);
		$data["dateString"] = $phpgw->common->show_date($message->header->date);
		
		$ent_num = findDisplayEntity($message);
		$data["ent_num"] = findDisplayEntity($message);
		
		/** TEXT STRINGS DEFINITIONS **/
		$echo_more = lang("more");
		$echo_less = lang("less");
		
		/** FORMAT THE TO STRING **/
		$i = 0;
		$to_string = "";
		$to_ary = $message->header->to;
		while ($i < count($to_ary)) 
		{
			$currentVal = decodeHeader($to_ary[$i]);
			$to_ary[$i] = htmlspecialchars(decodeHeader($to_ary[$i]));
			
			if ($to_string)
				$to_string = "$to_string<BR>$to_ary[$i]".createAddressbookButton($currentVal);
			else
				$to_string = "$to_ary[$i]".createAddressbookButton($currentVal);
				
			$i++;
			if (count($to_ary) > 1) 
			{
				if ($show_more == false) 
				{
					if ($i == 1) 
					{
						if (isset($where) && isset($what)) 
						{
							// from a search
							$to_string = "$to_string&nbsp;(<A HREF=\"" . $phpgw->link('/felamimail/read_body.php',"mailbox=$urlMailbox&passed_id=$passed_id&where=".urlencode($where)."&what=".urlencode($what)."&show_more=1&show_more_cc=$show_more_cc") . "\">$echo_more</A>)";
						} 
						else 
						{
							$to_string = "$to_string&nbsp;(<A HREF=\"" . $phpgw->link('/felamimail/read_body.php',"mailbox=$urlMailbox&passed_id=$passed_id&sort=$sort&startMessage=$startMessage&show_more=1&show_more_cc=$show_more_cc") . "\">$echo_more</A>)";
						}
						$i = count($to_ary);
					}
				} 
				else if ($i == 1) 
				{
					if (isset($where) && isset($what)) 
					{
						// from a search
						$to_string = "$to_string&nbsp;(<A HREF=\"" . $phpgw->link('/felamimail/read_body.php',"mailbox=$urlMailbox&passed_id=$passed_id&where=".urlencode($where)."&what=".urlencode($what)."&show_more=0&show_more_cc=$show_more_cc") . "\">$echo_less</A>)";
					} 
					else 
					{
						$to_string = "$to_string&nbsp;(<A HREF=\"" . $phpgw->link('/felamimail/read_body.php',"mailbox=$urlMailbox&passed_id=$passed_id&sort=$sort&startMessage=$startMessage&show_more=0&show_more_cc=$show_more_cc") . "\">$echo_less</A>)";
					}
				}
			}
		}
		$data["to_string"] = $to_string;
		
		/** FORMAT THE CC STRING **/
		$i = 0;
		if (isset ($message->header->cc[0]) && trim($message->header->cc[0]))
		{
			$cc_string = "";
			$cc_ary = $message->header->cc;
			while ($i < count(decodeHeader($cc_ary))) 
			{
				$currentVal = decodeHeader($cc_ary[$i]);
				$cc_ary[$i] = htmlspecialchars($cc_ary[$i]);
				if ($cc_string)
					$cc_string = "$cc_string<BR>$cc_ary[$i]".createAddressbookButton($currentVal);
				else
					$cc_string = "$cc_ary[$i]".createAddressbookButton($currentVal);
					
				$i++;
				if (count($cc_ary) > 1) 
				{
					if ($show_more_cc == false) 
					{
						if ($i == 1) 
						{
							if (isset($where) && isset($what)) 
							{
								// from a search
								$cc_string = "$cc_string&nbsp;(<A HREF=\"" . $phpgw->link('/felamimail/read_body.php',"mailbox=$urlMailbox&passed_id=$passed_id&what=".urlencode($what)."&where=".urlencode($where)."&show_more_cc=1&show_more=$show_more") . "\">$echo_more</A>)";
							} 
							else 
							{
								$cc_string = "$cc_string&nbsp;(<A HREF=\"" . $phpgw->link('/felamimail/read_body.php',"mailbox=$urlMailbox&passed_id=$passed_id&sort=$sort&startMessage=$startMessage&show_more_cc=1&show_more=$show_more") . "\">$echo_more</A>)";
							}
							$i = count($cc_ary);
						}
					} 
					else if ($i == 1) 
					{
						if (isset($where) && isset($what)) 
						{
							// from a search
							$cc_string = "$cc_string&nbsp;(<A HREF=\"" . $phpgw->link('/felamimail/read_body.php',"mailbox=$urlMailbox&passed_id=$passed_id&what=".urlencode($what)."&where=".urlencode($where)."&show_more_cc=0&show_more=$show_more") . "\">$echo_less</A>)";
						} 
						else 
						{
							$cc_string = "$cc_string&nbsp;(<A HREF=\"" . $phpgw->link('/felamimail/read_body.php',"mailbox=$urlMailbox&passed_id=$passed_id&sort=$sort&startMessage=$startMessage&show_more_cc=0&show_more=$show_more") . "\">$echo_less</A>)";
						}
					}
				}
			}
		}
		$data["cc_string"] = $cc_string;
		/** make sure everything will display in HTML format **/
		$data["from_name"] = decodeHeader(htmlspecialchars($message->header->from));
		$data["from_name"] .= createAddressbookButton($message->header->from);
		$data["subject"] = decodeHeader(htmlspecialchars($message->header->subject));
		
		return $data;
	
	}

	// given an IMAP message id number, this will look it up in the cached and sorted msgs array and
	//    return the index.  used for finding the next and previous messages
	
	// returns the index of the next valid message from the array or -1 if not found
	function findNextMessage($_mailboxStatus, $_mailbox, $_currentArrayIndex) 
	{
		$sortedList = $_mailboxStatus["$_mailbox"]["sortedList"];
		
		for ($i = 0; $i < count($sortedList); $i++)
		{
			if ($sortedList[$i] == $_currentArrayIndex && $sortedList[$i+1])
			{
				#print (count($sortedList)-$i)-1;
				#print "&nbsp;&nbsp;&nbsp;&nbsp;";
				return $sortedList[$i+1];
			}
		}
		return -1;
	}
	
	// returns the index of the previous message from the array or -1 if not found
	function findPreviousMessage($_mailboxStatus, $_mailbox, $_currentArrayIndex) 
	{
		$sortedList = $_mailboxStatus["$_mailbox"]["sortedList"];
		
		for ($i = 0; $i < count($sortedList); $i++)
		{
			if ($sortedList[$i] == $_currentArrayIndex && $sortedList[$i-1])
			{
				#print $i."&nbsp;|&nbsp;";
				return $sortedList[$i-1];
			}
		}
		#print "0&nbsp;|&nbsp;";
		return -1;
	}

	function viewMessageHeader($_data)
	{
		global $phpgw, $phpgw_info, $imapConnection, $startMessage, $show_more;
		
		$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
		$t->set_file(array('view_message' => 'view_message.tpl'));
		$t->set_block('view_message','message_header','message_header');
		$t->set_block('view_message','B_cc_data','B_cc_data');
		
		$t->set_var('th_bg', $phpgw_info['theme']['th_bg']);
		$t->set_var('row_on', $phpgw_info['theme']['row_on']);
		$t->set_var('row_off', $phpgw_info['theme']['row_off']);
		$t->set_var('bg01', $phpgw_info['theme']['bg01']);
		$t->set_var('bg02', $phpgw_info['theme']['bg02']);
		$t->set_var('bg03', $phpgw_info['theme']['bg03']);
		
		$t->set_var('lang_from', lang('from'));
		$t->set_var('lang_to', lang('to'));
		$t->set_var('lang_cc', lang('cc'));
		$t->set_var('lang_date', lang('date'));
		$t->set_var('lang_files', lang('files'));
		$t->set_var('lang_subject', lang('subject'));
		
		$t->set_var('from_data', $_data["from_name"]);
		$t->set_var('to_data_final', $_data["to_string"]);
		$t->set_var('subject_data', $_data["subject"]);
		$t->set_var('date_data', $_data["dateString"]);
		
		$urlMailbox = $_data["urlMailbox"];
		$where = $_data["where"];
		$what = $_data["what"];
		$passed_id = $_data["passed_id"];
		
		if (isset($where) && isset($what)) 
		{
			// Got here from a search
			$string = "<a href=\""
			. $phpgw->link('/felamimail/read_body.php',"mailbox=$urlMailbox&passed_id=$passed_id&where=".urlencode($where)."&what=".urlencode($what)."&view_hdr=1") . "\">" . lang("View full header") . "</a>";
		} 
		else 
		{
			$string =  "<a class=\"body_link\" href=\""
			. $phpgw->link('/felamimail/read_body.php',"mailbox=$urlMailbox&passed_id=$passed_id&startMessage=$startMessage&show_more=$show_more&view_hdr=1") . "\">" . lang("View full header") . "</a>";
			$t->set_var('view_header', $string);
		}

		if ($_data["cc_string"])
		{
			$t->set_var('cc_data_final',$_data["cc_string"]);
			$cc_data = $t->parse('out','B_cc_data');
			$t->set_var('cc_data',$cc_data);
			$t->set_var('bg_date', $phpgw_info['theme']['row_off']);
			$t->set_var('bg_subject', $phpgw_info['theme']['row_on']);
		}
		else
		{
			$t->set_var('cc_data','');
			$t->set_var('bg_date', $phpgw_info['theme']['row_on']);
			$t->set_var('bg_subject', $phpgw_info['theme']['row_off']);
		}

		return $t->parse('out','message_header');
	}

	function viewMessageNavbar($_mailbox, $_mailboxStatus, $_data, $_currentArrayIndex, $_sort, $_startMessage, $_uid)
	{
		global $phpgw, $phpgw_info, $imapConnection, $sent_folder;
		
		$svr_image_dir = PHPGW_IMAGES_DIR;
	
		$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
		$t->set_file(array('view_message' => 'view_message.tpl'));
		$t->set_block('view_message','message_navbar','message_navbar');
		#$t->set_block('view_message','column','column');
		
		$where = $_data["where"];
		$what = $_data["what"];
		$urlMailbox = $_data["urlMailbox"];
		$passed_id = $_data["passed_id"];
		$ent_num = $_data["ent_num"];
		$url_replyto = $_data["url_replyto"];
		$url_replytoall = $_data["url_replytoall"];
		$url_replytoallcc = $_data["url_replytoallcc"];
		$subject = $_data["subject"];
		$url_subj = $_data["url_subj"];
		
		if (isset($_where) && isset($_what))
		{
			// search filter
			$t->set_var('link_messagelist',$phpgw->link('/felamimail/search.php',"where=".urlencode($where)."&what=".urlencode($what)."&mailbox=$urlMailbox"));
			$t->set_var('link_delete',$phpgw->link('/felamimail/src/delete_message.php',"mailbox=$urlMailbox&message=$passed_id&where=".urlencode($where)."&what=".urlencode($what)));		
			$t->set_var('left_arrow','');
			$t->set_var('right_arrow','');
		}
		else
		{

			$t->set_var('link_messagelist',$phpgw->link('/felamimail/index.php',"sort=$_sort&startMessage=$_startMessage&mailbox=$urlMailbox"));
			$linkData = array
			(
				'menuaction'	=> 'felamimail.uifelamimail.deleteMessage',
				'mailbox' 	=> $urlMailbox,
				'message'	=> $_uid,
				'sort'		=> $_sort,
				'startMessage'	=> $_startMessage
				
			);
			$t->set_var('link_delete',$phpgw->link('/index.php',$linkData));

			if ($_currentArrayIndex == -1)
			{
				$t->set_var('left_arrow',"<img border=\"0\" src=\"$svr_image_dir/left-grey.gif\" alt=\"".lang("no previous Message")."\" title=\"".lang("no previous Message")."\">");
				$t->set_var('right_arrow',"<img border=\"0\" src=\"$svr_image_dir/right-grey.gif\" alt=\"".lang("no previous Message")."\" title=\"".lang("no previous Message")."\">");
			}
			else
			{
				$prev = findPreviousMessage($_mailboxStatus, $_mailbox, $_currentArrayIndex);
				$next = findNextMessage($_mailboxStatus, $_mailbox, $_currentArrayIndex);
				
				if ($prev != -1)
				{
					#$message = sqimap_get_message_header($imapConnection, $prev, $mailbox);
					if($mailbox == $sent_folder)
						$hdr = sqimap_get_small_header ($imapConnection, $prev, true);
					else
						$hdr = sqimap_get_small_header ($imapConnection, $prev, false);
					$purl_subj = decodeHeader($hdr->subject);
					$leftArrow  = "<a href=\"" . $phpgw->link('/felamimail/read_body.php',"passed_id=$prev&mailbox=$urlMailbox&sort=$_sort&startMessage=$_startMessage&show_more=0") . "\">";
					$leftArrow .= "<img border=\"0\" src=\"$svr_image_dir/left.gif\" alt=\"$purl_subj\" title=\"$purl_subj\">";
					$leftArrow .= "</a>";
					$t->set_var('left_arrow',$leftArrow);
				}
				else
				{
					$t->set_var('left_arrow',"<img border=\"0\" src=\"$svr_image_dir/left-grey.gif\" alt=\"".lang("no previous Message")."\" title=\"".lang("no previous Message")."\">");
				}
				
				if ($next != -1)
				{
					#$message = sqimap_get_message_header($imapConnection, $next, $mailbox);
					if($mailbox == $sent_folder)
						$hdr = sqimap_get_small_header ($imapConnection, $next, true);
					else
						$hdr = sqimap_get_small_header ($imapConnection, $next, false);
					$nurl_subj = decodeHeader($hdr->subject);
					$rightArrow  = "<a href=\"" . $phpgw->link('/felamimail/read_body.php',"passed_id=$next&mailbox=$urlMailbox&sort=$_sort&startMessage=$_startMessage&show_more=0") . "\">";
					$rightArrow .= "<img border=\"0\" src=\"$svr_image_dir/right.gif\" alt=\"$nurl_subj\" title=\"$nurl_subj\">";
					$rightArrow .= "</a>";
					$t->set_var('right_arrow',$rightArrow);
				}
				else
				{
					$t->set_var('right_arrow',"<img border=\"0\" src=\"$svr_image_dir/right-grey.gif\" alt=\"".lang("no next Message")."\" title=\"".lang("no next Message")."\">");
				}
			}
		}
		
		$linkData = array
		(
			'menuaction'	=> 'felamimail.uicompose.compose',
			'mailbox'	=> $urlMailbox
		);
		$t->set_var('link_compose',$GLOBALS['phpgw']->link('/index.php',$linkData));
		
		$linkData = array
		(
			'menuaction'	=> 'felamimail.uifelamimail.viewMainScreen',
			'mailbox'	=> $urlMailbox,
			'startMessage'	=> $GLOBALS['HTTP_GET_VARS']['startMessage'],
			'sort'		=> $GLOBALS['HTTP_GET_VARS']['sort']
		);
		$t->set_var("link_message_list",$GLOBALS['phpgw']->link('/index.php',$linkData));
		$t->set_var('folder_name',$urlMailbox);
		$langArray = array
			(
				'lang_messagelist'	=> lang('Message List'),
				'lang_compose'		=> lang('Compose'),
				'lang_delete'		=> lang('Delete'),
				'lang_forward'		=> lang('Forward'),
				'lang_reply'		=> lang('Reply'),
				'lang_reply_all'	=> lang('Reply All'),
				'lang_back_to_folder'	=> lang('back to folder'),
				'app_image_path'	=> PHPGW_IMAGES,
				'link_reply'		=> $phpgw->link('/felamimail/compose.php',"send_to=$url_replyto&reply_subj=$url_subj&reply_id=$passed_id&mailbox=$urlMailbox&ent_num=$ent_num"),
				'link_reply_all'	=> $phpgw->link('/felamimail/compose.php',"send_to=$url_replytoall&send_to_cc=$url_replytoallcc&reply_subj=$url_subj&reply_id=$passed_id&mailbox=$urlMailbox&ent_num=$ent_num"),
				'link_forward'		=> $phpgw->link('/felamimail/compose.php',"forward_id=$passed_id&forward_subj=$url_subj&mailbox=$urlMailbox&ent_num=$ent_num")
			);
			
		$t->set_var('th_bg', $phpgw_info['theme']['navbar_bg']);
		$t->set_var($langArray);
		
		return $t->parse('out','message_navbar');
	}


// here we go!


	$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
	sqimap_mailbox_select($imapConnection, $mailbox);
	do_hook("html_top");
	#displayPageHeader($color, $mailbox);
	
	
	if (isset($view_hdr)) 
	{
		fputs ($imapConnection, "a003 FETCH $passed_id BODY[HEADER]\r\n");
		$read = sqimap_read_data ($imapConnection, "a003", true, $a, $b);
		
		echo "<br>";
		echo "<table width=100% cellpadding=2 cellspacing=0 border=0 align=center>\n";
		echo "   <TR><TD BGCOLOR=\"$color[9]\" WIDTH=100%><center><b>" . lang("Viewing full header") . "</b> - ";
		if (isset($where) && isset($what)) 
		{
			// Got here from a search
			echo "<a href=\"" . $phpgw->link('/felamimail/read_body.php',"mailbox=".urlencode($mailbox)."&passed_id=$passed_id&where=".urlencode($where)."&what=".urlencode($what))."\">";
		} 
		else 
		{
			echo "<a href=\"" . $phpgw->link('/felamimail/read_body.php',"mailbox=".urlencode($mailbox)."&passed_id=$passed_id&startMessage=$startMessage&show_more=$show_more") . "\">";
		}
		echo "".lang("View message") . "</a></b></center></td></tr></table>\n";
		echo "<table width=99% cellpadding=2 cellspacing=0 border=0 align=center>\n";
		echo "<tr><td>";
		
		$cnum = 0;
		for ($i=1; $i < count($read)-1; $i++) 
		{
			$line = htmlspecialchars($read[$i]);
			if (eregi("^&gt;", $line)) 
			{
				$second[$i] = $line;
				$first[$i] = "&nbsp;";
				$cnum++;
			} 
			else if (eregi("^[ |\t]", $line)) 
			{
				$second[$i] = $line;
				$first[$i] = "";
			} 
			else if (eregi("^([^:]+):(.+)", $line, $regs)) 
			{
				$first[$i] = $regs[1] . ":";
				$second[$i] = $regs[2];
				$cnum++;
			} 
			else 
			{
				$second[$i] = trim($line);
				$first[$i] = "";
			}
		}
		for ($i=0; $i < count($second); $i = $j) 
		{
			if (isset($first[$i])) $f = $first[$i];
			if (isset($second[$i])) $s = nl2br($second[$i]);
			$j = $i + 1;
			while ($first[$j] == "" && $j < count($first)) 
			{
				$s .= "&nbsp;&nbsp;&nbsp;&nbsp;" . nl2br($second[$j]);
				$j++;
			}
			parseEmail($s);
			
			if (isset($f)) echo "<nobr><tt><b>$f</b>$s</tt></nobr>";
		}
		echo "</td></tr></table>\n";
		echo "</body></html>";
		$phpgw->common->phpgw_footer();
		sqimap_logout($imapConnection);
		exit;
	}

	$currentArrayIndex = $passed_id;
	$data = fillDataArray($imapConnection, $passed_id, $mailbox);
	
	do_hook("read_body_top");

   echo "<TABLE CELLSPACING=0 WIDTH=100% BORDER=0 ALIGN=CENTER CELLPADDING=0>\n";
   echo "   <TR><TD>";
   
   print viewMessageNavbar($mailbox, $mailboxStatus, $data, $currentArrayIndex, $sort, $startMessage, $uid);
   
   echo "   </TD></TR>";
   echo "   <TR><TD CELLSPACING=0 WIDTH=100%>";

	print viewMessageHeader($data);   

   do_hook("read_body_header");

   echo "   </TD></TR>";
   echo "</table>";
   echo "<TABLE COLS=1 CELLSPACING=0 WIDTH=97% BORDER=0 ALIGN=CENTER CELLPADDING=0>\n";

   echo "   <TR><TD BGCOLOR=\"$color[4]\" WIDTH=100%>\n";
   echo "<BR>";

	if($header->phpgw_type[type]) {
		echo '<center><h1>THIS IS A phpGroupWare-'.strtoupper($header->phpgw_type[type]).' EMAIL</h1><hr></center><br>';
	}
   
   $message = sqimap_get_message($imapConnection, $passed_id, $mailbox);
   $body = formatBody($imapConnection, $message, $color, $wrap_at);

   echo $body;

	if($header->phpgw_type[type])
	{
		if(isset($header->phpgw_type[id]))
		{
			$calendar_id = intval($header->phpgw_type[id]);
			echo '<table align="center" width="100%"><tr><td align="center">';
			$phpgw->hooks->single('email',$header->phpgw_type[type]);
			echo '</td></tr></table>';
		}
	}

   echo "<TABLE COLS=1 CELLSPACING=0 WIDTH=100% BORDER=0 ALIGN=CENTER CELLPADDING=0>\n";
   echo "   <TR><TD BGCOLOR=\"$color[9]\">&nbsp;</TD></TR>";
   echo "</TABLE>\n";

//mkorff@vpoint.com.br: inserted three next lines
   if (!isset($translated_setup) && !function_exists('translate_read_form') )
      include(PHPGW_APP_ROOT . "/inc/translate_setup.php");
   translate_read_form();

   do_hook("read_body_bottom");
   do_hook("html_bottom");
   sqimap_logout($imapConnection);

   $phpgw->common->phpgw_footer(); 
?>
