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
	include('../header.inc.php');

	$phpgw->db->query("select * from phpgw_forum_categories where id='" . $session_info['cat_id'] . "'");
	$phpgw->db->next_record();
	$category = $phpgw->db->f('name');

	$phpgw->db->query("select * from phpgw_forum_forums where id='" . $session_info['forum_id'] . "'");
	$phpgw->db->next_record();
	$forums	= $phpgw->db->f('name');

//	$catfor	= "cat=" . $cat	. "&for=" . $for;
	$phpgw->template->set_var(array(
		BGROUND			=> $phpgw_info["theme"]["th_bg"],
		IMG_URL_PREFIX		=> PHPGW_IMAGES . "/",
		LANG_TOPIC		=> lang("Topic"),
		LANG_AUTHOR		=> lang("Author"),
		LANG_REPLIES		=> lang("Replies"),
		LANG_LATREP		=> lang("Latest Reply"),
		LANG_MAIN		=> lang("Forums"),
		LANG_NEWTOPIC		=> lang("New Topic"),
		LANG_CATEGORY		=> $category,
		LANG_FORUM		=> $forum,
		FORUM_LINK		=> $phpgw->link('/forum/forums.php'),
		MAIN_LINK		=> $phpgw->link('/forum/index.php'),
		POST_LINK		=> $phpgw->link('/forum/post.php','type=new'),
	));


	if ($session_info['view'] == 'collapsed')
	{
		$phpgw->template->set_file('COLLAPSE','collapse.threads.tpl');
		$phpgw->template->set_block('COLLAPSE','CollapseThreads','CollapseT');
		$phpgw->db->query("select * from phpgw_forum_threads where cat_id='" . $session_info['cat_id'] . "' and for_id='" . $session_info['forum_id'] . "'"
			. " and parent	= -1  order by postdate	DESC");


		//for viewing the collapse threads

		while ($phpgw->db->next_record())
		{
			$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
			$subject = $phpgw->db->f("subject");
			if (! $subject)
			{
				$subject = lang("[No subject]");
			}			//end if


			$phpgw->template->set_var(array(
				COLOR => $tr_color,
				TOPIC => $subject,
				AUTHOR => ($phpgw->db->f('thread_owner')?$phpgw->accounts->id2name($phpgw->db->f('thread_owner')):lang('Unknown')),
				REPLIES	=> $phpgw->db->f("n_replies") ,
				READ_LINK => $phpgw->link('/forum/read.php','msg=$msg' . $phpgw->db->f('id')),
				LATESTREPLY => $phpgw->common->show_date($phpgw->db->from_timestamp($phpgw->db->f('postdate')))
			));

			$phpgw->template->parse('CollapseT','CollapseThreads',true);

		}		//end	while
		$phpgw->template->set_var(array(
			THREADS_LINK =>	$phpgw->link('/forum/threads.php','view=threads'),
			LANG_THREADS => lang('View Threads')
		));
		$phpgw->template->parse("Out",'COLLAPSE');
		$phpgw->template->p("Out");

	}	//end if

	//For viewing the normal view
	else
	{
		$phpgw->template->set_file('NORMAL','normal.threads.tpl');
		$phpgw->template->set_block('NORMAL','NormalThreads','NormalT');
		$phpgw->db->query("select * from phpgw_forum_threads where cat_id='" . $session_info['cat_id'] . "' and for_id='"
			. $session_info['forum_id'] . "' order by thread DESC, postdate, depth");

		while($phpgw->db->next_record())
		{
			$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
			$move = "";
			//$move =	'<img src="'.$phpgw_info["server"]["app_images"] . '/trans.gif"';
			for($tmp = 1;$tmp <= $phpgw->db->f("depth"); $tmp++)
			/*	if($tmp==1)
			{
				$move .= '<img src="'.$phpgw_info["server"]["app_images"] . '/n.gif"';
			}
			*/
			$move	.= "&nbsp;&nbsp;";
			//putting some images point like <li></li>
			$move .= '<img src="'. PHPGW_IMAGES  . '/n.gif">';
			$move .= "&nbsp;&nbsp;";
			$subject = $phpgw->db->f("subject");
			if (! $subject)
			{
				$subject = lang("[ No Subject ]");
			}



			$phpgw->template->set_var(array(
				COLOR		=> $tr_color,
				TOPIC		=> $subject,
				AUTHOR		=> ($phpgw->db->f('thread_owner')?$phpgw->accounts->id2name($phpgw->db->f('thread_owner')):lang('Unknown')),
				REPLIES		=> $phpgw->db->f("n_replies") ,
				LATESTREPLY	=> $phpgw->common->show_date($phpgw->db->from_timestamp($phpgw->db->f('postdate'))),
				READ_LINK	=> $phpgw->link('/forum/read.php','pos=' . $pos . '&msg=' . $phpgw->db->f('id')),
				DEPTH		=> $move
			));

			$phpgw->template->parse('NormalT','NormalThreads',true);


		}		//end	while

		$phpgw->template->set_var(array(
			THREADS_LINK	=> $phpgw->link('/forum/threads.php','view=collapsed'),
			LANG_THREADS	=> lang('Collapse Threads')
		));

		$phpgw->template->parse('Out','NORMAL');
		$phpgw->template->p('Out');


	}	//end else

	$phpgw->common->phpgw_footer();
?>
