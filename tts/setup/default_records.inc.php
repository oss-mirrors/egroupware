<?php
  /**************************************************************************\
  * phpGroupWare - Setup                                                     *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
  /* $Id$ */

	
	$oProc->query("insert into phpgw_tts_states(state_id,state_name,state_description) values(-1,'UNDEFINED','The ticket was existent before the Petri Net infrastructure was defined and should be assigned a state.')");
	$oProc->query("insert into phpgw_tts_states(state_id,state_name,state_description,state_initial) values(1,'NEW','A new ticket has been reported.',1)");
	$oProc->query("insert into phpgw_tts_states(state_id,state_name,state_description) values(2,'ACCEPTED','The ticket has been accepted by the owner, who is about to work on it.')");
	$oProc->query("insert into phpgw_tts_states(state_id,state_name,state_description) values(3,'REOPENED','The ticket has been reopened for further work.')");
	$oProc->query("insert into phpgw_tts_states(state_id,state_name,state_description) values(4,'RESOLVED','The ticket has been successfully resolved.')");
	$oProc->query("insert into phpgw_tts_states(state_id,state_name,state_description) values(5,'VERIFIED','The ticket has been verified and has to be worked on.')");
	$oProc->query("insert into phpgw_tts_states(state_id,state_name,state_description) values(6,'CLOSED','The ticket has been closed without resolution.')");
	$oProc->query("insert into phpgw_tts_states(state_id,state_name,state_description) values(7,'TOVALIDATE','The ticket has been worked on and the work requires validation.')");
	$oProc->query("insert into phpgw_tts_states(state_id,state_name,state_description) values(8,'NEEDSWORK','The ticket has been worked on, but requires more work.')");
	$oProc->query("insert into phpgw_tts_states(state_id,state_name,state_description) values(9,'INVALID','The owner of the ticket was not able to confirm the issue.')");
	$oProc->query("insert into phpgw_tts_states(state_id,state_name,state_description) values(10,'DUPLICATE','The ticket was found a duplicate of another ticket.')");

	if ($oProc->type == 'pgsql')	//PGSQL ONLY!!!
	{
		$oProc->query("select setval('seq_phpgw_tts_states',10)");
	}
	foreach(array(
		1  => 'NEW',
		2  => 'ACCEPT',
		3  => 'REOPENED',
		4  => 'RESOLVED',
		5  => 'VERIFIED',
		6  => 'CLOSED',
		7  => 'TOVALIDATE',
		8  => 'NEEDSWORK',
		9  => 'INVALID',
		10 => 'DUPLICATE',
	) as $num => $state)
	{
		$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
						." values('TO$state','Put the preexistent ticket into the state %1.',-1,$num)");
	}
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
						." values('ACCEPT','Accept the ticket into verification process.',1,2)");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
						." values('VERIFY','Verify the ticket to work on it.',2,5)");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
						." values('INVALIDATE','The ticket is invalid and cannot be worked on.',2,9)");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
						." values('DUPLICATE','The ticket is a duplicate of another ticket and should be closed.',2,10)");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
						." values('CLOSE','Close the invalid ticket.',9,6)");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
						." values('MOREWORK','I worked on the ticket, but did not finish.',5,8)");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
						." values('COMPLETED','I worked on the ticket and the work requires validation.',5,7)");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
						." values('COMPLETED','I worked on the ticket and the work requires validation.',8,7)");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
						." values('NOT COMPLETED','The validation of the ticket was unsuccessfull. The ticket requires more work.',7,8)");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
						." values('RESOLVE','The ticket resolution was successfully validated. Close the ticket.',7,4)");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
						." values('REOPEN','The closed ticket requires more work. Reopen it.',6,3)");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
						." values('REOPEN','The closed ticket requires more work. Reopen it.',4,3)");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
						." values('NO DUPLICATE','The ticket is essentially not a duplicate of another ticket. Reopen it.',10,3)");
	$oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
						." values('ACCEPT','Accept the ticket into verification process.',3,2)");
?>
