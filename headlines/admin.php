<?php
	/**************************************************************************\
	* phpGroupWare - Headlines Administration                                  *
	* http://www.phpgroupware.org                                              *
	* Written by Joseph Engo <jengo@phpgroupware.org>                          *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$phpgw_info['flags'] = array(
		'admin_only'              => True,
		'currentapp'              => 'headlines',
		'enable_nextmatchs_class' => True
	);
	include('../header.inc.php');

	// This is done for a reason (jengo)
	$phpgw->template->set_root($phpgw->common->get_tpl_dir('headlines'));
	$phpgw->template->set_file(array(
		'admin' => 'admin.tpl'
	));
	$phpgw->template->set_block('admin','list');
	$phpgw->template->set_block('admin','row');
	$phpgw->template->set_block('admin','row_empty');

	$phpgw->template->set_var('th_bg',$phpgw_info['theme']['th_bg']);
	$phpgw->template->set_var('title',lang('Headline Sites'));
	$phpgw->template->set_var('lang_site',lang('Site'));
	$phpgw->template->set_var('lang_edit',lang('Edit'));
	$phpgw->template->set_var('lang_delete',lang('Delete'));
	$phpgw->template->set_var('lang_view',lang('View'));
	$phpgw->template->set_var('lang_add',lang('Add'));

	$phpgw->db->query('select count(*) from phpgw_headlines_sites',__LINE__,__FILE__);
	$phpgw->db->next_record();

	if (! $phpgw->db->f(0))
	{
		$phpgw->template->set_var('lang_row_empty',lang('No headlines found'));
		$phpgw->nextmatchs->template_alternate_row_color($phpgw->template);
		$phpgw->template->parse('rows','row_empty');
	}

	$phpgw->db->query('select con,display from phpgw_headlines_sites order by display',__LINE__,__FILE__);
	while ($phpgw->db->next_record())
	{
		$phpgw->nextmatchs->template_alternate_row_color($phpgw->template);

		$phpgw->template->set_var('row_display',$phpgw->db->f('display'));
		$phpgw->template->set_var('row_edit',$phpgw->link('/headlines/editheadline.php','con='.$phpgw->db->f('con')));
		$phpgw->template->set_var('row_delete',$phpgw->link('/headlines/deleteheadline.php','con='.$phpgw->db->f('con')));
		$phpgw->template->set_var('row_view',$phpgw->link('/headlines/viewheadline.php','con='.$phpgw->db->f('con')));

		$phpgw->template->parse('rows','row',True);
	}

	$phpgw->template->set_var('add_url',$phpgw->link('/headlines/newheadline.php'));
	$phpgw->template->set_var('grab_more_url',$phpgw->link('/headlines/grabnewssites.php'));
	$phpgw->template->set_var('lang_grab_more',lang('Grab New News Sites'));

	$phpgw->template->pfp('out','list');

	$phpgw->common->phpgw_footer();

?>