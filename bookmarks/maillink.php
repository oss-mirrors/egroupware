<?php
	/**************************************************************************\
	* phpGroupWare - Bookmarks                                                 *
	* http://www.phpgroupware.org                                              *
	* Based on Bookmarker Copyright (C) 1998  Padraic Renaghan                 *
	*                     http://www.renaghan.com/bookmarker                   *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp'              => 'bookmarks',
		'enable_nextmatchs_class' => True,
		'enable_categories_class' => True
	);
	include('../header.inc.php');

	$GLOBALS['phpgw']->bookmarks = createobject('bookmarks.bookmarks');

	$GLOBALS['phpgw']->template->set_file(array(
		'common_' => 'common.tpl',
		'body'    => 'maillink.body.tpl'
	));

	app_header(&$GLOBALS['phpgw']->template);

	// if browser is MSIE, then need to add this bit
	// of javascript to the page so that MSIE correctly
	// brings quik-mark and mail-this-link popups to the front.
	if (check_browser() == 'MSIE')
	{
		$GLOBALS['phpgw']->template->parse(MSIE_JS,'msie_js');
	}

	// Check if there was a submission
	if ($GLOBALS['HTTP_POST_VARS']['send'])	// Send button clicked
	{
		$validate = createobject('phpgwapi.validator');
		// Strip space and tab from anywhere in the To field
		$to = $validate->strip_space($GLOBALS['HTTP_POST_VARS']['to']);

		// Trim the subject
		$subject = trim($GLOBALS['HTTP_POST_VARS']['subject']);

		$message = $GLOBALS['HTTP_POST_VARS']['message'];

		// Do we have all necessary data?
		if (empty($to) || empty($subject) || empty($message))
		{
			$error_msg .= '<br>'.lang('Please fill out <B>To E-Mail Address</B>, <B>Subject</B>, and <B>Message</B>!');
		}
		else
		{
			// the To field may contain one or more email addresses
			// separated by commas. Check each one for proper format.
			$to_array = explode(",", $to);

			while (list($key, $val) = each($to_array))
			{
				// Is email address in the proper format?
				if (!$validate->is_email($val))
				{
					$error_msg .= '<br>'.lang('To address %1 invalid. Format must be <strong>user@domain</strong> and domain must exist!',$val).
						'<br><small>'.$validate->ERROR.'</small>';
					break;
				}
			}
		}

		if (!isset ($error_msg))
		{
			$send     = createobject('phpgwapi.send');
			// add additional headers to our email
			$addl_headers = sprintf("%s: %s <%s>",lang('From'),stripslashes($from_name), $from);

			//$addl_headers = sprintf('%s\n%s',$addl_headers,$GLOBALS['phpgw']->template->parse('_footer','footer'));
			$reply_to = $GLOBALS['phpgw_info']['user']['fullname'].' <'.$GLOBALS['phpgw_info']['user']['preferences']['email']['address'].'>';
			if (empty($replay_to))
			{
				$reply_to = 'No reply <noreply@' . $GLOBALS['phpgw_info']['server']['mail_suffix'].'>';
			}
			// send the message
			$send->msg('email',$to,$subject,$message ."\n". $GLOBALS['phpgw']->bookmarks->config['mail_footer'],'','','',$reply_to);
			
			$msg .= '<br>'.lang('mail-this-link message sent to %1.',$to);
		}
	}

	if (empty($subject))
	{
		$subject = lang('Found a link you might like');
	}

	if (empty($message))
	{
		$filtermethod = '( bm_owner=' . $GLOBALS['phpgw_info']['user']['account_id'];
		if (is_array($GLOBALS['phpgw']->bookmarks->grants))
		{
			$grants = $GLOBALS['phpgw']->bookmarks->grants;
			reset($grants);
			while (list($user) = each($grants))
			{
				$public_user_list[] = $user;
			}
			reset($public_user_list);
			$filtermethod .= " OR (bm_access='public' AND bm_owner in(" . implode(',',$public_user_list) . ')))';
		}
		else
		{
			$filtermethod .= ' )';
		}
	
		if ($mass_bm_id)
		{
			$bm_id = unserialize(stripslashes($mass_bm_id));
			echo '<pre>'; print_r($bm_id); echo '</pre>';
	
			while (is_array($bm_id) && (list(,$id) = each($bm_id)))
			{
				$GLOBALS['phpgw']->db->query("select * from phpgw_bookmarks where bm_id='$id' and $filtermethod",__LINE__,__FILE__);
				$GLOBALS['phpgw']->db->next_record();
	
				$links[] = array(
					'name' => $GLOBALS['phpgw']->db->f('bm_name'),
					'url'  => $GLOBALS['phpgw']->db->f('bm_url')
				);		
			}
		}
		else
		{
			$GLOBALS['phpgw']->db->query("select * from phpgw_bookmarks where bm_id='$bm_id' and $filtermethod",__LINE__,__FILE__);
			$GLOBALS['phpgw']->db->next_record();
	
			$links[] = array(
				'name' => $GLOBALS['phpgw']->db->f('bm_name'),
				'url'  => $GLOBALS['phpgw']->db->f('bm_url')
			);
		}
	
		$message = lang('I thought you would be interested in the following link(s):')."\n";
		while (list(,$link) = @each($links))
		{
			$message .= sprintf("%s - %s\n",$link['name'],$link['url']);
		}
	}

	$GLOBALS['phpgw']->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
	$GLOBALS['phpgw']->template->set_var('header_message',lang('Send bookmark'));
	$GLOBALS['phpgw']->template->set_var('lang_from',lang('Message from'));
	$GLOBALS['phpgw']->template->set_var('lang_to',lang('To E-Mail Addresses'));
	$GLOBALS['phpgw']->template->set_var('lang_multiple_addr',lang('(comma separate multiple addresses)'));
	$GLOBALS['phpgw']->template->set_var('lang_subject',lang('Subject'));
	$GLOBALS['phpgw']->template->set_var('lang_message',lang('Message'));
	$GLOBALS['phpgw']->template->set_var('lang_send',lang('Send'));
	$GLOBALS['phpgw']->template->set_var('from_name',$GLOBALS['phpgw']->common->display_fullname());

	$GLOBALS['phpgw']->template->set_var('form_action',$GLOBALS['phpgw']->link('/bookmarks/maillink.php'));
	$GLOBALS['phpgw']->template->set_var('to',$to);
	$GLOBALS['phpgw']->template->set_var('subject',$subject);
	$GLOBALS['phpgw']->template->set_var('message',$message);

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
