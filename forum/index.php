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

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp'              => 'forum',
		'enable_nextmatchs_class' => True
	);
	include('../header.inc.php');

	$GLOBALS['phpgw']->template->set_file('INDEX' , 'index.body.tpl');
	$GLOBALS['phpgw']->template->set_block('INDEX','CategoryForum','CatF');

	$db2 = $GLOBALS['phpgw']->db;
	$GLOBALS['phpgw']->db->query("select * from phpgw_forum_categories",__LINE__,__FILE__);
	$GLOBALS['phpgw']->template->set_var(array(
		'IMG_URL_PREFIX' => PHPGW_IMAGES . '/',
		'BGROUND'        => $GLOBALS['phpgw_info']['theme']['th_bg'],
		'FORUM'          => lang('Forum')
	));

	while ($GLOBALS['phpgw']->db->next_record())
	{
		$db2->query("select max(postdate) from phpgw_forum_threads where cat_id='" . $GLOBALS['phpgw']->db->f('id')
			. "'",__LINE__,__FILE__);
		$db2->next_record();
		if ($db2->f(0))
		{
			$last_post_date = $GLOBALS['phpgw']->common->show_date($GLOBALS['phpgw']->db->from_timestamp($db2->f(0)));
		}
		else
		{
			$last_post_date = '&nbsp;';
		}

		$db2->query("select count(*) from phpgw_forum_threads where cat_id='" . $GLOBALS['phpgw']->db->f('id')
			. "'",__LINE__,__FILE__);
		$db2->next_record();
		$total = $db2->f(0);

		$tr_color = $GLOBALS['phpgw']->nextmatchs->alternate_row_color($tr_color);
		$GLOBALS['phpgw']->template->set_var(array(
			'COLOR'           => $tr_color,
			'CAT'             => $GLOBALS['phpgw']->db->f('name'),
			'DESC'            => ($GLOBALS['phpgw']->db->f('descr')?$GLOBALS['phpgw']->db->f('descr'):'&nbsp;'),
			'CAT_LINK'        => $GLOBALS['phpgw']->link('/forum/forums.php','cat_id=' .  $GLOBALS['phpgw']->db->f('id')),
			'value_last_post' => $last_post_date,
			'value_total'     => $total
		));
		$GLOBALS['phpgw']->template->parse('CatF','CategoryForum',true);
	}

	$GLOBALS['phpgw']->template->parse('Out','INDEX');
	$GLOBALS['phpgw']->template->p('Out');

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
