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

	// $Id$
	// $Source$

	$submit = $HTTP_POST_VARS['submit'];
	$cancel = $HTTP_POST_VARS['cancel'];
	$transition_id = $HTTP_POST_VARS['transition_id'];
	if ($transition_id==0) {
		$transition_id = $HTTP_GET_VARS['transition_id'];
	}
	
	if($submit || $cancel)
	{
		$GLOBALS['phpgw_info']['flags'] = array(
			'noheader' => True,
			'nonavbar' => True
		);
	}

	$GLOBALS['phpgw_info']['flags']['currentapp']          = 'tts';
	$GLOBALS['phpgw_info']['flags']['enable_send_class']   = True;
	$GLOBALS['phpgw_info']['flags']['enable_config_class'] = True;
	$GLOBALS['phpgw_info']['flags']['enable_categories_class'] = True;

	include('../header.inc.php');

	$GLOBALS['phpgw']->config->read_repository();

	if($cancel)
	{
		$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/tts/transitions.php'));
	}

	if($submit)
	{
		if ($transition_id==0) {
			$GLOBALS['phpgw']->db->query("insert into phpgw_tts_transitions (transition_name,transition_description,transition_source_state,transition_target_state) values ('"
			. $transition['name'] . "','"
			. $transition['description'] . "',"
			. $transition['source_state'] . ", "
			. $transition['target_state']. ")",__LINE__,__FILE__);
		}
		else {
			$GLOBALS['phpgw']->db->query("update phpgw_tts_transitions "
				. " set transition_name='". addslashes($transition['name']) . "', "
				. " transition_description='". addslashes($transition['description']) . "', "
				. " transition_source_state=". $transition['source_state']. ", "
				. " transition_target_state=". $transition['target_state']
				. " WHERE transition_id=".intval($transition_id),__LINE__,__FILE__);
	
		}
		$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/tts/transitions.php'));
	}
	else {
		// select the ticket that you selected
		$GLOBALS['phpgw']->db->query("select * from phpgw_tts_transitions where transition_id='$transition_id'",__LINE__,__FILE__);
		$GLOBALS['phpgw']->db->next_record();

		$transition['name'] 				= $GLOBALS['phpgw']->db->f('transition_name');
		$transition['description']  	= $GLOBALS['phpgw']->db->f('transition_description');
		$transition['source_state']   = $GLOBALS['phpgw']->db->f('transition_source_state');
		$transition['target_state']   = $GLOBALS['phpgw']->db->f('transition_target_state');

		$GLOBALS['phpgw']->template->set_file(array(
			'edit_transition'   => 'edit_transition.tpl'
		));
		$GLOBALS['phpgw']->template->set_block('edit_transition','form');

		$GLOBALS['phpgw']->template->set_var('lang_edit_a_state',($state_id==0?lang('Create new transition'):lang('Edit the transition')));
		$GLOBALS['phpgw']->template->set_var('form_action', $GLOBALS['phpgw']->link('/tts/edit_transition.php','&transition_id='.$transition_id));

		$GLOBALS['phpgw']->template->set_var('lang_transition_name',lang('Transition name'));
		$GLOBALS['phpgw']->template->set_var('lang_transition_description', lang('Description'));
		$GLOBALS['phpgw']->template->set_var('lang_source_state', lang('Source State'));
		$GLOBALS['phpgw']->template->set_var('lang_target_state', lang('Target State'));
		$GLOBALS['phpgw']->template->set_var('lang_submit',lang('Submit'));
		$GLOBALS['phpgw']->template->set_var('lang_cancel',lang('Cancel'));

		$GLOBALS['phpgw']->template->set_var('row_off', $GLOBALS['phpgw_info']['theme']['row_off']);
		$GLOBALS['phpgw']->template->set_var('row_on', $GLOBALS['phpgw_info']['theme']['row_on']);
		$GLOBALS['phpgw']->template->set_var('th_bg', $GLOBALS['phpgw_info']['theme']['th_bg']);

		$GLOBALS['phpgw']->template->set_var('value_name',$transition['name']);
		$GLOBALS['phpgw']->template->set_var('value_description',$transition['description']);
		$GLOBALS['phpgw']->template->set_var('options_source_state',listid_field('phpgw_tts_states','state_name','state_id',$transition['source_state']));
		$GLOBALS['phpgw']->template->set_var('options_target_state',listid_field('phpgw_tts_states','state_name','state_id',$transition['target_state']));

		$GLOBALS['phpgw']->template->pfp('out','form');
		$GLOBALS['phpgw']->common->phpgw_footer();
	}

?>
