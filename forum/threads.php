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
	include('../header.inc.php');

	$GLOBALS['phpgw']->db->query("select * from phpgw_forum_categories where id='" . $session_info['cat_id'] . "'");
	$GLOBALS['phpgw']->db->next_record();
	$category = $GLOBALS['phpgw']->db->f('name');

	$GLOBALS['phpgw']->db->query("select * from phpgw_forum_forums where id='" . $session_info['forum_id'] . "'");
	$GLOBALS['phpgw']->db->next_record();
	$forums = $GLOBALS['phpgw']->db->f('name');

//	$catfor = "cat=" . $cat . "&for=" . $for;
	$GLOBALS['phpgw']->template->set_var(array(
		'BGROUND'        => $GLOBALS['phpgw_info']["theme"]["th_bg"],
		'IMG_URL_PREFIX' => PHPGW_IMAGES . "/",
		'LANG_TOPIC'     => lang("Topic"),
		'LANG_AUTHOR'    => lang("Author"),
		'LANG_REPLIES'   => lang("Replies"),
		'LANG_LATREP'    => lang("Latest Reply"),
		'LANG_MAIN'      => lang("Forums"),
		'LANG_NEWTOPIC'  => lang("New Topic"),
		'LANG_CATEGORY'  => $category,
		'LANG_FORUM'     => $forum,
		'FORUM_LINK'     => $GLOBALS['phpgw']->link('/forum/forums.php'),
		'MAIN_LINK'      => $GLOBALS['phpgw']->link('/forum/index.php'),
		'POST_LINK'      => $GLOBALS['phpgw']->link('/forum/post.php','type=new'),
	));

	if ($session_info['view'] == 'collapsed')
	{
		$GLOBALS['phpgw']->template->set_file('COLLAPSE','collapse.threads.tpl');
		$GLOBALS['phpgw']->template->set_block('COLLAPSE','CollapseThreads','CollapseT');
		$GLOBALS['phpgw']->db->query("select * from phpgw_forum_threads where cat_id='" . $session_info['cat_id'] . "' and for_id='" . $session_info['forum_id'] . "'"
			. " and parent = -1 order by postdate DESC");

		//for viewing the collapse threads

		while ($GLOBALS['phpgw']->db->next_record())
		{
			$tr_color = $GLOBALS['phpgw']->nextmatchs->alternate_row_color($tr_color);
			$subject = $GLOBALS['phpgw']->db->f("subject");
			if (! $subject)
			{
				$subject = lang("[No subject]");
			} //end if

			$GLOBALS['phpgw']->template->set_var(array(
				'COLOR'       => $tr_color,
				'TOPIC'       => $subject,
				'AUTHOR'      => ($GLOBALS['phpgw']->db->f('thread_owner')?$GLOBALS['phpgw']->accounts->id2name($GLOBALS['phpgw']->db->f('thread_owner')):lang('Unknown')),
				'REPLIES'     => $GLOBALS['phpgw']->db->f("n_replies") ,
				'READ_LINK'   => $GLOBALS['phpgw']->link('/forum/read.php','msg=$msg' . $GLOBALS['phpgw']->db->f('id')),
				'LATESTREPLY' => $GLOBALS['phpgw']->common->show_date($GLOBALS['phpgw']->db->from_timestamp($GLOBALS['phpgw']->db->f('postdate')))
			));

			$GLOBALS['phpgw']->template->parse('CollapseT','CollapseThreads',true);
		} //end while

		$GLOBALS['phpgw']->template->set_var(array(
			'THREADS_LINK' => $GLOBALS['phpgw']->link('/forum/threads.php','view=threads'),
			'LANG_THREADS' => lang('View Threads')
		));
		$GLOBALS['phpgw']->template->parse("Out",'COLLAPSE');
		$GLOBALS['phpgw']->template->p("Out");
	} //end if

	//For viewing the normal view
	else
	{
		$GLOBALS['phpgw']->template->set_file('NORMAL','normal.threads.tpl');
		$GLOBALS['phpgw']->template->set_block('NORMAL','NormalThreads','NormalT');
		$GLOBALS['phpgw']->db->query("select * from phpgw_forum_threads where cat_id='" . $session_info['cat_id'] . "' and for_id='"
			. $session_info['forum_id'] . "' order by thread DESC, postdate, depth");

		while($GLOBALS['phpgw']->db->next_record())
		{
			$tr_color = $GLOBALS['phpgw']->nextmatchs->alternate_row_color($tr_color);
			$move = '';
			//$move = '<img src="'.$GLOBALS['phpgw_info']["server"]["app_images"] . '/trans.gif"';
			for($tmp = 1;$tmp <= $GLOBALS['phpgw']->db->f("depth"); $tmp++)
			/* if($tmp==1)
			{
				$move .= '<img src="'.$GLOBALS['phpgw_info']["server"]["app_images"] . '/n.gif"';
			}
			*/
			$move .= "&nbsp;&nbsp;";
			//putting some images point like <li></li>
			$move .= '<img src="'. PHPGW_IMAGES . '/n.gif">';
			$move .= "&nbsp;&nbsp;";
			$subject = $GLOBALS['phpgw']->db->f("subject");
			if (! $subject)
			{
				$subject = lang("[ No Subject ]");
			}

			$pos = $GLOBALS['phpgw']->db->f('pos');

			$GLOBALS['phpgw']->template->set_var(array(
				'COLOR'       => $tr_color,
				'TOPIC'       => $subject,
				'AUTHOR'      => ($GLOBALS['phpgw']->db->f('thread_owner')?$GLOBALS['phpgw']->accounts->id2name($GLOBALS['phpgw']->db->f('thread_owner')):lang('Unknown')),
				'REPLIES'     => $GLOBALS['phpgw']->db->f("n_replies") ,
				'LATESTREPLY' => $GLOBALS['phpgw']->common->show_date($GLOBALS['phpgw']->db->from_timestamp($GLOBALS['phpgw']->db->f('postdate'))),
				'READ_LINK'   => $GLOBALS['phpgw']->link('/forum/read.php','pos=' . $pos . '&msg=' . $GLOBALS['phpgw']->db->f('id')),
				'DEPTH'       => $move
			));

			$GLOBALS['phpgw']->template->parse('NormalT','NormalThreads',true);
		} //end while

		$GLOBALS['phpgw']->template->set_var(array(
			'THREADS_LINK' => $GLOBALS['phpgw']->link('/forum/threads.php','view=collapsed'),
			'LANG_THREADS' => lang('Collapse Threads')
		));

		$GLOBALS['phpgw']->template->parse('Out','NORMAL');
		$GLOBALS['phpgw']->template->p('Out');

	} //end else

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
