<?php
	/*****************************************************************************\
	* phpGroupWare - Forums                                                       *
	* http://www.phpgroupware.org                                                 *
	* Written by Jani Hirvinen <jpkh@shadownet.com>                               *
	* -------------------------------------------                                 *
	*  This program is free software; you	can redistribute it and/or modify it   *
	*  under the terms of	the GNU	General	Public License as published by the  *
	*  Free Software Foundation; either version 2	of the License,	or (at your *
	*  option) any later version.                                                 *
	\*****************************************************************************/

	/* $Id$ */

	$phpgw_info['flags'] = array(
		'currentapp'              => 'forum',
		'enable_nextmatchs_class' => True
	);
	if ($action)
	{
		$phpgw_info['flags']['noheader'] = True;
		$phpgw_info['flags']['nonavbar'] = True;
	}
	include('../header.inc.php');


	if ($action == 'reply')
	{
		$stat = 0;

		$phpgw->db->query("select max(id) from	phpgw_forum_body",__LINE__,__FILE__);
		$phpgw->db->next_record();
		$next_f_body_id = $phpgw->db->f(0) + 1;

		$phpgw->db->query("select max(id) from	phpgw_forum_threads",__LINE__,__FILE__);
		$phpgw->db->next_record();
		$next_f_threads_id = $phpgw->db->f(0) + 1;

		if ($pos != 0)
		{
			$tmp = $phpgw->db->query("select id,pos from phpgw_forum_threads	where thread = $thread and pos >= $pos order by	pos desc",__LINE__,__FILE__);
			while($phpgw->db->next_record($tmp))
			{
				$oldpos = $phpgw->db->f("pos") + 1;
				$oldid = $phpgw->db->f("id");
				print	"$oldid	$oldpos<br>";
				$phpgw->db->query("update phpgw_forum_threads set pos=$oldpos where thread = $thread and id = $oldid",__LINE__,__FILE__);
			}
		}
		else
		{
			$pos = 1;
		}

		$phpgw->db->query("insert into phpgw_forum_threads (pos,thread,depth,main,parent,cat_id,for_id,"
			. "thread_owner,subject,stat,n_replies) VALUES ('$pos','$thread','$depth','"
			. "$next_f_body_id','"	. addslashes($msg) . "','$cat','$for','"
			. $phpgw_info['user']['account_id']	. "','" . addslashes($subject) .	"','$stat',0)",__LINE__,__FILE__);

		$phpgw->db->query("update phpgw_forum_threads set n_replies = n_replies+1 where thread='$thread'",__LINE__,__FILE__);

		$phpgw->db->query("insert into phpgw_forum_body (cat_id,for_id,message) VALUES ('$cat','$for','" . addslashes($message) . "')",__LINE__,__FILE__);

		Header("Location: ". $phpgw->link("/forum/threads.php","cat=".$cat."&for=".$for."&col=".$col));
		$phpgw->common->phpgw_exit();

	}




	$phpgw->template->set_file('READ','read.body.tpl');

	$phpgw->template->set_var('row_off',$phpgw_info['theme']['row_off']);
	$phpgw->template->set_var('row_on',$phpgw_info['theme']['row_on']);

	$phpgw->db->query("select * from phpgw_forum_categories where id='" . $session_info['cat_id']
		. "'",__LINE__,__FILE__);

	$phpgw->db->next_record();
	$category = $phpgw->db->f('name');

	$phpgw->db->query("select * from phpgw_forum_forums where id='" . $session_info['forum_id']
		. "'",__LINE__,__FILE__);
	$phpgw->db->next_record();
	$forums = $phpgw->db->f('name');

	$phpgw->template->set_var(array(
		BGROUND			=> $phpgw_info["theme"]["th_bg"],
		LANG_TOPIC		=> lang("Topic"),
		LANG_AUTHOR		=> lang("Author"),
		LANG_REPLIES		=> lang("Replies"),
		LANG_LATREP		=> lang("Latest	Reply"),
		LANG_MAIN		=> lang("Forum"),
		LANG_NEWTOPIC		=> lang("New Topic"),
		LANG_CATEGORY		=> $category,
		LANG_FORUM		=> $forums,
		LANG_SEARCH		=> lang("Search"),
		LANG_POST		=> lang("Post"),
		FORUM_LINK		=> $phpgw->link('/forum/forums.php'),
		MAIN_LINK		=> $phpgw->link('/forum/index.php'),
		POST_LINK		=> $phpgw->link('/forum/read.php','type=new'),
		THREADS_LINK		=> $phpgw->link('/forum/threads.php'),
		SEARCH_LINK		=> $phpgw->link('/forum/search.php'),
		READ_ACTION		=> $phpgw->link('/forum/read.php'),
		MSG			=> $msg,
		POST			=> $pos,
		ACTION			=> 'reply'
	));


	$phpgw->template->set_var(array(
		THREADS_LINK  => $phpgw->link('/forum/threads.php'),
		LANG_THREADS  => lang('Return to forums')
	));

	$phpgw->db->query("select * from phpgw_forum_threads where id='$msg'",__LINE__,__FILE__);

	$phpgw->db->next_record();
	$thread  = $phpgw->db->f('thread');
	$depth   = $phpgw->db->f('depth') + 1;
	$subject = $phpgw->db->f('subject');
	if (! $subject)
	{
		$subject = '[ ' . lang('No subject') . ' ]';
	}

	$msgid	= $phpgw->db->f('main');

	$subj = 'Re: ' . $subject;

	$phpgw->template->set_var(array(
		THREAD       => $thread,
		DEPTH        => $depth,
		LANG_AUTHOR  => lang('Author'),
		LANG_DATE    => lang('Date'),
		LANG_SUBJECT => lang('Subject'),
		AUTHOR       => ($phpgw->db->f('thread_owner')?$phpgw->accounts->id2name($phpgw->db->f('thread_owner')):lang('Unknown')),
		POSTDATE     => $phpgw->common->show_date($phpgw->db->from_timestamp($phpgw->db->f('postdate'))),
		SUBJECT      => $subj
	));

	$phpgw->db->query("select * from phpgw_forum_body where id='$msgid'",__LINE__,__FILE__);
	$phpgw->db->next_record();

	$phpgw->template->set_var('MESSAGE',$phpgw->strip_html($phpgw->db->f('message')));

	$phpgw->template->set_var(array(
		LANG_SUBJECT		=> lang("Subject"),
		LANG_REPLY		=> lang("Email replies to this thread, to the address above"),
		LANG_SUBMIT		=> lang("Submit"),
		LANG_MESSAGE		=> lang("Message"),
		NAME			=> $name,
		EMAIL			=> $email,
		SUBJECT			=> $subject
	));
	$phpgw->template->pfp('Out','READ');

	$phpgw->common->phpgw_footer();
?>
