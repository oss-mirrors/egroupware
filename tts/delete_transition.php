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

	// select what tickets to view
	$cancel     = $HTTP_POST_VARS['cancel'];
	$submit 		= $HTTP_POST_VARS['submit'];
	
	$transition_id  	= $HTTP_GET_VARS['transition_id'];

	if($submit || $cancel || $transition_id==0)
	{
		$GLOBALS['phpgw_info']['flags'] = array(
			'noheader' => True,
			'nonavbar' => True
		);
	}

	$GLOBALS['phpgw_info']['flags']['currentapp'] = 'tts';
	$GLOBALS['phpgw_info']['flags']['enable_contacts_class'] = True;
	$GLOBALS['phpgw_info']['flags']['enable_categories_class'] = True;
	$GLOBALS['phpgw_info']['flags']['enable_nextmatchs_class'] = True;
	include('../header.inc.php');

	if ($transition_id==0) {
		$transition_id  	= $HTTP_POST_VARS['transition_id'];
		if ($transition_id==0) {
			$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/tts/transitions.php'));
		}
	}
	if($cancel)
	{
		$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/tts/transitions.php'));
	}

	if($submit){
		$GLOBALS['phpgw']->db->query("delete from phpgw_tts_transitions where transition_id=$transition_id",__LINE__,__FILE__);
		$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/tts/transitions.php'));
	}


	$GLOBALS['phpgw']->template->set_file('delete_transition','delete_transition.tpl');

	$GLOBALS['phpgw']->template->set_var('lang_delete_transition',lang('Deleting the transition'));
	$s=id2field('phpgw_tts_transitions','transition_name','transition_id',$transition_id);
	$GLOBALS['phpgw']->template->set_var('lang_are_you_sure',lang('You want to delete the transition %1. Are you sure?',$s));

	$GLOBALS['phpgw']->template->set_var('delete_transition_link',
		$GLOBALS['phpgw']->link('/tts/delete_transition.php','transition_id='.$transition_id));
	$GLOBALS['phpgw']->template->set_var('lang_ok',lang('Delete'));
	$GLOBALS['phpgw']->template->set_var('lang_cancel',lang('Cancel'));

	// fill header
	$GLOBALS['phpgw']->template->set_var('tts_head_bgcolor',$GLOBALS['phpgw_info']['theme']['th_bg'] );
	$GLOBALS['phpgw']->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg'] );

	$GLOBALS['phpgw']->template->pfp('out','delete_transition');

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
