<?php
	/**************************************************************************\
	* phpGroupWare - Forums                                                    *
	* http://www.phpgroupware.org                                              *
	* Written by Jani Hirvinen <jpkh@shadownet.com>                            *
	* -------------------------------------------                              *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp'              => 'forum',
		'enable_nextmatchs_class' => True
	);

	$action = $GLOBALS['HTTP_POST_VARS']['post'] ? $GLOBALS['HTTP_POST_VARS']['post'] : $GLOBALS['HTTP_GET_VARS']['post'];
	if ($action)
	{
		$GLOBALS['phpgw_info']['flags']['noheader'] = True;
		$GLOBALS['phpgw_info']['flags']['nonavbar'] = True;
	}
	include('../header.inc.php');

	$subject = $GLOBALS['HTTP_POST_VARS']['subject'];
	$stat    = $GLOBALS['HTTP_POST_VARS']['stat'];
	$message = $GLOBALS['HTTP_POST_VARS']['message'];

	if ($action == 'post')
	{
		$stat = 0;

		$GLOBALS['phpgw']->db->query('select max(id) from phpgw_forum_body',__LINE__,__FILE__);
		$GLOBALS['phpgw']->db->next_record();
		$next_f_body_id = $GLOBALS['phpgw']->db->f('0') + 1;

		$GLOBALS['phpgw']->db->query("select max(id) from phpgw_forum_threads",__LINE__,__FILE__);
		$GLOBALS['phpgw']->db->next_record();
		$next_f_threads_id = $GLOBALS['phpgw']->db->f('0') + 1;

		//print "$next_f_threads_id <br> $next_f_body_id";

		$GLOBALS['phpgw']->db->query("insert into phpgw_forum_threads (pos,thread,depth,main,parent,"
			. "cat_id,for_id,thread_owner,subject,stat,n_replies) VALUES (0,$next_f_body_id,"
			. "0,$next_f_body_id,-1,'" . $session_info['cat_id'] . "','" . $session_info['forum_id']
			. "','" . $GLOBALS['phpgw_info']['user']['account_id'] . "','$subject',$stat,0)",__LINE__,__FILE__);

		$GLOBALS['phpgw']->db->query("insert into phpgw_forum_body (cat_id,for_id,message) VALUES ('"
			. $session_info['cat_id'] . "','" . $session_info['forum_id'] . "','$message')",__LINE__,__FILE__);

		$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/forum/threads.php'));
		$GLOBALS['phpgw']->common->phpgw_exit();
	}

	$GLOBALS['phpgw']->template->set_file('POST','post.body.tpl');

	$GLOBALS['phpgw']->db->query("select * from phpgw_forum_categories where id='" . $session_info['cat_id'] . "'",__LINE__,__FILE__);
	$GLOBALS['phpgw']->db->next_record();
	$category = $GLOBALS['phpgw']->db->f('name');

	$GLOBALS['phpgw']->db->query("select * from phpgw_forum_forums where id='" . $session_info['forum_id'] . "'",__LINE__,__FILE__);
	$GLOBALS['phpgw']->db->next_record();
	$forums   = $GLOBALS['phpgw']->db->f('name');

	$GLOBALS['phpgw']->template->set_var(array(
		'BGROUND'       => $GLOBALS['phpgw_info']['theme']['th_bg'],
		'LANG_TOPIC'    => lang('Topic'),
		'LANG_AUTHOR'   => lang('Author'),
		'LANG_REPLIES'  => lang('Replies'),
		'LANG_LATREP'   => lang('Latest Reply'),
		'LANG_MAIN'     => lang('Forum'),
		'LANG_NEWTOPIC' => lang('New Topic'),
		'LANG_SEARCH'   => lang('Search'),
		'LANG_POST'     => lang('Post'),
		'FORUM_LINK'    => $GLOBALS['phpgw']->link('/forum/forums.php'),
		'MAIN_LINK'     => $GLOBALS['phpgw']->link('/forum/index.php'),
		'POST_LINK'     => $GLOBALS['phpgw']->link('/forum/post.php','type=new'),
		'THREADS_LINK'  => $GLOBALS['phpgw']->link('/forum/threads.php'),
		'SEARCH_LINK'   => $GLOBALS['phpgw']->link('/forum/search.php'),
		'POST_ACTION'   => $GLOBALS['phpgw']->link('/forum/post.php'),
		'TYPE'          => $type,
		'ACTION'        => 'post'
	));

	$GLOBALS['phpgw']->template->set_var(array(
		'THREADS_LINK' => $GLOBALS['phpgw']->link('/forum/threads.php'),
		'LANG_THREADS' => lang('Return')
	));

	$name = $GLOBALS['phpgw_info']['user']['firstname'] . ' ' . $GLOBALS['phpgw_info']['user']['lastname'];
	$email = $GLOBALS['phpgw_info']['user']['email_address'];

	$GLOBALS['phpgw']->template->set_var(array(
		'LANG_NAME'    => lang('Your Name'),
		'LANG_EMAIL'   => lang('Your Email'),
		'LANG_SUBJECT' => lang('Subject'),
		'LANG_REPLY'   => lang('Email replies to this thread, to the address above'),
		'LANG_SUBMIT'  => lang('Submit'),
		'LANG_MESSAGE' => lang('Message'),
		'NAME'         => $name,
		'EMAIL'        => $email,
		'SUBJECT'      => $subject,
	));

	$GLOBALS['phpgw']->template->pfp('Out','POST');
	$GLOBALS['phpgw']->common->phpgw_footer();
?>
