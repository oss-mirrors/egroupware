<?php
  /**************************************************************************\
  *eGroupWare - Setup                                                        *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
  /* $Id$ */

	$oProc->query("DELETE FROM phpgw_tts_states");
	$oProc->query("DELETE FROM phpgw_tts_transitions");

	// for undefined ticket id must be -1
	$oProc->query("insert into phpgw_tts_states(state_id,state_name,state_description,state_initial)"
	." values('-1','UNDEFINED','The ticket was existent before the Petri Net infrastructure was defined and should be assigned a state.','0')");

	$states = array(		
		'NEW'  => 'A new ticket has been reported.',	
		'ASSIGNED'  => 'The ticket has been assigned.',
		'RESOLVED_FALSE_ALARM'  => 'The ticket has been successfully resolved. It was False Alarm (provocation).',
		'RESOLVED_OPER'  => 'The ticket has been successfully resolved by Help Desk.',
		'ACCEPTED'  => 'The ticket has been rejected.',
		'REJECTED'  => 'The ticket has been accepted by the owner, who is about to work on it.',
		'TOVALIDATE'  => 'The ticket has been worked on and the work requires validation.',
		'PENDING'  => 'The work on ticket is temporarily paused or blocked.',
		'POSTPONED'  => 'The ticket has been successfully resolved.',
		'VERIFIED'  => 'The work on ticket is postponed.',
		'RETURNED_VERIFIER'    => 'The ticket has been reopened for further work by verifier.',
		'RETURNED_OPER'=> 'The ticket has been worked on and the work requires validation.',
		'RESOLVED'   => 'The ticket has been successfully resolved.'
	);
	foreach($states as $state => $desc)
	{
		$oProc->query("insert into phpgw_tts_states(state_name,state_description) values('$state','$desc')");
		$states[$state] = $oProc->m_odb->get_last_insert_id('phpgw_tts_states','state_id');
	}
	
	//for new state state_initial must be 1 because only then ticket can be put into this state
	$oProc->query("update phpgw_tts_states set state_initial = '1' where state_name = 'NEW'");
	
	
	
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
		." values('REJECT', 'Reject the returning of ticket from verifier back to me.', $states[RETURNED_VERIFIER],$states[REJECTED])");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
		." values('REJECT', 'Reject the returning of ticket from Help Desk back to me.', $states[RETURNED_OPER],$states[REJECTED])");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
		." values('TONEW', 'Put the preexistent ticket into the state NEW.', -1,$states[NEW])");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
		." values('TOASSIGN', 'The ticket is assigned.', $states[NEW],$states[ASSIGNED])");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
		." values('RESOLVE_FALSE_ALARM', 'The ticket is false alarm (provocation). There is no need to work on it.', $states[NEW],$states[RESOLVED_FALSE_ALARM])");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
		." values('RESOLVE_OPER', 'The ticket is succesfully resolved by Help Desk.', $states[NEW],$states[RESOLVED_OPER])");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
		." values('ACCEPT', 'Accept the ticket to work on it.', $states[ASSIGNED],$states[ACCEPTED])");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
		." values('REJECT', 'Reject the ticket.', $states[ASSIGNED],$states[REJECTED])");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
		." values('REASSIGN', 'Reassign the ticket.', $states[REJECTED],$states[ASSIGNED])");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
		." values('VALIDATE', 'I worked on the ticket and the work requires validation.', $states[ACCEPTED],$states[TOVALIDATE])");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
		." values('PAUSE', 'I worked on the ticket but the work can not be completed. Paused because work is blocked.', $states[ACCEPTED],$states[PENDING])");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
		." values('POSTPONE', 'I worked on the ticket but the work can not be completed. Resolution is postponed.', $states[ACCEPTED],$states[POSTPONED])");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
		." values('VERIFY', 'The ticket resolution was successfully validated.', $states[TOVALIDATE],$states[VERIFIED])");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
		." values('VALIDATE', 'I worked on the ticket and the work requires validation.', $states[PENDING],$states[TOVALIDATE])");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
		." values('VALIDATE', 'I worked on the ticket and the work requires validation.', $states[POSTPONED],$states[TOVALIDATE])");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
		." values('NOT COMPLETED', 'The ticket is not resolved and it requires more work.', $states[TOVALIDATE],$states[RETURNED_VERIFIER])");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
		." values('COMPLETED', 'The ticket resolution was successfully validated. Close the ticket.', $states[VERIFIED],$states[RESOLVED])");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
		." values('NOT COMPLETED', 'The ticket is not resolved and it requires more work.', $states[VERIFIED],$states[RETURNED_OPER])");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
		." values('REASSIGN', 'Accept the ticket to work on it because validation by Help Desk did not succeeded.', $states[RETURNED_OPER],$states[ACCEPTED])");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
		." values('REASSIGN', 'Accept the ticket to work on it because validation by verifier did not succeeded.', $states[RETURNED_VERIFIER],$states[ACCEPTED])");


?>
