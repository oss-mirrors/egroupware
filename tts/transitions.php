<?php
	/**************************************************************************\
	* phpGroupWare - Trouble Ticket System                                     *
	* http://www.phpgroupware.org                                              *
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
	include('../header.inc.php');

	$GLOBALS['phpgw']->historylog = createobject('phpgwapi.historylog','tts');

	$GLOBALS['phpgw']->template->set_file('transitions','transitions.tpl');
	$GLOBALS['phpgw']->template->set_block('transitions', 'transition_list', 'transition_list');
	$GLOBALS['phpgw']->template->set_block('transitions', 'transition_row', 'transition_row');

	// select what tickets to view
	$filter = $HTTP_GET_VARS['filter'];
	$start  = $HTTP_GET_VARS['start'];
	$sort   = $HTTP_GET_VARS['sort'];
	$order  = $HTTP_GET_VARS['order'];

	if (!$sort)
	{
		$sortmethod = 'order by transition_name';
	}
	else
	{
		$sortmethod = "order by $order $sort";
	}

	if (!$filter)
	{
		$filtermethod = '';
	}
	else
	{
		$filtermethod = "where $filter";
	}

	$db2 = $GLOBALS['phpgw']->db;
	$GLOBALS['phpgw']->db->query("select * from phpgw_tts_transitions $filtermethod $sortmethod",__LINE__,__FILE__);
	$numfound = $GLOBALS['phpgw']->db->num_rows();

	// fill header
	$GLOBALS['phpgw']->template->set_var('lang_list_of_transitions',lang("List of available tickets' transitions."));
	$GLOBALS['phpgw']->template->set_var('tts_head_bgcolor',$GLOBALS['phpgw_info']['theme']['th_bg'] );
	$GLOBALS['phpgw']->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg'] );
	$GLOBALS['phpgw']->template->set_var('tts_head_transition_id', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'transition_id',$order,'/tts/transitions.php','#'));
	$GLOBALS['phpgw']->template->set_var('tts_head_transition', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'transition_name',$order,'/tts/transitions.php',lang('Transition')));
	$GLOBALS['phpgw']->template->set_var('tts_head_description', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'transition_description',$order,'/tts/transitions.php',lang('Description')));
	$GLOBALS['phpgw']->template->set_var('tts_head_target_state', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'transition_target_state',$order,'/tts/transitions.php',lang('Target State')));
	$GLOBALS['phpgw']->template->set_var('tts_head_source_state', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'transition_source_state',$order,'/tts/transitions.php',lang('Source State')));
	$GLOBALS['phpgw']->template->set_var('lang_add',lang('Add'));
	$GLOBALS['phpgw']->template->set_var('lang_edit',lang('Edit'));
	$GLOBALS['phpgw']->template->set_var('lang_delete',lang('Delete'));

	if ($GLOBALS['phpgw']->db->num_rows() == 0)
	{
		$GLOBALS['phpgw']->template->set_var('rows', '<p><center>'.lang('No transitions found').'</center>');
	}
	else
	{
		while ($GLOBALS['phpgw']->db->next_record())
		{
			$GLOBALS['phpgw']->template->set_var('tts_col_status','');
			$tr_color = $GLOBALS['phpgw_info']['theme']['bg01']; 

			$db2->next_record();

			$GLOBALS['phpgw']->template->set_var('tts_row_color', $tr_color );
			$GLOBALS['phpgw']->template->set_var('tts_transitionedit_link', $GLOBALS['phpgw']->link('/tts/edit_transition.php','transition_id=' . $GLOBALS['phpgw']->db->f('transition_id')));
			$GLOBALS['phpgw']->template->set_var('tts_transitiondelete_link', $GLOBALS['phpgw']->link('/tts/delete_transition.php','transition_id=' . $GLOBALS['phpgw']->db->f('transition_id')));

			$GLOBALS['phpgw']->template->set_var('transition_id',$GLOBALS['phpgw']->db->f('transition_id'));
			$GLOBALS['phpgw']->template->set_var('transition_name',$GLOBALS['phpgw']->db->f('transition_name'));
			$GLOBALS['phpgw']->template->set_var('transition_description',$GLOBALS['phpgw']->db->f('transition_description'));
			$GLOBALS['phpgw']->template->set_var('transition_source_state',
				id2field('phpgw_tts_states','state_name','state_id',
				$GLOBALS['phpgw']->db->f('transition_source_state')));
			$GLOBALS['phpgw']->template->set_var('transition_target_state',
				id2field('phpgw_tts_states','state_name','state_id',
				$GLOBALS['phpgw']->db->f('transition_target_state')));

			$GLOBALS['phpgw']->template->parse('rows','transition_row',True);
		}
	}
	$GLOBALS['phpgw']->template->set_var('tts_transitionadd_link', $GLOBALS['phpgw']->link('/tts/edit_transition.php','transition_id=0'));

	// this is a workaround to clear the subblocks autogenerated vars
	$GLOBALS['phpgw']->template->set_var('transition_row','');

	$GLOBALS['phpgw']->template->pfp('out','transitions');

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
