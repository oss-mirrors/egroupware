<?php
	/**************************************************************************\
	* eGroupWare - Trouble Ticket System                                       *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	/* Note to self:
	** Self ... heres the query to use when limiting access to entrys within a group
	** The acl class *might* handle this instead .... not sure
	** select distinct group_ticket_id, phpgw_tts_groups.group_ticket_id, phpgw_tts_tickets.*
	** from phpgw_tts_tickets, phpgw_tts_groups where ticket_id = group_ticket_id and group_id in (14,15);
	*/

	/* ACL levels
	** 1 - Read ticket within your group only
	** 2 - Close ticket
	** 4 - Allow to make changes to priority, billing hours, billing rate, category, and assigned to
	*/

	$GLOBALS['phpgw_info']['flags']['currentapp'] = 'tts';
	$GLOBALS['phpgw_info']['flags']['enable_contacts_class'] = True;
	$GLOBALS['phpgw_info']['flags']['enable_categories_class'] = True;
	$GLOBALS['phpgw_info']['flags']['enable_nextmatchs_class'] = True;
	$GLOBALS['phpgw_info']['flags']['noheader'] = True;
	include('../header.inc.php');

	$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['tts']['title'].' - '.lang("List of available groups per categories.");
	$GLOBALS['phpgw']->common->phpgw_header();

	$GLOBALS['phpgw']->historylog = createobject('phpgwapi.historylog','tts');

	$GLOBALS['phpgw']->template->set_file('cat_group','cat_group.tpl');
	$GLOBALS['phpgw']->template->set_block('cat_group', 'cat_group_list', 'cat_group_list');
	$GLOBALS['phpgw']->template->set_block('cat_group', 'cat_group_row', 'cat_group_row');

	// select what tickets to view
	$filter = reg_var('filter','GET');
	$start  = reg_var('start','GET','numeric',0);
	$sort   = reg_var('sort','GET');
	$order  = reg_var('order','GET');

	if (!$sort)
	{
		$sortmethod = 'order by cat_name';
	}
	else
	{
		$sortmethod = "order by $order $sort";
	}

	if (!$filter)
	{
		//$filtermethod = '';
                $filtermethod = ' t, phpgw_accounts a, phpgw_categories c
                WHERE t.cat_id = c.cat_id AND t.account_id = a.account_id ';


	}
	else
	{
		//$filtermethod = "where $filter";
                $filtermethod = " t, phpgw_accounts a, phpgw_categories c
                WHERE t.cat_id = c.cat_id AND t.account_id = a.account_id AND $filter";
	}

	$GLOBALS['phpgw']->db->query("select * from phpgw_tts_categories_groups $filtermethod $sortmethod",__LINE__,__FILE__);
	$numfound = $GLOBALS['phpgw']->db->num_rows();

	// fill header
	$GLOBALS['phpgw']->template->set_var('tts_head_cat_name', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'cat_name',$order,'/tts/cat_group.php',lang('Category Name')));
	$GLOBALS['phpgw']->template->set_var('tts_head_cat_description', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'cat_description',$order,'/tts/cat_group.php',lang('Category Description')));
	$GLOBALS['phpgw']->template->set_var('tts_head_group_name', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'account_lid',$order,'/tts/cat_group.php',lang('Group Name')));
	$GLOBALS['phpgw']->template->set_var('lang_add',lang('Add'));
	$GLOBALS['phpgw']->template->set_var('lang_edit',lang('Edit'));
	$GLOBALS['phpgw']->template->set_var('lang_delete',lang('Delete'));

	if ($GLOBALS['phpgw']->db->num_rows() == 0)
	{
		$GLOBALS['phpgw']->template->set_var('rows', '<p><center>'.lang('No categories, groups found').'</center>');
	}
	else
	{
		$i = 0;
		while ($GLOBALS['phpgw']->db->next_record())
		{
			$GLOBALS['phpgw']->template->set_var('tts_col_status','');

			$GLOBALS['phpgw']->template->set_var('row_class', ++$i & 1 ? 'row_on' : 'row_off' );
			$GLOBALS['phpgw']->template->set_var('tts_cat_group_edit_link', $GLOBALS['phpgw']->link('/tts/edit_cat_group.php','cat_group_id=' . $GLOBALS['phpgw']->db->f('cat_group_id')));
			$GLOBALS['phpgw']->template->set_var('tts_cat_group_delete_link', $GLOBALS['phpgw']->link('/tts/delete_cat_group.php','cat_group_id=' . $GLOBALS['phpgw']->db->f('cat_group_id').'&'.'cat_id=' . $GLOBALS['phpgw']->db->f('cat_id').'&'.'account_id=' . $GLOBALS['phpgw']->db->f('account_id')));

			//$GLOBALS['phpgw']->template->set_var('cat_group_id',$GLOBALS['phpgw']->db->f('cat_group_id'));
                        $GLOBALS['phpgw']->template->set_var('cat_name',$GLOBALS['phpgw']->db->f('cat_name'));
                        $GLOBALS['phpgw']->template->set_var('cat_description',$GLOBALS['phpgw']->db->f('cat_description'));
                        $GLOBALS['phpgw']->template->set_var('group_name',$GLOBALS['phpgw']->db->f('account_lid'));
			/*$GLOBALS['phpgw']->template->set_var('cat_name',
				try_lang($GLOBALS['phpgw']->db->f('state_name'),False,True));
			$GLOBALS['phpgw']->template->set_var('state_description',
				try_lang($GLOBALS['phpgw']->db->f('state_description'),False));
                        */

			$GLOBALS['phpgw']->template->parse('rows','cat_group_row',True);
		}
	}
	$GLOBALS['phpgw']->template->set_var('row_class', ++$i & 1 ? 'row_on' : 'row_off' );
	$GLOBALS['phpgw']->template->set_var('tts_cat_group_add_link', $GLOBALS['phpgw']->link('/tts/edit_cat_group.php','cat_group_id=0'));

	// this is a workaround to clear the subblocks autogenerated vars
	$GLOBALS['phpgw']->template->set_var('cat_group_row','');

	$GLOBALS['phpgw']->template->pfp('out','cat_group');

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
