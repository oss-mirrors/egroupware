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

	$GLOBALS['phpgw']->template->set_file('_list' ,'forums.body.tpl');

	$GLOBALS['phpgw']->template->set_block('_list','row_empty');
	$GLOBALS['phpgw']->template->set_block('_list','list');
	$GLOBALS['phpgw']->template->set_block('_list','row');

	$GLOBALS['phpgw']->db->query("select * from phpgw_forum_categories where id='$cat_id'",__LINE__,__FILE__);
	$GLOBALS['phpgw']->db->next_record();

	$GLOBALS['phpgw']->template->set_var(array(
		'BGROUND'        => $GLOBALS['phpgw_info']['theme']['th_bg'],
		'IMG_URL_PREFIX' => PHPGW_IMAGES . '/',
		'CATEGORY'       => $GLOBALS['phpgw']->db->f('name'),
		'LANG_MAIN'      => lang('Forum'),
		'MAIN_LINK'      => $GLOBALS['phpgw']->link('/forum/index.php')
	));

	$db2 = $GLOBALS['phpgw']->db;
	$GLOBALS['phpgw']->db->query("select * from phpgw_forum_forums where cat_id='$cat_id'",__LINE__,__FILE__);
	if (! $GLOBALS['phpgw']->db->num_rows())
	{
		$GLOBALS['phpgw']->nextmatchs->template_alternate_row_color(&$GLOBALS['phpgw']->template);
		$GLOBALS['phpgw']->template->set_var('lang_no_forums',lang('There are no forums in this category'));

		$GLOBALS['phpgw']->template->fp('rows','row_empty');
	}
	else
	{
		while ($GLOBALS['phpgw']->db->next_record())
		{
			$db2->query("select max(postdate) from phpgw_forum_threads where cat_id='$cat_id' and for_id='"
				. $GLOBALS['phpgw']->db->f('id') . "'",__LINE__,__FILE__);
			$db2->next_record();

			if ($db2->f(0))
			{
				$last_post_date = $GLOBALS['phpgw']->common->show_date($GLOBALS['phpgw']->db->from_timestamp($db2->f(0)));
			}
			else
			{
				$last_post_date = '&nbsp;';
			}

			$db2->query("select count(*) from phpgw_forum_threads where cat_id='$cat_id' and for_id='"
				. $GLOBALS['phpgw']->db->f('id') . "'",__LINE__,__FILE__);
			$db2->next_record();

			$total = $db2->f(0);

			$GLOBALS['phpgw']->nextmatchs->template_alternate_row_color(&$GLOBALS['phpgw']->template);
			$GLOBALS['phpgw']->template->set_var(array(
				NAME              => $GLOBALS['phpgw']->db->f('name'),
				DESC              => ($GLOBALS['phpgw']->db->f('descr')?$GLOBALS['phpgw']->db->f('descr'):'&nbsp;'),
				THREADS_LINK      => $GLOBALS['phpgw']->link('/forum/threads.php' ,'forum_id=' . $GLOBALS['phpgw']->db->f('id')),
				'value_last_post' => $last_post_date,
				'value_total'     => $total
			));
	
			$GLOBALS['phpgw']->template->fp('rows','row',True);
		}
	}

	$GLOBALS['phpgw']->template->pfp('out','list');

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
