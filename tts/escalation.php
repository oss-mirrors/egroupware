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

	$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['tts']['title'].' - '.lang("List of escalation parameters for certain group.");
	$GLOBALS['phpgw']->common->phpgw_header();

	$GLOBALS['phpgw']->historylog = createobject('phpgwapi.historylog','tts');

	$GLOBALS['phpgw']->template->set_file('escalation','escalation.tpl');
	$GLOBALS['phpgw']->template->set_block('escalation', 'escalation_list', 'escalation_list');
	$GLOBALS['phpgw']->template->set_block('escalation', 'escalation_row', 'escalation_row');

	// select what tickets to view
	$filter = reg_var('filter','GET');
	$start  = reg_var('start','GET','numeric',0);
	$sort   = reg_var('sort','GET');
	//$order  = reg_var('order','GET');

    $order  = get_var('order',array('POST','GET'),'account_lid');

	if (!$sort)
	{
		$sortmethod = 'order by account_lid';
	}
	else
	{
		$sortmethod = "order by $order $sort";
	}

	if (!$filter)
	{
		//$filtermethod = '';
                $filtermethod = ' e, phpgw_accounts a
                WHERE e.ticket_group = a.account_id ';


	}
	else
	{
		//$filtermethod = "where $filter";
                $filtermethod = " e, phpgw_accounts a
                WHERE e.ticket_group = a.account_id AND $filter";
	}

	$GLOBALS['phpgw']->db->query("select * from phpgw_tts_escalation $filtermethod $sortmethod",__LINE__,__FILE__);
	$numfound = $GLOBALS['phpgw']->db->num_rows();

	// fill header
	$GLOBALS['phpgw']->template->set_var('tts_head_escalation_id', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'escalation_id',$order,'/tts/escalation.php',lang('Escalation ID')));
	$GLOBALS['phpgw']->template->set_var('tts_head_group_name', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'account_lid',$order,'/tts/escalation.php',lang('Group Name')));
    $GLOBALS['phpgw']->template->set_var('tts_head_priority_1', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_priority_1',$order,'/tts/escalation.php',lang('Priority start')));
    $GLOBALS['phpgw']->template->set_var('tts_head_priority_2', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_priority_2',$order,'/tts/escalation.php',lang('Priority end')));
    $GLOBALS['phpgw']->template->set_var('tts_head_time_1', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'time_1',$order,'/tts/escalation.php',lang('Escalation time 1')));
    $GLOBALS['phpgw']->template->set_var('tts_head_time_2', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'time_2',$order,'/tts/escalation.php',lang('Escalation time 2')));
    $GLOBALS['phpgw']->template->set_var('tts_head_time_3', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'time_3',$order,'/tts/escalation.php',lang('Escalation time 3')));
    $GLOBALS['phpgw']->template->set_var('tts_head_email_1', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'email_1',$order,'/tts/escalation.php',lang('Escalation email 1')));
    $GLOBALS['phpgw']->template->set_var('tts_head_email_2', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'email_2',$order,'/tts/escalation.php',lang('Escalation email 2')));

	$GLOBALS['phpgw']->template->set_var('lang_add',lang('Add'));
	$GLOBALS['phpgw']->template->set_var('lang_edit',lang('Edit'));
	$GLOBALS['phpgw']->template->set_var('lang_delete',lang('Delete'));

	if ($GLOBALS['phpgw']->db->num_rows() == 0)
	{
		$GLOBALS['phpgw']->template->set_var('rows', '<p><center>'.lang('No escalations found').'</center>');
	}
	else
	{
		$i = 0;
		while ($GLOBALS['phpgw']->db->next_record())
		{
			$GLOBALS['phpgw']->template->set_var('tts_col_status','');

			$GLOBALS['phpgw']->template->set_var('row_class', ++$i & 1 ? 'row_on' : 'row_off' );
			$GLOBALS['phpgw']->template->set_var('tts_escalation_edit_link', $GLOBALS['phpgw']->link('/tts/edit_escalation.php','escalation_id=' . $GLOBALS['phpgw']->db->f('escalation_id')));
			$GLOBALS['phpgw']->template->set_var('tts_escalation_delete_link', $GLOBALS['phpgw']->link('/tts/delete_escalation.php','escalation_id=' . $GLOBALS['phpgw']->db->f('escalation_id')));

			$GLOBALS['phpgw']->template->set_var('escalation_id',$GLOBALS['phpgw']->db->f('escalation_id'));
            $GLOBALS['phpgw']->template->set_var('group_name',$GLOBALS['phpgw']->db->f('account_lid'));
            $GLOBALS['phpgw']->template->set_var('priority_1',$GLOBALS['phpgw']->db->f('ticket_priority_1'));
            $GLOBALS['phpgw']->template->set_var('priority_2',$GLOBALS['phpgw']->db->f('ticket_priority_2'));
            $GLOBALS['phpgw']->template->set_var('time_1',$GLOBALS['phpgw']->db->f('time_1'));
            $GLOBALS['phpgw']->template->set_var('time_2',$GLOBALS['phpgw']->db->f('time_2'));
            $GLOBALS['phpgw']->template->set_var('time_3',$GLOBALS['phpgw']->db->f('time_3'));
            //$GLOBALS['phpgw']->template->set_var('email_1',$GLOBALS['phpgw']->db->f('email_1'));
            //$GLOBALS['phpgw']->template->set_var('email_2',$GLOBALS['phpgw']->db->f('email_2'));
            $email_1 = $GLOBALS['phpgw']->db->f('email_1');
            $email_2 = $GLOBALS['phpgw']->db->f('email_2');

            $email_1_label = "No";
            $email_2_label = "No";

            if ($email_1 > 0)
            {
                     $email_1_label = "Yes";
            }
            if ($email_2 > 0)
            {
                     $email_2_label = "Yes";
            }
            $GLOBALS['phpgw']->template->set_var('email_1',lang($email_1_label));
            $GLOBALS['phpgw']->template->set_var('email_2',lang($email_2_label));

			/*$GLOBALS['phpgw']->template->set_var('state_name',
				try_lang($GLOBALS['phpgw']->db->f('state_name'),False,True));
			$GLOBALS['phpgw']->template->set_var('state_description',
				try_lang($GLOBALS['phpgw']->db->f('state_description'),False));
            */


			$GLOBALS['phpgw']->template->parse('rows','escalation_row',True);
		}
	}
	$GLOBALS['phpgw']->template->set_var('row_class', ++$i & 1 ? 'row_on' : 'row_off' );
	$GLOBALS['phpgw']->template->set_var('tts_escalation_add_link', $GLOBALS['phpgw']->link('/tts/edit_escalation.php','escalation_id=0'));

	// this is a workaround to clear the subblocks autogenerated vars
	$GLOBALS['phpgw']->template->set_var('escalation_row','');

	$GLOBALS['phpgw']->template->pfp('out','escalation');

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
