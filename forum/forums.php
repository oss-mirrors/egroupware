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

	$phpgw->db->query("select * from phpgw_forum_categories where id='$cat'",__LINE__,__FILE__);
	$phpgw->db->next_record();

	$phpgw->template->set_var(array(
		BGROUND        => $phpgw_info['theme']['th_bg'],
		IMG_URL_PREFIX => PHPGW_IMAGES . '/',
		CATEGORY       => $phpgw->db->f('name'),
		LANG_MAIN      => lang('Forum'),
		MAIN_LINK      => $phpgw->link('/forum/index.php')
	));

	$phpgw->db->query("select * from phpgw_forum_forums where cat_id='$cat'",__LINE__,__FILE__);
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
			$phpgw->nextmatchs->template_alternate_row_color(&$phpgw->template);
			$phpgw->template->set_var(array(
				NAME         => $phpgw->db->f('name'),
				DESC         => ($phpgw->db->f('descr')?$phpgw->db->f('descr'):'&nbsp;'),
				THREADS_LINK => $phpgw->link('/forum/threads.php' ,'cat=' .	$cat . '&for=' . $phpgw->db->f('id'))
			));
	
			$phpgw->template->fp('rows','row',True);
		}
	}

	$phpgw->template->pfp('out','list');

	$phpgw->common->phpgw_footer();
?>