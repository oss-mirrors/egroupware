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


	$phpgw_info["flags"] = array("currentapp" => "forum",	"enable_nextmatchs_class" => True);
	if($action)
	{
		$phpgw_info["flags"]["noheader"] = True;
		$phpgw_info["flags"]["nonavbar"] = True;
	}

	include("../header.inc.php");

	if ($action == "post")
	{
		$stat = 0;

		$phpgw->db->query("select max(id) from	phpgw_forum_body",__LINE__,__FILE__);
		$phpgw->db->next_record();
		$next_f_body_id = $phpgw->db->f("0") +	1;

		$phpgw->db->query("select max(id) from	phpgw_forum_threads",__LINE__,__FILE__);
		$phpgw->db->next_record();
		$next_f_threads_id = $phpgw->db->f("0") + 1;

		//print	"$next_f_threads_id <br> $next_f_body_id";

		$phpgw->db->query("insert into phpgw_forum_threads (pos,thread,depth,main,parent,"
			. "cat_id,for_id,thread_owner,subject,stat,n_replies) VALUES (0,$next_f_body_id,"
			. "0,$next_f_body_id,-1,'" . $session_info['cat_id'] . "','" . $session_info['forum_id']
			. "','" . $phpgw_info['user']['account_id'] . "','$subject',$stat,0)",__LINE__,__FILE__);

		$phpgw->db->query("insert into phpgw_forum_body	(cat_id,for_id,message)	VALUES ("
			. "$cat,$for,'$message')",__LINE__,__FILE__);

		$link_values = array(
			'cat' => $cat,
			'for' => $for,
			'col' => $col
		);
		$phpgw->redirect($phpgw->link('/forum/threads.php',$link_values));
		$phpgw->common->phpgw_exit();
	}

	$phpgw->template->set_file('POST','post.body.tpl');


	$phpgw->db->query("select * from phpgw_forum_categories where id='$cat'",__LINE__,__FILE__);
	$phpgw->db->next_record();
	$category = $phpgw->db->f('name');

	$phpgw->db->query("select * from phpgw_forum_forums where id='$for'",__LINE__,__FILE__);
	$phpgw->db->next_record();
	$forums   = $phpgw->db->f('name');

	$catfor = "cat=" . $cat . "&for=" . $for;

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
		FORUM_LINK		=> $phpgw->link("/forum/forums.php","cat=" . $cat),
		MAIN_LINK		=> $phpgw->link("/forum/index.php"),
		POST_LINK		=> $phpgw->link("/forum/post.php","$catfor&type=new"),
		THREADS_LINK		=> $phpgw->link("/forum/threads.php","$catfor&col=$col"),
		SEARCH_LINK		=> $phpgw->link("/forum/search.php","$catfor"),
		POST_ACTION		=> $phpgw->link("/forum/post.php"),
		CAT			=> $cat,
		FORU			=> $for,
		TYPE			=> $type,
		ACTION			=> 'post'
	));




	if (!$col)
	{
		$phpgw->template->set_var(array(
			THREADS_LINK  => $phpgw->link("/forum/threads.php","$catfor&col=1"),
			LANG_THREADS  => lang("Normal View")
		));
	}
	else
	{
		$phpgw->template->set_var(array(
		THREADS_LINK	=> $phpgw->link("/forum/threads.php","$catfor&col=0"),
		LANG_THREADS	=> lang("View Collapse"),
		COL		=> $col
		));
	}

	$name = $phpgw_info["user"]["firstname"] . " "	. $phpgw_info["user"]["lastname"];
	$email	= $phpgw_info["user"]["email_address"];

	$phpgw->template->set_var(array(
		LANG_NAME		=> lang("Your Name"),
		LANG_EMAIL		=> lang("Your Email"),
		LANG_SUBJECT		=> lang("Subject"),
		LANG_REPLY		=> lang("Email replies to this thread, to the address above"),
		LANG_SUBMIT		=> lang("Submit"),
		LANG_MESSAGE		=> lang("Message"),
		NAME			=> $name,
		EMAIL			=> $email,
		SUBJECT			=> $subject,
	));


	$phpgw->template->pfp('Out','POST');
	$phpgw->common->phpgw_footer();

?>