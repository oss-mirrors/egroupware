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
		'currentapp'               => 'forum',
		'enable_nextmatchs_class'	=> True
	);
	include('../header.inc.php');

	$phpgw->template->set_file('_list' ,'forums.body.tpl');

	$phpgw->template->set_block('_list','row_empty');
	$phpgw->template->set_block('_list','list');
	$phpgw->template->set_block('_list','row');

	$phpgw->db->query("select * from phpgw_forum_categories where id='$cat_id'",__LINE__,__FILE__);
	$phpgw->db->next_record();

	$phpgw->template->set_var(array(
		BGROUND        => $phpgw_info['theme']['th_bg'],
		IMG_URL_PREFIX => PHPGW_IMAGES . '/',
		CATEGORY       => $phpgw->db->f('name'),
		LANG_MAIN      => lang('Forum'),
		MAIN_LINK      => $phpgw->link('/forum/index.php')
	));

	$db2 = $phpgw->db;
	$phpgw->db->query("select * from phpgw_forum_forums where cat_id='$cat_id'",__LINE__,__FILE__);
	if (! $phpgw->db->num_rows())
	{
		$phpgw->nextmatchs->template_alternate_row_color(&$phpgw->template);
		$phpgw->template->set_var('lang_no_forums',lang('There are no forums in this category'));

		$phpgw->template->fp('rows','row_empty');
	}
	else
	{
		while ($phpgw->db->next_record())
		{
			$db2->query("select max(postdate) from phpgw_forum_threads where cat_id='$cat_id' and for_id='"
				. $phpgw->db->f('id') . "'",__LINE__,__FILE__);
			$db2->next_record();

			if ($db2->f(0))
			{
				$last_post_date = $phpgw->common->show_date($phpgw->db->from_timestamp($db2->f(0)));
			}
			else
			{
				$last_post_date = '&nbsp;';
			}

			$db2->query("select count(*) from phpgw_forum_threads where cat_id='$cat_id' and for_id='"
				. $phpgw->db->f('id') . "'",__LINE__,__FILE__);
			$db2->next_record();

			$total = $db2->f(0);

			$phpgw->nextmatchs->template_alternate_row_color(&$phpgw->template);
			$phpgw->template->set_var(array(
				NAME              => $phpgw->db->f('name'),
				DESC              => ($phpgw->db->f('descr')?$phpgw->db->f('descr'):'&nbsp;'),
				THREADS_LINK      => $phpgw->link('/forum/threads.php' ,'forum_id=' . $phpgw->db->f('id')),
				'value_last_post' => $last_post_date,
				'value_total'     => $total
			));
	
			$phpgw->template->fp('rows','row',True);
		}
	}

	$phpgw->template->pfp('out','list');

	$phpgw->common->phpgw_footer();
?>