<?
	/**************************************************************************\
	* phpGroupWare - Forums						     *
	* http://www.phpgroupware.org						     *
	* Written by Jani Hirvinen <jpkh@shadownet.com>			     *
	* -------------------------------------------				     *
	*  This program is free software; you	can redistribute it and/or modify it *
	*  under the terms of	the GNU	General	Public License as published by the   *
	*  Free Software Foundation; either version 2	of the License,	or (at your  *
	*  option) any later version.						     *
	\**************************************************************************/



	$phpgw_info["flags"] = array("currentapp" => "forum", "enable_nextmatchs_class"	=> True);
	include("../header.inc.php");


	$phpgw->db->query("select * from f_categories where id = $cat");
	$phpgw->db->next_record();
	$category = $phpgw->db->f("name");

	$phpgw->db->query("select * from f_forums where	id = $for");
	$phpgw->db->next_record();
	$forums	= $phpgw->db->f("name");

	$catfor	= "cat=" . $cat	. "&for=" . $for;

	$phpgw->template->set_var(array(
	BGROUND			=> $phpgw_info["theme"]["th_bg"],
	LANG_TOPIC		=> lang("Topic"),
	LANG_AUTHOR		=> lang("Author"),
	LANG_REPLIES		=> lang("Replies"),
	LANG_LATREP		=> lang("Latest	Reply"),
	LANG_MAIN		=> lang("Forums"),
	LANG_NEWTOPIC		=> lang("New Topic"),
	LANG_CATEGORY		=> $category,
	LANG_FORUM		=> $forum,
	FORUM_LINK		=> $phpgw->link("/forum/forums.php","cat=" . $cat),
	MAIN_LINK		=> $phpgw->link("/forum/index.php"),
	POST_LINK		=> $phpgw->link("/forum/post.php","$catfor&type=new&col=$col"),
	));


	if(!$col)
	{
		$phpgw->template->set_file('COLLAPSE','collapse.threads.tpl');
		$phpgw->template->set_block('COLLAPSE','CollapseThreads','CollapseT');
		$phpgw->db->query("select * from f_threads where cat_id=$cat and for_id=$for and parent	= -1  order by postdate	DESC");


		//for viewing the collapse threads

		while($phpgw->db->next_record())
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
			AUTHOR => $phpgw->db->f("author"),
			REPLIES	=> $phpgw->db->f("n_replies") ,
			LATESTREPLY => $phpgw->db->f("postdate")
			));

			$phpgw->template->parse('CollapseT','CollapseThreads',true);

		}		//end	while
		$phpgw->template->set_var(array(
		THREADS_LINK =>	$phpgw->link("/forum/threads.php","$catfor&col=1"),
		READ_LINK => $phpgw->link("/forum/read.php","cat=$cat&for=$for&msg=$msg" . $phpgw->db->f("id")),
		LANG_THREAD => lang("View Threads")
		));
		$phpgw->template->parse("Out",'COLLAPSE');
		$phpgw->template->p("Out");

	}	//end if

	//For viewing the normal view
	else
	{
		$phpgw->template->set_file('NORMAL','normal.threads.tpl');
		$phpgw->template->set_block('NORMAL','NormalThreads','NormalT');
		$phpgw->db->query("select * from f_threads where cat_id	= $cat and for_id = $for order by thread DESC, postdate, depth");

		while($phpgw->db->next_record())
		{
			$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);

			$move =	"";
			for($tmp = 1;$tmp <= $phpgw->db->f("depth"); $tmp++)
			$move	.= "&nbsp;&nbsp;";
			$subject = $phpgw->db->f("subject");
			if (! $subject)
			{
				$subject = lang("No Subject");
			}



			$phpgw->template->set_var(array(
			COLOR		=> $tr_color,
			TOPIC		=> $subject,
			AUTHOR		=> $phpgw->db->f("author"),
			REPLIES		=> $phpgw->db->f("n_replies") ,
			LATESTREPLY	=> $phpgw->db->f("postdate"),
			DEPTH		=> $move
			));

			$phpgw->template->parse('NormalT','NormalThreads',true);


		}		//end	while

		$phpgw->template->set_var(array(
		THREADS_LINK	=> $phpgw->link("/forum/threads.php","$catfor&col=0"),
		READ_LINK	=> $phpgw->link("/forum/read.php","cat=$cat&for=$for&pos=$pos&col=1&msg=" . $phpgw->db->f("id")),
		LANG_THREAD	=> lang("Collapse Threads")
		));

		$phpgw->template->parse('Out','NORMAL');
		$phpgw->template->p('Out');


	}	//end else

	$phpgw->common->phpgw_footer();
?>
