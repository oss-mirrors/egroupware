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

	$phpgw->template->set_file('INDEX' , 'index.body.tpl');
	$phpgw->template->set_block('INDEX','CategoryForum','CatF');

	$phpgw->db->query("select * from phpgw_forum_categories",__LINE__,__FILE__);
	$phpgw->template->set_var(array(
		IMG_URL_PREFIX => PHPGW_IMAGES . '/',
		'BGROUND'      => $phpgw_info['theme']['th_bg'],
		'FORUM'        => lang('Forum')
	));

	while($phpgw->db->next_record())
	{
		$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
		$phpgw->template->set_var(array(
			COLOR	=> $tr_color,
			CAT	=> $phpgw->db->f('name'),
			DESC	=> ($phpgw->db->f('descr')?$phpgw->db->f('descr'):'&nbsp;'),
			CAT_LINK	=> $phpgw->link('/forum/forums.php','cat=' .  $phpgw->db->f('id'))
		));
		$phpgw->template->parse('CatF','CategoryForum',true);

	}

	$phpgw->template->parse('Out','INDEX');
	$phpgw->template->p('Out');


	$phpgw->common->phpgw_footer();

?>
