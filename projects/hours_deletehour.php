<?php
	/**************************************************************************\
	* phpGroupWare - projcts/projecthours                                      *
	* (http://www.phpgroupware.org)                                            *
	* Written by Bettina Gille  [ceb@phpgoupware.org]                          *
	*          & Jens Lentfoehr <sw@lf.shlink.de>                              *
	* --------------------------------------------------------                 *
	* This program is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU General Public License as published by the    *
	* Free Software Foundation; either version 2 of the License, or (at your   *
	* option) any later version.                                               *
	\**************************************************************************/
	/* $Id$ */
  
	if (! $id)
	{
		Header('Location: ' . $phpgw->link('/projects/hours_index.php','sort=' . $sort . '&order=' . $order . '&query=' . $query
											. '&start=' . $start . '&filter=' . $filter));
	}

	if ($confirm)
	{
		$phpgw_info['flags'] = array('noheader' => True, 
									'nonavbar' => True);
	}

	$phpgw_info['flags']['currentapp'] = 'projects';
	include('../header.inc.php');

	if ($confirm)
	{
		$phpgw->db->query("delete from phpgw_p_hours where id='$id'");
		Header('Location: ' . $phpgw->link('/projects/hours_listhours.php','filter=' . $filter . '&sort=' . $sort . '&order=' . $order
											. '&query=' . $query . '&start=' . $start));
	}
	else
	{
		$hidden_vars = '<input type="hidden" name="sort" value="' . $sort . '">' . "\n"
					. '<input type="hidden" name="order" value="' . $order . '">' . "\n"
					. '<input type="hidden" name="query" value="' . $query . '">' . "\n"
					. '<input type="hidden" name="start" value="' . $start . '">' . "\n"
					. '<input type="hidden" name="id" value="' . $id . '">' . "\n"
					. '<input type="hidden" name="filter" value="' . $filter . '">' . "\n";

		$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
		$t->set_file(array('job_delete' => 'delete.tpl'));

		$t->set_var('deleteheader',lang('Are you sure you want to delete this job ?'));
		$t->set_var('hidden_vars',$hidden_vars);
		$t->set_var('nolink',$phpgw->link('/projects/hours_listhours.php','sort=' . $sort . '&order=' . $order . '&query=' . $query
											. '&start=' . $start . '&filter=' . $filter));
		$t->set_var('lang_no',lang('No'));

		$t->set_var('action_url',$phpgw->link('hours_deletehour.php','id=' . $id));
		$t->set_var('lang_yes',lang('Yes'));
		$t->pparse('out','job_delete');
	}
	$phpgw->common->phpgw_footer();
?>
