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
		'currentapp'              => 'admin',
		'enable_nextmatchs_class' => True,
		'nonavbar'                => True,
		'noheader'                => True
	);
	include('../header.inc.php');

	if (($con) && (! $confirm))
	{
		$phpgw->common->phpgw_header();
		echo parse_navbar();

		// This is done for a reason (jengo)
		$phpgw->template->set_root($phpgw->common->get_tpl_dir('headlines'));
	
		$phpgw->template->set_file(array(
			'delete_form' => 'admin_delete.tpl'
		));

		$phpgw->template->set_var('title',lang('Headlines Administration - Delete headline'));
		$phpgw->template->set_var('lang_message',lang('Are you sure you want to delete this news site ?'));
		$phpgw->template->set_var('lang_no',lang('No'));
		$phpgw->template->set_var('lang_yes',lang('Yes'));

		$phpgw->template->set_var('link_no',$phpgw->link('/headlines/admin.php'));
		$phpgw->template->set_var('link_yes',$phpgw->link('/headlines/deleteheadline.php',"con=$con&confirm=true"));

		$phpgw->template->pfp('out','delete_form');

		$phpgw->common->phpgw_footer();
	}
	else
	{
		$phpgw->db->transaction_begin();

		$phpgw->db->query("delete from phpgw_headlines_sites where con='$con'",__LINE__,__FILE__);
		$phpgw->db->query("delete from phpgw_headlines_cached where site='$con'",__LINE__,__FILE__);

		$phpgw->db->query("SELECT * FROM phpgw_preferences",__LINE__,__FILE__);
		while ($phpgw->db->next_record())
		{
			if ($phpgw->db->f('preference_owner') == $phpgw_info['user']['account_id'])
			{
				if ($phpgw_info['user']['preferences']['headlines'][$con])
				{
					$phpgw->preferences->delete('headlines',$con);
					$phpgw->preferences->commit();
				}
			}
			else
			{
				$phpgw_newuser['user']['preferences'] = $phpgw->db->f('preference_value');
				if ($phpgw_newuser['user']['preferences']['headlines'][$con])
				{
					$phpgw->preferences->delete_newuser('headlines',$con);
					$phpgw->preferences->commit_user($phpgw->db->f('preference_owner'));
				}
			}
		}

		$phpgw->db->transaction_commit();
		$phpgw->redirect($phpgw->link('/headlines/admin.php','cd=16'));
	}
?>
