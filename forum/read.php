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
	if ($action)
	{
		$GLOBALS['phpgw_info']['flags']['noheader'] = True;
		$GLOBALS['phpgw_info']['flags']['nonavbar'] = True;
	}
	include('../header.inc.php');

	if ($action == 'reply')
	{
		$stat = 0;

		$GLOBALS['phpgw']->db->query("select max(id) from phpgw_forum_body",__LINE__,__FILE__);
		$GLOBALS['phpgw']->db->next_record();
		$next_f_body_id = $GLOBALS['phpgw']->db->f(0) + 1;

		$GLOBALS['phpgw']->db->query("select max(id) from phpgw_forum_threads",__LINE__,__FILE__);
		$GLOBALS['phpgw']->db->next_record();
		$next_f_threads_id = $GLOBALS['phpgw']->db->f(0) + 1;

		if ($pos != 0)
		{
			$tmp = $GLOBALS['phpgw']->db->query("select id,pos from phpgw_forum_threads where thread = $thread and pos >= $pos order by pos desc",__LINE__,__FILE__);
			while($GLOBALS['phpgw']->db->next_record($tmp))
			{
				$oldpos = $GLOBALS['phpgw']->db->f("pos") + 1;
				$oldid = $GLOBALS['phpgw']->db->f("id");
//				print "$oldid $oldpos<br>";
				$GLOBALS['phpgw']->db->query("update phpgw_forum_threads set pos=$oldpos where thread = $thread and id = $oldid",__LINE__,__FILE__);
			}
		}
		else
		{
			$pos = 1;
		}

		$GLOBALS['phpgw']->db->query("insert into phpgw_forum_threads (pos,thread,depth,main,parent,cat_id,for_id,"
			. "thread_owner,subject,stat,n_replies) VALUES ('$pos','$thread','$depth','"
			. "$next_f_body_id','" . addslashes($msg) . "','" . $session_info['cat_id'] . "','"
			. $session_info['forum_id'] . "','" . $GLOBALS['phpgw_info']['user']['account_id'] . "','"
			. addslashes($subject) . "','$stat',0)",__LINE__,__FILE__);

		$GLOBALS['phpgw']->db->query("update phpgw_forum_threads set n_replies = n_replies+1 where thread='$thread'",__LINE__,__FILE__);

		$GLOBALS['phpgw']->db->query("insert into phpgw_forum_body (cat_id,for_id,message) VALUES ('$cat','$for','" . addslashes($message) . "')",__LINE__,__FILE__);

		Header("Location: ". $GLOBALS['phpgw']->link("/forum/threads.php","cat=".$cat."&for=".$for."&col=".$col));
		$GLOBALS['phpgw']->common->phpgw_exit();
	}

	$GLOBALS['phpgw']->template->set_file('READ','read.body.tpl');

	$GLOBALS['phpgw']->template->set_var('row_off',$GLOBALS['phpgw_info']['theme']['row_off']);
	$GLOBALS['phpgw']->template->set_var('row_on',$GLOBALS['phpgw_info']['theme']['row_on']);

	$GLOBALS['phpgw']->db->query("select * from phpgw_forum_categories where id='" . $session_info['cat_id']
		. "'",__LINE__,__FILE__);

	$GLOBALS['phpgw']->db->next_record();
	$category = $GLOBALS['phpgw']->db->f('name');

	$GLOBALS['phpgw']->db->query("select * from phpgw_forum_forums where id='" . $session_info['forum_id']
		. "'",__LINE__,__FILE__);
	$GLOBALS['phpgw']->db->next_record();
	$forums = $GLOBALS['phpgw']->db->f('name');

	$GLOBALS['phpgw']->template->set_var(array(
		'BGROUND'       => $GLOBALS['phpgw_info']['theme']['th_bg'],
		'LANG_TOPIC'    => lang('Topic'),
		'LANG_AUTHOR'   => lang('Author'),
		'LANG_REPLIES'  => lang('Replies'),
		'LANG_LATREP'   => lang('Latest Reply'),
		'LANG_MAIN'     => lang('Forum'),
		'LANG_NEWTOPIC' => lang('New Topic'),
		'LANG_CATEGORY' => $category,
		'LANG_FORUM'    => $forums,
		'LANG_SEARCH'   => lang("Search"),
		'LANG_POST'     => lang("Post"),
		'FORUM_LINK'    => $GLOBALS['phpgw']->link('/forum/forums.php'),
		'MAIN_LINK'     => $GLOBALS['phpgw']->link('/forum/index.php'),
		'POST_LINK'     => $GLOBALS['phpgw']->link('/forum/read.php','type=new'),
		'THREADS_LINK'  => $GLOBALS['phpgw']->link('/forum/threads.php'),
		'SEARCH_LINK'   => $GLOBALS['phpgw']->link('/forum/search.php'),
		'READ_ACTION'   => $GLOBALS['phpgw']->link('/forum/read.php'),
		'MSG'           => $msg,
		'POST'          => $pos,
		'ACTION'        => 'reply'
	));


	$GLOBALS['phpgw']->template->set_var(array(
		THREADS_LINK  => $GLOBALS['phpgw']->link('/forum/threads.php'),
		LANG_THREADS  => lang('Return to forums')
	));

	$GLOBALS['phpgw']->db->query("select * from phpgw_forum_threads where id='$msg'",__LINE__,__FILE__);

	$GLOBALS['phpgw']->db->next_record();
	$thread  = $GLOBALS['phpgw']->db->f('thread');
	$depth   = $GLOBALS['phpgw']->db->f('depth') + 1;
	$subject = $GLOBALS['phpgw']->db->f('subject');
	if (! $subject)
	{
		$subject = '[ ' . lang('No subject') . ' ]';
	}

	$msgid = $GLOBALS['phpgw']->db->f('main');

	$subj = 'Re: ' . $subject;

	$GLOBALS['phpgw']->template->set_var(array(
		'THREAD'       => $thread,
		'DEPTH'        => $depth,
		'LANG_AUTHOR'  => lang('Author'),
		'LANG_DATE'    => lang('Date'),
		'LANG_SUBJECT' => lang('Subject'),
		'AUTHOR'       => ($GLOBALS['phpgw']->db->f('thread_owner')?$GLOBALS['phpgw']->accounts->id2name($GLOBALS['phpgw']->db->f('thread_owner')):lang('Unknown')),
		'POSTDATE'     => $GLOBALS['phpgw']->common->show_date($GLOBALS['phpgw']->db->from_timestamp($GLOBALS['phpgw']->db->f('postdate'))),
		'SUBJECT'      => $subj
	));

	$GLOBALS['phpgw']->db->query("select * from phpgw_forum_body where id='$msgid'",__LINE__,__FILE__);
	$GLOBALS['phpgw']->db->next_record();

	$GLOBALS['phpgw']->template->set_var('MESSAGE',$GLOBALS['phpgw']->strip_html($GLOBALS['phpgw']->db->f('message')));

	$GLOBALS['phpgw']->template->set_var(array(
		'LANG_SUBJECT' => lang('Subject'),
		'LANG_REPLY'   => lang('Email replies to this thread, to the address above'),
		'LANG_SUBMIT'  => lang('Submit'),
		'LANG_MESSAGE' => lang('Message'),
		'NAME'         => $name,
		'EMAIL'        => $email,
		'SUBJECT'      => $subject
	));
	$GLOBALS['phpgw']->template->pfp('Out','READ');

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
